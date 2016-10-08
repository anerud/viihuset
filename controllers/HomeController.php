<?php
require_once("interfaces/iController.php");
require_once("interfaces/AbstractModule.php");

final class HomeController extends AbstractModule {

	private $dbContext;

	public function init($router, $dbContext){
		parent::init($router, $dbContext);

		$this->dbContext = $dbContext;

		//Brf page
		$router->map('GET', '/[a:brf]/hem',
			function($brf) {
				//Query database for correct info
				require_once("model/module/ModuleInfo.php");
				$module = $this->dbContext->getModule($brf,"home");

				//Render view
				//Render view
				$view = "HomeView";
				require_once("views/home/".$view.".php");
				$viewObject = new $view($module,NULL);
				$viewObject->render();
			},
			NULL,
			function(){return UserLevels::$userLevels["brf"];}
		);

		//Admin page
		$router->map('GET', '/hem',
			function() {
				//Get the logged in user
				$user = $_SESSION["user"];
				if ($user != null && UserLevels::$userLevels[$user->userlevel] == UserLevels::$userLevels["sysadmin"]) {
					header("Location: /sysadmin");
					return;
				}
				$brf = $user->brf;

				//Query database for correct info
				require_once("model/module/ModuleInfo.php");
				$module = $this->dbContext->getModule($brf,"home");

				//Render view
				$view = "HomeAdminView";
				require_once("views/home/".$view.".php");
				$viewObject = new $view($module,NULL);
				$viewObject->render();
			},
			NULL,
			function(){return UserLevels::$userLevels["admin"];}
		);

	}
	/*
	* Implementing abstract methods
	*/

	public function getDomain() {
		return "portal";
	}

	public function getModuleName() {
		return "home";
	}

	public function getBaseUrl() {
		return "hem";
	}

	public function getLinkTitle() {
		return "Hem";
	}

	public function getDefaultModule() {
		require_once("model/module/ModuleInfo.php");
		$module = new ModuleInfo();
        $module->name = "home";
		$module->brf = null;
		$module->title = "Hem";
        $module->description = "Välkommen till vår hemsida!";
		$module->userlevel = "brf";
		$module->visible = 1;
		$module->sortindex = 0;
		$module->rightcol_sortindex = null;
		return $module;
	}

	public function getRightColModule($currentBrf) {
		return null;
	}

	public function getRightColSortIndex($currentBrf) {
		return null;
	}
}

?>
