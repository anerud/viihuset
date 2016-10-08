<?php

class SliderText {
	
	public $sliderTitle;
	public $sliderText;
	
	public function __construct($sliderTitle, $sliderText) {
		
		$this->sliderTitle = $sliderTitle;
		$this->sliderText = $sliderText;
		bbcode($this->sliderText);
				
	}
	
}

?>