<?php
require_once("interfaces/iView.php");
require_once("views/moduleInfo/ModuleInfoView.php");

class HomeView implements iView{
	
	private $errors;
	private $moduleInfo;
	
	public function __construct($moduleInfo,$errors){
		$this->moduleInfo = $moduleInfo;
		$this->errors = $errors;
	}

	public function render(){
	
		$moduleView = new ModuleInfoView($this->moduleInfo);
		$moduleView->render();
		
        if($this->errors && sizeof($this->errors) > 0){
            errors($this->errors);
        }
        
	}
	
}

?>