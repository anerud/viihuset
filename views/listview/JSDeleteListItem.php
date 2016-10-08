<?php
require_once("interfaces/iView.php");

class JSDeleteListItem implements iView{

	private $url;
	private $style;

	public function __construct($url, $style){
		$this->url = $url;
        $this->style = $style;
	}

	//Listener will be created in site.js
	public function render(){
        $style = $this->style != null ? " style=\"".$this->style." !important\"" : "";
		echo '<div class="JSDeleteListItem"'.$style.'>';
		echo '<a href="'.$this->url.'" class="delete">';
		echo '<img src="/gfx/delete_icon.png" alt="x" height="13px" width="13px">';
		echo '</a>';
		echo '</div>';
	}

}

?>
