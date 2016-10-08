<?php
require_once("interfaces/iView.php");

class LinkListItem implements iView{
	
	private $url;
	private $listItem;
	private $newTab;
	
	public function __construct($url, $listItem, $newTab){
		$this->url = $url;		
		$this->listItem = $listItem;
		$this->newTab = $newTab;		
	}
	
	public function render(){
		echo '<a '.($this->newTab ? 'target="_blank"' : '').' href="'.$this->url.'" class="linkListItem textcolor4">';
		$this->listItem->render();
		echo '</a>';
	}
	
}

?>