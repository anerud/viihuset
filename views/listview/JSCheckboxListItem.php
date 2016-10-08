<?php
require_once("interfaces/iView.php");

class JSCheckboxListItem implements iView{
	
	private $url;
	private $startValue;
    private $style;
	
	public function __construct($url, $startValue, $style){
		$this->url = $url;		
		$this->startValue = $startValue;
        $this->style = $style;
	}
	
	//Listener will be created in site.js
	public function render(){
        $style = $this->style != null ? " style=\"".$this->style."\"" : "";
		echo '<div class="JSCheckboxListItem"'.$style.'>';
		echo '<input data-url="'.$this->url.'" class="checkbox" '.($this->startValue ? 'checked': '').'  type="checkbox" />';
		echo '</div>';
	}
	
}

?>