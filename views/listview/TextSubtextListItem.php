<?php
require_once("interfaces/iView.php");

class TextSubtextListItem implements iView{
	
	private $title;
	private $subtitle;
    private $style;
	
	public function __construct($title, $subtitle, $style){
		$this->title = $title;
		$this->subtitle = $subtitle;
        $this->style = $style;
	}
	
	public function render(){
        $style = $this->style != null ? " style=\"".$this->style." !important\"" : "";
		echo '<div class="titleSubtitleListItem">';
		echo '<div class="textcolor0"'.$style.'>';
		echo $this->title;
		echo '</div>';
		echo '<div class="subtitle textcolorGray"'.$style.'>';
		echo $this->subtitle;
		echo '</div>';
		echo '</div>';
	}
	
}

?>