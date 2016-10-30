<?php
require_once("interfaces/iView.php");
require_once("views/moduleInfo/ModuleInfoAdminView.php");

class MailSentView implements iView{

	private $brf;
	private $result;

	public function __construct($brf, $result){
		$this->brf = $brf;
		$this->result = $result;
	}

	public function render(){
		echo "<p>".$this->result."</p>";
		echo "<a href='/".$this->brf."/mailings' class='button-blue'>Tillbaka</a>";
	}

}

?>
