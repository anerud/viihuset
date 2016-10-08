<?php
require_once("interfaces/iController.php");


abstract class AbstractModule implements iController{

	 private $dbContext;
	 private $moduleName;

	public function init($router, $dbContext){
		$this->dbContext = $dbContext;
		$this->moduleName = $this->getDefaultModule()->name;


	}

	public abstract function getDefaultModule();

	public abstract function getDomain();

	public abstract function getModuleName();

	public abstract function getBaseUrl();

	public abstract function getLinkTitle();

	public function getRightColModule($currentBrf) {
		return null;
	}

	public function getRightColSortIndex($currentBrf) {
		return $this->dbContext->getModule($currentBrf, $this->moduleName)->rightcol_sortindex;
	}

	public function getLinksByLevel($level, $endpointLevel, $brf, $domain){

		// Dont show links if not in correct domain.
		if($domain != $this->getDomain() || $brf == null) {
			return null;
		}

		$module = $this->dbContext->getModule($brf, $this->getModuleName());
		$required_userlevel = UserLevels::$userLevels[$module->userlevel];
		if($level < $required_userlevel) {
			return null;
		}

		$url = "/".$this->getBaseUrl();
		$url = $endpointLevel < UserLevels::$userLevels["admin"] ? "/<currentBrf>".$url : $url;
        $sortindex = $module->sortindex;
		$submodules = $this->dbContext->getSubModules($brf, $this->getModuleName());
		$links = array();
		array_push($links,
				array(
					"title" => $this->getLinkTitle(),
					"url" => $url,
					"position" => "navbar",
					"sublinks" => array(),
					"class" => " bas ",
                    "sortindex" => $sortindex,
					"submodules" => $submodules
				));

		return $links;
	}

	public function isVisible($brf){
		return $this->dbContext->getVisibility($brf, $this->moduleName);
	}

}

?>
