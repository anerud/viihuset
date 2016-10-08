<?php
require_once("interfaces/iController.php");

final class SalesController implements iController{

	private $dbContext;

	public function init($router, $dbContext){

		$this->dbContext = $dbContext;

		$router->map('GET', '/', function() {

			if(isset($_SESSION["user"])){
				// Redirect to users brf
				$user = $_SESSION["user"];
				if ($user != null && UserLevels::$userLevels[$user->userlevel] == UserLevels::$userLevels["sysadmin"]) {
					header("Location: /sysadmin");
					return;
				}
				header("Location: /".$user->brf."/hem");
				return;
			}else{
				// Show the homepage for Vi i Huset
				require_once("model/texts/SliderText.php");
				$siteTexts = $this->getSiteTexts();
				$sliderText = new SliderText(
				 	$siteTexts->hem_slider_title,
					$siteTexts->hem_slider_text
				);

				//Create the sales view
				$view = "SalesView";
				require_once("views/sales/".$view.".php");
				$viewObject = new $view($sliderText);
				$viewObject->render();
			}


		},"corp",0,"Hem",true,"sales");

		$router->map('GET', '/kontakt', function() {

			//Init of variables
			$siteTexts = $this->getSiteTexts();
			require_once("model/texts/SliderText.php");
			$sliderText = new SliderText(	$siteTexts->kontakt_slider_title,
											$siteTexts->kontakt_slider_text);

			//Create the sales view
			$view = "ContaktView";
			require_once("views/sales/".$view.".php");
			$viewObject = new $view($sliderText);
			$viewObject->render();

		},"corp",0,"Kontakt",true,"sales");

		$router->map('GET', '/anvandaravtal', function() {

			//Create the view
			$view = "UserAgreementView";
			require_once("views/sales/".$view.".php");
			$viewObject = new $view();
			$viewObject->render();

		},"",0,"Användaravtal",true,"sales");

	}

	private function getSiteTexts(){
		$siteTexts = $this->dbContext->getSiteTexts();
		return json_decode($siteTexts);
	}

	public function getLinksByLevel($level, $endpointLevel, $brf, $domain){
		$links = array();

		if($domain != "sales") {
			return $links;
		}

		if($level == UserLevels::$userLevels["sales"]){
			array_push($links,
				array(
					"title" => "Hem",
					"url" => "/",
					"position" => "navbar|footer|header",
					"sublinks" => array(),
					"class" => " hem ",
					"sortindex" => 1,
				));

				array_push($links,
				array(
					"title" => "Kontakt",
					"url" => "/kontakt",
					"position" => "navbar|footer|header",
					"sublinks" => array(),
					"class" => " kontakt ",
					"sortindex" => 2,
				));

				array_push($links,
				array(
					"title" => "Sekretess & avtal",
					"url" => "/anvandaravtal",
					"position" => "navbar|footer",
					"sublinks" => array(),
					"class" => " bas ",
					"sortindex" => 3,
				));
		}

		return $links;
	}


}

?>
