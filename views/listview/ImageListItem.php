<?php
require_once("interfaces/iView.php");

class ImageListItem implements iView{

	private $src;
	private $style;
	public function __construct($imageType, $style){
		if(strpos($imageType, "image") !== false){
			$this->src = '/gfx/saveHover.png';
		} else if ($imageType == 'application/pdf') {
			$this->src = '/gfx/pdf_ikon.png';
		} else {
			$this->src = '/gfx/create.png';
		}
        $this->style = $style;
	}

	public function render(){
        $style = $this->style != null ? " style=\"".$this->style." !important\"" : "";
		echo '<div class="textListItem">';
		echo '<div class="text textcolorGray"'.$style.'>';
		echo '<img src="'.$this->src.'"></img>';
		echo '<div>';
		echo '</div>';
	}

}

?>
