<?php
require_once("interfaces/iController.php");
require_once("interfaces/AbstractModule.php");

final class UtilController implements iController{

	private $dbContext;

	public function init($router, $dbContext){
		$this->dbContext = $dbContext;

		$router->map('POST', '/[a:moduleName]/toggleVisibility',
			function($moduleName) {
				$user = $_SESSION["user"];
				$this->dbContext->toggleModuleVisibility($user->brf, $moduleName);
				header("HTTP/1.1 200 OK");
				return;
			},
			NULL,
			function(){return UserLevels::$userLevels["admin"];},
			"",
			false
		);

        $router->map('POST', '/[a:moduleName]/moveUp',
			function($moduleName) {
				$user = $_SESSION["user"];
				$this->dbContext->moveUpModule($user->brf, $moduleName);
				header("HTTP/1.1 200 OK");
				return;
			},
			NULL,
			function(){return UserLevels::$userLevels["admin"];},
			"",
			false
		);

        $router->map('POST', '/[a:moduleName]/moveDown',
			function($moduleName) {
				//Get the logged in user
				$user = $_SESSION["user"];
				//Toggle visibility for module.
				$this->dbContext->moveDownModule($user->brf, $moduleName);
				header("HTTP/1.1 200 OK");
				return;
			},
			NULL,
			function(){return UserLevels::$userLevels["admin"];},
			"",
			false
		);

		$router->map('POST', '/[a:moduleName]/moveRightColUp',
			function($moduleName) {
				//Get the logged in user
				$user = $_SESSION["user"];
				//Toggle visibility for module.
				$this->dbContext->moveUpRightColModule($user->brf, $moduleName);
				header("HTTP/1.1 200 OK");
				return;
			},
			NULL,
			function(){return UserLevels::$userLevels["admin"];},
			"",
			false
		);

        $router->map('POST', '/[a:moduleInfo]/moveRightColDown',
			function($moduleName) {
				//Get the logged in user
				$user = $_SESSION["user"];
				//Toggle visibility for module.
				$this->dbContext->moveDownRightColModule($user->brf, $moduleName);
				header("HTTP/1.1 200 OK");
				return;
			},
			NULL,
			function(){return UserLevels::$userLevels["admin"];},
			"",
			false
		);

		//Admin page (POST) for updating module info.
		$router->map('POST', '/[a:moduleName]/moduleInfo',
			function($moduleName) {

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
					$sortindex = $_POST['sortindex'];
					$module = $this->dbContext->getModule($user->brf, $moduleName);
					$userdefined = $module->userdefined;

					$this->dbContext->createOrUpdateModule(
						$moduleName,
						$user->brf,
						$title,
						$description,
						$sortindex,
						$userlevel != NULL ? $userlevel : "brf",
						$userdefined,
						1
					);
				}

				$returnUrl = $_POST["returnUrl"];
				header("Location: ".$returnUrl);
			},
			NULL,
			function(){return UserLevels::$userLevels["admin"];}
		);

	}

    public function getLinksByLevel($level, $endpointLevel, $brf, $domain) {
		return null;
	}

}

?>
