<?php
/*** Image uploading class    [28/07/2011]
  * 
  * no pre-requisits necessary except a folder in root called "images" with chmod 0777
  * 
***/

class Imagery {
	
	public $dir;
	private $img, $gen, $type, $exact;
	private $name, $wmax, $hmax, $main, $re, $err;
	private $n, $w, $h, $m;
	
	public function __construct($dir, $id = 0, $ajax = '') {
		$this->ini();
		$this->dir = sprintf("%suploads/%s/", $ajax, trim($dir, '/'));
		@mkdir("$this->dir", 0777);
		
		if($id !== 0) {
			$this->dir .= sprintf("%s/", trim($id, '/'));
			@mkdir("$this->dir", 0777);
		}
		
		@mkdir("{$this->dir}thumbs/", 0777);
		$this->err = false;
	}
	
	private function ini() {
		$mem = substr(ini_get('memory_limit'), 0, -1);
		if($mem < 48) ini_set('memory_limit', '48');
	}
	
	public function upload($wmax, $hmax, $input, $exact = 0) {
		if($this->err == true) return;
		global $override;
		
		$this->exact = $exact;
		$this->gen = isset($override) ? $override : $this->generate();
		$this->wmax = $wmax;
		$this->hmax = $hmax;
		$this->thb = $input;
		
		$this->img = isset($_FILES[$input]) ? $_FILES[$input] : $this->img;
		
		if(empty($this->img['tmp_name'])) return errors("Du måste välja en bild att ladda upp.");
		if(!isset($this->main)) {
			$this->type = $this->mime();
			if(empty($this->type)) return errors("Filen du försöker ladda upp är av fel filtyp.");
			
			$this->name = sprintf("%s.%s", $this->gen, $this->type);
			list($this->m['w'], $this->m['h']) = getimagesize($this->img['tmp_name']);
			
			if(!move_uploaded_file($this->img['tmp_name'], $this->dir.$this->name)) {
				$this->err = true; return false;
			}
			$this->main = $this->dir.$this->name;
		}
			
		// check sizes and scale
		$this->reshape();
		
		// move_uploaded_file();
		$this->move();
		
		// set attributes to be returned
		$attr = array('name' => $this->n,
					  'width' => $this->w,
					  'height' => $this->h,
					  'org_width' => $this->m['w'],
					  'org_height' => $this->m['h']);
		
		// refresh variables for next execution
		unset($this->w);
		unset($this->h);
		unset($this->exact);
		return $attr;
	}
	
	private function move() {
		
		if(isset($this->re)) {
			$this->n = sprintf("%s_%s", $this->thb, $this->name);
			$this->dir .= "thumbs/";
		} else $this->n = $this->name;
		
		$this->re = true;
		$ndir     = $this->dir.$this->n;

		if($this->type == 'png') $image = imagecreatefrompng($this->main);
		if($this->type == 'jpeg' ||
		   $this->type == 'jpg') $image = imagecreatefromjpeg($this->main);
		if($this->type == 'gif') $image = imagecreatefromgif($this->main);
		
		$x = imagesx($image);
		$y = imagesy($image);

		$thumb = imagecreatetruecolor($this->w, $this->h);
		imagecopyresampled($thumb, $image, 0, 0, 0, 0, $this->w, $this->h, $x, $y);
		
		if($this->type == 'png') imagepng($thumb, $ndir);
		if($this->type == 'jpeg' ||
		   $this->type == 'jpg') imagejpeg($thumb, $ndir, 100);
		if($this->type == 'gif') imagegif($thumb, $ndir);
		
		$this->dir = str_replace('thumbs/', '', $this->dir);
	}
	
	private function reshape() {
		
		$this->h = $this->m['h'];
		$this->w = $this->m['w'];
		
		if($this->exact == 1) {
			$this->h = $this->hmax;
			$this->w = $this->wmax;
			return;
		}
		
		if($this->h > $this->hmax) {
			$percent = $this->hmax / $this->h;
			$this->h = $this->hmax;
			$this->w = round($percent * $this->w); 
		}
		
		if($this->w > $this->wmax) {
			$percent = $this->wmax / $this->w;
			$this->w = $this->wmax;
			$this->h = round($percent * $this->h);
		}
	}
	
	private function generate($str = '') {
	
		$letters = range('a', 'z');
		for($l = 1; $l <= 3; $l++)
			$str .= $letters[rand(0, 25)];
			
		for($n = 1; $n <= 9; $n++)
			$str .= rand(0, 9);
		
	return $str;
	}
	
	private function mime() {
		switch($this->img['type']) {
			case 'image/pjpeg' :
			case 'image/jpeg'  :
			case 'image/jpg'   : return 'jpg'; break;
			case 'image/png'   : return 'png'; break;
			case 'image/gif'   : return 'gif'; break;
		}
	}
}
?>