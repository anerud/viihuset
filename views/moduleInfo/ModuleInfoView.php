<?php
require_once("interfaces/iView.php");

class ModuleInfoView implements iView{
	
	private $moduleInfo;
	
	public function __construct($moduleInfo) {
		$this->moduleInfo = $moduleInfo;
	}

	public function render(){
		
		?>
		
		<h2><?php echo $this->moduleInfo->title;?></h2>
		<?php echo $this->moduleInfo->description;?>
		<hr/>
		<?php
	}
}
?>