<?php
ob_start();

/* --- Includes --- */

include('functions.php');
require_once("model/user/User.php");
require_once("enum/UserLevels.php");
require_once("DataContext/DatabaseContext.php");
require_once("AppConfig.php");
require 'vendor/autoload.php';
require __DIR__.'/vendor/autoload.php';
require __DIR__.'/classes/AltoRouter.php';
use Mailgun\Mailgun;
use Http\Adapter\Guzzle6\Client;

$cookieLifetime=60*60*24*14; // 14 days
session_start();
setcookie(session_name(),session_id(),time()+$cookieLifetime);



/* --- Init variables --- */

//Open a db connection
$dbContext = new DatabaseContext();

//Init user level enums
UserLevels::init($dbContext);

// Create Mailgun client
$appConfig = new AppConfig();
$httpClient = new Client();
$mgClient = new Mailgun($appConfig->cfg['mg_key'], $httpClient);
$mailDomain = $appConfig->cfg['mg_domain'];

// Init AltoRouter (used for matching and routing of endpoints)
$router = new AltoRouter();

// Init all controllers and store the visible ones.
$controllers = getControllers();
foreach ($controllers as $controller) {
	$controller->init($router, $dbContext);
	if ($controller instanceof MailingController) {
		$controller->initMailingClient($mgClient, $mailDomain);
	}
}

// Init request variables defaults
$siteContent = true;
$homePageSubTitleName = null; // The sub title of the home page.
$user = null; // The logged in user, null if not logged in
$brfByUrl = null; // The brf extracted from the request url
$currentBrf = null; // The brf of the request, extracted from logged in user, fallback on brf by url, else null
$requestLevel = UserLevels::$userLevels["sales"]; // The user level of the request, depends on the level of the logged in user.
$endPointLevel = 0; // The required user level for the endpoint trying to be accessed
$domain = "sales"; // Different parts of the site, like "portal", "design", "settings". Default to "sales".

// TODO: Figure out and simplify these variables
$linkReplaces = array();
$navs = array();
$headers = array();
$footers = array();

// If user set on session, update user and the request level
if(isset($_SESSION["user"])){
	$user = $_SESSION["user"];
	$requestLevel = UserLevels::$userLevels[$user->userlevel];
}

// Match incoming request to endpoint
$match = $router->match();
if($match == null) {
	// Split URL by "/" to get it's parts
	$partsOfURL = explode("/", $_SERVER["REQUEST_URI"]);

	// Figure out if brf is specified in URL
	if(sizeof($partsOfURL) > 1){
		// Possible brf expected to be at position 1
		$possibleBrf = $partsOfURL[1];

		// Check if there exists a home module for possible brf
		$mod = $dbContext->getModule($possibleBrf, "home");

		// If home module exists, redirect to it
		if($mod != null){
			header("Location: /".$mod->brf."/hem");
			return;
		}
	}

	// Fall back to default page
	$match = $router->match("/");
}

// Update variables from the match
$siteContent =  $match["useOB"];
$homePageSubTitleName =  $match["titleName"];
$domain = $match["domain"];

// Update brf specific variables if there is a brf in the match
if(isset($match["params"]) && isset($match["params"]["brf"])){
	$currentBrf = $match["params"]["brf"];
	$brfByUrl = $currentBrf;
	// The user requested non-sales site, update the request level to at least "brf"
	$requestLevel = max($requestLevel, UserLevels::$userLevels["brf"]);
}

// If domain name is brf specific, check if it is request is allowed towards it
$domainName = url_to_domain(idn_to_utf8($_SERVER['SERVER_NAME']));
if($domainName !== "viihuset.se" && $domainName !== "localhost"){
	if(!isRequestAllowedForDomain($dbContext, $user, $brfByUrl, $domainName)) {
		return;
	}
}

// Update the required user level for the endpoint
$endPointLevel = call_user_func($match['authLevelFunction'], $currentBrf);

// If a logged in user tries to access another brf's auth required pages,
// then redirect to login page
if($user != null
	&& UserLevels::$userLevels[$user->userlevel] != UserLevels::$userLevels["sysadmin"]
	&& $currentBrf != null
	&& $user->brf != $currentBrf
	&& $endPointLevel > UserLevels::$userLevels["brf"]
) {
	header("Location: /auth?returnUrl=".urlencode($_SERVER['REQUEST_URI']));
	return;
}

// If somehow the brf is not figured out, take brf from user if present
if($currentBrf == null && $user != null){
	$currentBrf = $user->brf;
}

// Save visitor for brf
$ip = $_SERVER['REMOTE_ADDR'];
$dbContext->saveVisitor($currentBrf, $ip);

// Access denied if endpoint level too high
if($endPointLevel > $requestLevel){
	header("Location: /auth?returnUrl=".urlencode($_SERVER['REQUEST_URI']));
	return;
}

// Used to replace the key "<currentBrf>" with current brf in dynamic links
$linkReplaces["<currentBrf>"] = $currentBrf;

$allLink = array();
foreach ($controllers as $controller) {
    $visible = isControllerVisibleForUser(
		$controller,
		$endPointLevel,
		$user != null ? $user->brf : null,
		$currentBrf
    );

	if(!$visible){
		continue;
	}

	$links = $controller->getLinksByLevel($requestLevel, $endPointLevel, $currentBrf, $domain);
    if(!is_array($links)){
		continue;
	}

    $checked = null;
    if($visible === 2){ // Admin, but checkbox in left nav should be unchecked
        $checked = 0;
    }else if($visible === 3){ // Admin, checkbox in left nav should be checked
        $checked = 1;
    }

	// For all of the controller's links, set checked property
    for ($i = 0 ; $i < sizeof($links); $i++) {
        if (!isset($links[$i]['checked'])) {
			$links[$i]['checked'] = $checked;
		}

		// If admin, the 'module' property is needed for moving module up or down
        if($checked === 1 || $checked === 0 ){
            $links[$i]['module'] = $controller->getDefaultModule()->name;
        }

	}

	$allLink = array_merge($allLink, $links);
}


// Sort links by their sortindex
usort($allLink, "sort_modules");

// Split all links in to left navigation bar, header and footer links
foreach($allLink as $l){
	if(strpos($l["position"],"navbar") !== false){
		array_push($navs, $l);
	}
	if(strpos($l["position"],"header") !== false){
		array_push($headers, $l);
	}
	if(strpos($l["position"],"footer") !== false){
		array_push($footers, $l);
	}
}

// Clears the output buffer.
ob_clean();

$banner = $dbContext->getBanner($currentBrf);
$brfInfo = $dbContext->getBrfInfo($currentBrf);

// Validate that user demo time has not ended
if ($brfInfo != null && !$brfInfo->activated) {
	$demoDaysLeft = userDemoDaysLeft($brfInfo);
    if($demoDaysLeft < 0) {
		session_destroy();
	    echo "Demo-tiden för den här sidan är slut. Kontakta kundtjänst för mer info.";
	    return;
	}
}

// If this is a request for asset (like .js) just call that endpoint
if(!$siteContent){
	callMatchedEndpoint($match);
}else{
	?>
	<!doctype html>
	<html>
	<head>
		<title><?php echo empty($homePageSubTitleName) ? "" :  $homePageSubTitleName." – "?>Vi i Huset</title>
		<meta charset='utf-8' />
	    <link href='https://fonts.googleapis.com/css?family=Roboto:400,300,300italic,400italic,500,500italic,700,700italic' rel='stylesheet' type='text/css'>
		<link rel='shortcut icon' href="/favicon.ico" type='image/x-icon' />
		<link rel='stylesheet' href="/css/site/<?php echo $endPointLevel; ?>/<?php echo $currentBrf == null ? "null" : $currentBrf; ?>" />
		<meta name='description' content="" />
		<meta name="robots" content="" />
		<meta name="author" content="John & Sebastian" />
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<script src="/js/jquery.js"></script>
		<script src="/js/site.js"></script>
		<script>
			window.brf = "<?php echo $currentBrf; ?>"
		</script>
		<script type="text/javascript" src="/js/tinymce/tinymce.min.js"></script>
		<script type="text/javascript">
			tinymce.init({
				selector: "textarea",
				 menubar:false,
	    		statusbar: false,
				plugins: [
					"advlist autolink lists link image charmap print preview anchor",
					"searchreplace visualblocks code fullscreen",
					"insertdatetime media table contextmenu paste",
					"template paste textcolor colorpicker textpattern imagetools"
				],
				toolbar: "insertfile undo redo | styleselect | forecolor backcolor | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | preview"
			});
		</script>
	</head>
	<body>


		<?php
			// If sysadmin, just call the endpoint
			if ($user != null && UserLevels::$userLevels[$user->userlevel] == UserLevels::$userLevels["sysadmin"]) {
				echo "<div class='sysadmin'>";
				callMatchedEndpoint($match);
				echo "</div>";
			} else {
				if($requestLevel == UserLevels::$userLevels["admin"]){
					?>
					<!-- Admin panel -->
					<div class="adminpanel">

						<!-- Left side: Vi i Huset logo -->
						<div class="adminpanelleft">
							<a class="adminpanellogo" href="/"></a>
						</div>

						<!-- Right side: Showing "DEMO" if not activated -->
						<div class="adminpanelright">
						<?php
						if(!$brfInfo->activated) {
						?>
							<div class="adminpaneldemo textcolor0">DEMO</div>
							<div class="adminpaneldemotext textcolor0"><?php echo $demoDaysLeft." dagar kvar";?><a class="textcolor4 orderToday" href="#">BESTÄLL IDAG</a></div>
						<?php
						}
						?>
						</div>

						<!-- Center: Navigations -->
						<div class="adminpanelcenter">
							<?php
							if ($endPointLevel != UserLevels::$userLevels["admin"]) {
							?>
								<!-- NOT ON ADMIN PAGE -->


								<!-- MIN SIDA -->
								<a href="/hem" class="adminpanellink">
									<div class="adminpanellinkimage" style="background-image: url(/gfx/Min_sida.jpg);"></div>
									<div class="adminpanellinktext textcolorGray textSize11px">min sida</div>
								</a>

							<?php
							} else {
							?>
								<!-- ON ADMIN PAGE -->

								<!-- KLAR -->
								<a href="/<?php echo $currentBrf ?>/hem" class="adminpanellink">
									<div class="adminpanellinkimage" style="background-image: url(/gfx/logout.png);"></div>
									<div class="adminpanellinktext textcolorGray textSize11px">klar</div>
								</a>

								<?php
								if($domain == "design" || $domain == "settings") {
								?>
									<!-- ON DESIGN OR SETTINGS -->

									<!-- SPARA -->
									<a href="#" class="adminpanellink">
										<div id="savebutton" class="adminpanellinkimage" style="background-image: url(/gfx/Spara_ikon.jpg);"></div>
										<div class="adminpanellinktext textcolorGray textSize11px">spara</div>
									</a>
									<script>
						                $("#savebutton").click(function(){
											document.getElementById("save").click();
						                });
									</script>

									<!-- MIN SIDA -->
									<a href="/hem" class="adminpanellink">
										<div class="adminpanellinkimage" style="background-image: url(/gfx/Min_sida.jpg);"></div>
										<div class="adminpanellinktext textcolorGray textSize11px">min sida</div>
									</a>

								<?php
								}
								?>


								<!-- LÖSENORD OCH BEHÖRIGHET -->
								<a href="/basinfo"  class="adminpanellink">
									<div class="adminpanellinkimage" style="background-image: url(/gfx/key.png);"></div>
									<div class="adminpanellinktext textcolorGray textSize11px">lösenord & behörighet</div>
								</a>

								<!-- DESIGN -->
								<a href="/teman"  class="adminpanellink">
									<div class="adminpanellinkimage" style="background-image: url(/gfx/design.png);"></div>
									<div class="adminpanellinktext textcolorGray textSize11px">design</div>
								</a>

								<!-- HJÄLP -->
								<a href="#"  class="adminpanellink">
									<div class="adminpanellinkimage" style="background-image: url(/gfx/help.png);"></div>
									<div class="adminpanellinktext textcolorGray textSize11px">hjälp</div>
								</a>

							<?php
							}
							?>


						</div>
					</div>
					<?php
				}

		if ($banner == null ||  $banner->max_width == 0) {
			echo '<div class="page">';
		}

		?>
		<div class="header">
		    <div class="headercon">
		        <div class="headertitlecon">
		            <div class="headertitle"><?php echo $banner != null ? $banner->bannerText : "";?></div>
		        </div>
		        <ul class="headermenu">
		            <?php
		                foreach ($headers as $nav) {
		                    $requrl=strtok($_SERVER["REQUEST_URI"],'?');
		                    $url = fixLink($linkReplaces, $nav["url"]);
		                    echo "<li class='".($requrl == $url ? ' a ': '')."'><a href='".$url."' class='".$nav["class"]."'>".$nav["title"]."</a></li>";
		            }
		            ?>
		        </ul>
		    </div>
		</div>
		<?php

		if ($banner != null && $banner->max_width == 1) {
			echo '<div class="page">';
		}

		  	?>
			<div class="content">
				<div class="contentcon">
					<div class="leftcontent">
						<ul class="leftNavBar">
							<?php

		                    if($user != null && UserLevels::$userLevels[$user->userlevel] == UserLevels::$userLevels["admin"] &&  $domain == "portal" ){
		                        ?>

		                        <div class='createPage'>

								<div style='background-image: url(/gfx/create.png); float:left; width: 26px; height: 28px;  '></div>
		                        <a href='#' class='' id='createNewPageButton'>
		                        <span>Skapa ny sida</span>
		                        </a>


		                            <div id='createNewPagePopupOverlay'>
		                                <div id='createNewPagePopup'>

		                                <form action='/createNewPage' method='post' class='form' autocomplete='off'>
											<!--  Create new page title -->
											<img src="/gfx/create.png"><h2>Skapa ny sida</h2></img>
											<hr>

											<!-- Title input -->
		                                    <h3 class="createNewPageFormInputTitle">Sidans titel</h3>
		                                    <input type='text' class="createNewPageFormInput" name='title' value='' placeholder='Titel' tabindex='1' class="form-input-basic"/>
											<hr>

											<!-- User level -->
							                <select class="form-input-basic-select" name="userlevel">
							                    <?php
							                    foreach(UserLevels::$userLevels as $key => $val){
							                        if($val >= UserLevels::$userLevels["brf"] && $val <= UserLevels::$userLevels["admin"]) {
							                            echo '<option value="'.$key.'" '.($key == "brf" ? 'selected' : '').'>'.UserLevels::$namesToDescription[$key].'</option>';
							                        }
							                    }
							                    ?>
							                </select>
											<hr>

											<!-- Head title or sub title -->
											<input type="radio" name="titleType" value="headtitle" checked="checked"><span class="textBold radioText">Huvudrubrik</span>
											<input type="radio" name="titleType" value="subtitle"><span class="textBold radioText">Underrubrik</span>
											<br>
											<br>
											<span>Underrubriker placeras under valfri huvudrubrik</span>
											<br>
							                <select class="form-input-basic-select" name="parent">
							                    <?php
												$modules = $dbContext->getAllModules($currentBrf);
							                    foreach($modules as $module){
						                            echo '<option value="'.$module->name.'">'.$module->title.'</option>';
							                    }
							                    ?>
							                </select>
											<hr>

											<!-- submit and cancel -->
			                                <input class="button-blue" type='submit' name='submit' value='Skicka' tabindex='5' />
			                                <button id="createNewPagePopupCancel" class="button-red">Avbryt</button>
		                                </form>
		                            </div>
		                       </div>
		                       </div>
		                        <?php
		                    }

							for ($i = 0; $i < count($navs); $i++) {
		                        $nav = $navs[$i];
								echo "<ul>";
		                        $requrl=strtok($_SERVER["REQUEST_URI"],'?');
		                        $url = fixLink($linkReplaces, $nav["url"]);
		                        echo "<li class='link ".(strpos($requrl,$url) !== false ? 'a ': '')."bordercolor3 bgcolor2 hoverbg textcolor0 ".$nav["class"].($nav["checked"] === 1 || $nav["checked"] === 0 ? " moduleli" : "")."'>";

									if($nav["checked"] === 1 || $nav["checked"] === 0) {
										// Check if admin and draw arrows and checkbox
										if($user != null && UserLevels::$userLevels[$user->userlevel] == UserLevels::$userLevels["admin"] ){
											echo "<div class='modulearrows'>";
											echo "<div class='moduleup' data-module='".$nav["module"]."'></div> ";
											echo "<div class='modulearrowspace'></div> ";
											echo "<div class='moduledown' data-module='".$nav["module"]."'></div> ";
											echo "</div>";
											echo  "<input data-module='".$nav["module"]."' type='checkbox' id='nav".$nav["module"]."' class='modulevisiblecheckbox' ".($nav["checked"] === 1 ? "checked='checked'" : "")."/>";
										}
									}

									echo "<a href='".$url."' class=''>";
									echo "<span>".$nav["title"]."</span>";
									echo "</a>";

		                        echo "</li>";


		                        if(!empty($nav["submodules"])) {
		                            echo "<li class='submodules'>";
		                            // Check for submodules

		                            foreach ($nav["submodules"] as $submodule) {
		                                $stringToReplace = $endPointLevel < UserLevels::$userLevels["admin"] ? "<currentBrf>/" : "";
		                                $submoduleUrl = fixLink($linkReplaces, "/".$stringToReplace."submodul/".$submodule->parent."/".$submodule->name);
		                                echo "<div class='".(strpos($requrl,$submoduleUrl) !== false ? 'a ': '')."subpage bordercolor3 bgcolor1 hoverbg textcolor0 ".$nav["class"]."'>";

		                                echo "<a href='".$submoduleUrl."' class=''>";
		                                echo "<span>".$submodule->title."</span>";
		                                echo "</a>";
										if(
											$user != null
											&& UserLevels::$userLevels[$user->userlevel] == UserLevels::$userLevels["admin"]
											&& $endPointLevel == UserLevels::$userLevels["admin"]
										){
											echo
												"<div data-name='".$submodule->name."' data-parent='".$submodule->parent."' class='submoduleremove'>
												<img src='/gfx/delete_icon.png' alt='x'>
												</div>";
										}
		                                echo "</div>";
		                            }
		                            echo "</li>";
		                        }

								echo "</ul>";
							}
							?>
						</ul>
					</div>
					<?php
						$showRightCol = $brfInfo != null && $brfInfo->show_right_col;
					?>
					<div class="middlecontent left
						<?php echo $endPointLevel > 0
							&& ($showRightCol || $endPointLevel == UserLevels::$userLevels["admin"])
							&& $domain == "portal" ? "right" : "";
						?>"
					>
					<?php
						callMatchedEndpoint($match);
					?>
					</div>
					<?php
					if($endPointLevel > 0 && $domain == "portal"){
						if($showRightCol || $endPointLevel == UserLevels::$userLevels["admin"]) {
							echo '<div class="rightcontent">';
							if($endPointLevel == UserLevels::$userLevels["admin"]) {
								echo  "<input type='checkbox' class='rightcolvisiblecheckbox' ".($showRightCol ? "checked='checked'" : "")."/>";
								echo "<span class='rightcolCheckboxText'>Högerspalt</span>";
							}
							$rightColModules = array();
							foreach (getControllers() as $controller) {
								if(!($controller instanceof AbstractModule)) {
									continue;
								}

								$rightColModule = $controller->getRightColModule($currentBrf);
								if($rightColModule != null && $rightColModule instanceof iView) {
									$rightCol = array();
									$sortIndex = $controller->getRightColSortIndex($currentBrf);
									$rightCol["sortindex"] = $sortIndex;
									$rightCol["module"] = $rightColModule;
									array_push($rightColModules, $rightCol);
								}
							}

							usort($rightColModules, "sort_modules");
							foreach($rightColModules as $module) {
								$module["module"]->render();
							}
							echo '</div>';
						}
				 	}
					?>
				</div>
			</div>
			<div class="footer">

			</div>
		</div>


		<?php
		if($_SERVER['SERVER_NAME'] != 'localhost') {
		?>
			<script>
			(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
			(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
			m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
			})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

			ga('create', 'UA-43749475-1', 'viihuset.se');
			ga('send', 'pageview');
			</script>
		<?php
		}
	}

	?>
	</body>
	</html>
	<?php
}

// Close db connection after request
$dbContext->closeConnection();
ob_end_flush();

/*
--- Helper functions ---
*/

function getControllers() {
	global $controllers;
	if($controllers != null && !empty($controllers)) {
		return $controllers;
	}

	// Read in all controllers
	$controllers = array();
	foreach (scandir(__DIR__.'/controllers') as $filename) {
		$path = __DIR__ . '/controllers/' . $filename;
		if (is_file($path)) {
			require_once($path);
			$controllerName = substr($filename,0,strlen($filename)-4);

			$controller = new $controllerName;
			if ($controller instanceof iController){
				array_push($controllers, $controller);
			}
		}
	}
	return $controllers;
}

// Calls the endpoint matched by the matcher
function callMatchedEndpoint ($match){
	if($match && is_callable($match['target'] )	) {
		call_user_func_array($match['target'], $match['params'] );
	} else {
		header("HTTP/1.1 404 Not Found");
	}
}

function setErrors($errors){
	 $_SESSION["errors"] = $errors;
}

function getErrors(){
	if(isset($_SESSION["errors"])){
		$errors = $_SESSION["errors"];
		$_SESSION["errors"] = null;
		return $errors;
	}
	return array();
}

function isControllerVisibleForUser($controller, $endPointLevel, $brf, $currentBrf) {
	if(!($controller instanceof AbstractModule)){
		return -1;
	}

	// If admin, should still display module in left nav
	// However, if controller is not visible it should be unchecked
    if($endPointLevel == UserLevels::$userLevels["admin"] && $brf != null && $brf == $currentBrf){
        $v = $controller->isVisible($currentBrf);
        if($v === 0){
            return 2; // Checkbox unchecked
        } else {
            return 3; // Checkbox checked
        }
	}

	// Not admin, return 0 if not visible, 1 otherwise.
	return $controller->isVisible($currentBrf);
}

function isRequestAllowedForDomain($dbContext, $user, $brfByUrl, $domainName) {
	$requestURI = strtolower($_SERVER['REQUEST_URI']);
	$isAsset = strpos($requestURI, "/css") === 0
		|| strpos($requestURI, "/js") === 0
		|| strpos($requestURI, "/auth") === 0;

	// If it is an assset like css, auth or javascript, allow request
	if($isAsset) {
		return true;
	}

	// If there is no brf linked to the domain, redirect to viihuset.se
	$brfByDomain = $dbContext->getBrfFromDomainName($domainName);
	if(empty($brfByDomain)){
		header("Location: http://viihuset.se/");
		return false;
	}

	// If no logged in user
	if(empty($user)){
		// If the brf in the url differes from the brf by domain,
		// redirect to brf by domain.
		// Example: www.somebrf.se/anotherbrf will direct to
		// www.somebrf.se/somebrf/hem
		if(!empty($brfByUrl) && $brfByDomain != $brfByUrl){
			header("Location: http://".$domainName."/".$brfByDomain."/hem");
			return false;
		}

		// If brf by domain is in the request uri, redirect to it's home page
		if(strpos($requestURI, "/".$brfByDomain) !== 0){
			header("Location: http://".$domainName."/".$brfByDomain."/hem");
			return false;
		}

		// Everything is ok
		return true;
	}

	// If logged in user's brf differs from the brf by domain
	if($user->brf != $brfByDomain){
		header("Location: http://viihuset.se/".$user->brf."/hem");
		session_destroy();
		return false;
	}

	// Evrything ok
	return true;
}

// parses url to domain name
function url_to_domain($url) {
    $host = @parse_url($url, PHP_URL_HOST);
    // If the URL can't be parsed, use the original URL
    // Change to "return false" if you don't want that
    if (!$host)
        $host = $url;
    // The "www." prefix isn't really needed if you're just using
    // this to display the domain to the user
    if (substr($host, 0, 4) == "www.")
        $host = substr($host, 4);

    return $host;
}

// function for sorting modules in left navbar
function sort_modules($a, $b)
{
	$aindex = 0;
	$bindex = 0;
	if(isset($a["sortindex"])){
		$aindex = $a["sortindex"];
	}
	if(isset($b["sortindex"])){
		$bindex = $b["sortindex"];
	}
    return $aindex - $bindex;
}

function userDemoDaysLeft($brfInfo) {
	$nDemoDays = 30;
    $registered_at = new DateTime($brfInfo->registered_at);
    $now = new DateTime();
    $demoDaysLeft = $nDemoDays - $now->diff($registered_at)->format("%a");
	return $demoDaysLeft;
}

?>
