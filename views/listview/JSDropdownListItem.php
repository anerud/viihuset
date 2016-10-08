<?php
require_once("interfaces/iView.php");

class JSDropdownListItem implements iView{
	
	private $url;
	private $value;
	private $currentValue;
    private $style;
	
	public function __construct($url, $values, $currentValue, $style){
		$this->url = $url;		
		$this->currentValue = $currentValue;	
		$this->values = $values;
        $this->style = $style;
	}
	
	//Listener will be created in site.js
	public function render(){
    $style = $this->style != null ? " style=\"".$this->style." !important\"" : "";
		echo '<div class="JSDropdownListItem"'.$style.'>';
		echo '<select class="select" data-url="'.$this->url.'">';
		foreach($this->values as $val => $description){
			echo '<option value="'.$val.'" '.($val == $this->currentValue ? 'selected' : '').'>'.$description.'</option>';			
		}		
		echo '</select>';
		echo '</div>';
	}
	
}

?>