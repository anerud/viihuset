<?php
require_once("interfaces/iController.php");
require_once("interfaces/AbstractModule.php");

final class UserdefinedModuleController implements iController {

	private $dbContext;

	public function init($router, $dbContext){
		$this->dbContext = $dbContext;

		// Get the page for a user defined submodule
		$router->map('GET', '/[a:brf]/submodul/[a:parent]/[a:submodule]',
			function($brf, $parent, $submodule) {
				//Query database for correct info
				require_once("model/module/SubModule.php");
				$submodule = $this->dbContext->getSubModule($brf, $parent, $submodule);
				if($submodule == null) {
					header("Location: /");
				}
				require_once("views/moduleInfo/ModuleInfoView.php");
				$viewObject = new ModuleInfoView($submodule, NULL);
				$viewObject->render();
			},
			NULL,
			function(){return UserLevels::$userLevels["brf"];}
		);

		// Get the admin page for a user defined submodule
		$router->map('GET', '/submodul/[a:parent]/[a:submodule]',
			function($parent, $submodule) {
				$user = $_SESSION["user"];
				$brf = $user->brf;
				//Query database for correct info
				require_once("model/module/SubModule.php");
				$submodule = $this->dbContext->getSubModule($brf, $parent, $submodule);
				if($submodule == null) {
					header("Location: /");
				}
				require_once("views/moduleInfo/SubModuleAdminView.php");
				$viewObject = new SubModuleAdminView($submodule, NULL);
				$viewObject->render();
			},
			NULL,
			function(){return UserLevels::$userLevels["admin"];}
		);

		$router->map('POST', '/[a:parent]/[a:name]/moduleInfo',
			function($parent, $name) {

				//Get the logged in user
				$user = $_SESSION["user"];

				//Check that all fields of form has been filled correctly
				$errors = array();

				if(!isset($_POST["title"]) || empty($_POST["title"])) {
					array_push($errors, "Du måste ange en titel!");
				}

				if(!isset($_POST["description"]) || empty($_POST["description"])) {
					$_POST["description"] = "";
				}

				//If there were errors.
				if(count($errors) > 0) {
					setErrors($errors);
				}else{
					$title = $_POST["title"];
					$description = $_POST["description"];
					$userlevel = $_POST['moduleUserLevel'];

					$this->dbContext->createOrUpdateSubModule(
						$name,
						$user->brf,
						$parent,
						$title,
						$description,
						$userlevel != NULL ? $userlevel : "brf",
						1
					);
				}

				$returnUrl = $_POST["returnUrl"];
				header("Location: ".$returnUrl);
			},
			NULL,
			function(){return UserLevels::$userLevels["admin"];}
		);

		//Brf page
		$router->map('GET', '/[a:brf]/modul/[a:moduleName]',
			function($brf, $moduleName) {
				//Query database for correct info
				require_once("model/module/ModuleInfo.php");
				$module = $this->dbContext->getModule($brf, $moduleName);

				//Render view
				//Render view
				$view = "ModuleInfoView";
				require_once("views/moduleInfo/".$view.".php");
				$viewObject = new $view($module,NULL);
				$viewObject->render();
			},
			NULL,
			function(){return UserLevels::$userLevels["brf"];}
		);

		//Admin page
		$router->map('GET', '/modul/[a:moduleName]',
			function($moduleName) {
				//Get the logged in user
				$user = $_SESSION["user"];
				$brf = $user->brf;

				//Query database for correct info
				require_once("model/module/ModuleInfo.php");
				$module = $this->dbContext->getModule($brf, $moduleName);

				//Render view
				$view = "ModuleInfoAdminView";
				require_once("views/moduleInfo/".$view.".php");
				$viewObject = new $view($module,NULL);
				$viewObject->render();
			},
			NULL,
			function(){return UserLevels::$userLevels["admin"];}
		);

        $router->map('DELETE', '/submodule/[a:parent]/[a:submodule]',
			function($parent, $submodule) {
				//Get the logged in user
				$user = $_SESSION["user"];

				//Collect documents for the view
				$this->dbContext->deleteSubModule($user->brf, $parent, $submodule);

				header("HTTP/1.1 200 OK");
				return;
			},
			NULL,
			function(){return UserLevels::$userLevels["admin"];},
			"",
			false
		);

		$router->map('POST', '/createNewPage',
			function() {

				//Get the logged in user
				$user = $_SESSION["user"];
				$brf = $user->brf;

				//Check that all fields of form has been filled correctly
				$errors = array();
				$title = "Titel";
				$userlevel = "brf";
				$titleType = "headtitle";
				$parent = "home";

				if(isset($_POST["title"])){
					$title = $_POST["title"];
				}

				if(isset($_POST["userlevel"])){
					$userlevel = $_POST["userlevel"];
				}

				if(isset($_POST["titleType"])){
					$titleType = $_POST["titleType"];
				}

				if(isset($_POST["parent"])){
					$parent = $_POST["parent"];
				}

				$name = normalize($title);
				$userdefined = 1;
				$visible = 1;
				if($titleType == "headtitle") {
					$sortindex = $this->dbContext->getMaxSortIndex($brf) + 1;
					$this->dbContext->createOrUpdateModule(
				        $name,
				        $brf,
				        $title,
						"",
				        $sortindex,
				        $userlevel,
						$userdefined,
						$visible
					);
				header("Location: /modul/".$name);
				} else {
					$this->dbContext->createOrUpdateSubModule(
				        $name,
				        $brf,
						$parent,
				        $title,
				        "",
				        $userlevel,
				        $visible
					);
				header("Location: /submodul/".$parent."/".$name);
				}
			},
			NULL,
			function(){return UserLevels::$userLevels["admin"];}
		);

	}

	public function isVisible($brf){
		return 1;
	}

    public function getLinksByLevel($level, $endpointLevel, $brf, $domain) {

		// Dont show links if not in correct domain.
		if($domain != $this->getDomain() || $brf == null) {
			return null;
		}

		$links = array();
		$userdefinedModules = $this->dbContext->getUserdefinedModules($brf);

		foreach ($userdefinedModules as $module) {
			$required_userlevel = UserLevels::$userLevels[$module->userlevel];
			if ($level < $required_userlevel) {
				return;
			}

			if (!$module->visible && $endpointLevel < UserLevels::$userLevels["admin"]) {
				continue;
			}

			$url = "/modul/".$module->name;
			$url = $endpointLevel < UserLevels::$userLevels["admin"] ? "/<currentBrf>".$url : $url;
			$submodules = $this->dbContext->getSubModules($brf, $module->name);
			array_push(
				$links,
				array(
					"title" => $module->title,
					"module" => $module->name,
					"checked" => $endpointLevel < UserLevels::$userLevels["admin"] ? null : $module->visible,
					"url" => $url,
					"position" => "navbar",
					"sublinks" => array(),
					"class" => " bas ",
	                "sortindex" => $module->sortindex,
					"submodules" => $submodules
				)
			);
		}
		return $links;
	}

	public function getDomain() {
		return "portal";
	}

}

?>
