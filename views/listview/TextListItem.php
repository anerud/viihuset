<?php
require_once("interfaces/iView.php");

class TextListItem implements iView{

	private $text;
	private $style;
	public function __construct($text, $style){
		$this->text = $text;
        $this->style = $style;
	}

	public function render(){
        $style = $this->style != null ? " style=\"".$this->style." !important\"" : "";
		echo '<div class="textListItem">';
		echo '<div class="text textcolorGray"'.$style.'>';
		echo '<span>'.$this->text.'</span>';
		echo '<div>';
		echo '</div>';
	}

}

?>
