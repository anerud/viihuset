<?php
// include extension
$file = $pdo = 'classes/pdo.class.php';

require_once($pdo);
unset($file);

class Globals extends SQL {
	
	public $page, $dep, $mod, $act, $post, $p;
	public $v0, $v1, $v2, $v3, $m, $k, $f;
	public $qstr, $ip, $set, $server;
	private $stimer;
	
	public function __construct() {
		$this->stimer = $this->timer();
		$this->p(); //pagination
		
		// Load SQL parent class
		parent::__construct();
		$this->settings();
		
		// Set available page variables
		$this->page = $this->v0 = isset($_GET['page']) ? strtolower($_GET['page']) : 'start'; //page
		$this->dep  = $this->v1 = isset($_GET['v1']) ? strtolower($_GET['v1']) : ''; //department
		$this->mod  = $this->v2 = isset($_GET['v2']) ? strtolower($_GET['v2']) : ''; //moderate
		$this->act  = $this->v3 = isset($_GET['v3']) ? strtolower($_GET['v3']) : ''; //action
		$this->post = $this->v4 = isset($_GET['v4']) ? strtolower($_GET['v4']) : ''; //final action
		
		#$this->m = utf8_encode(Globals::$meta);
		#$this->k = utf8_encode(Globals::$keys);
		$this->m = $this->cfg['seo']['desc'];
		$this->k = $this->cfg['seo']['keys'];
		$this->f = $this->cfg['seo']['flw'];
		$this->c = $this->cfg['g']['charset'];
		
		$this->qstr = $_SERVER['QUERY_STRING'];
		$this->ip   = $_SERVER['REMOTE_ADDR'];
		
		if(!defined('HTTP')) define('HTTP', $this->cfg['g']['http']);
		date_default_timezone_set($this->cfg['g']['timezone']); //Default timezone
		
		$this->server = sprintf("http://%s%s", preg_replace('/^www\./i', '', $_SERVER['SERVER_NAME']),
									$this->cfg['g']['port'] ? ':'.$_SERVER['SERVER_PORT'] : '');
	}
	
	public function collect($join = 'none', $prefix = array(), $ignore = array(), $out = array()) {		
		foreach($_POST as $key => $value) {
			if(isset($ignore) && in_array($key, $ignore)) continue;
			if(isset($prefix) && sizeof($prefix) == 2 && preg_match("/^{$prefix[0]}\_?/", $key))			
				$key = preg_replace("/^{$prefix[0]}\_?/", "{$prefix[1]}_", $key);
			
			$out[$key] = $value;
		}
		return $this->impl($join, $out);
	}
	
	public function impl($join, $out) {
		if(!is_array($out)) return false;
		switch($join) {
			case 'update' :
				foreach($out as $key => $value)
					$vs[] = sprintf("%s = '%s'", $key, $value);
				return join(', ', $vs);
			case 'insert' :
				return sprintf("(%s) values ('%s')", join(', ', array_keys($out)), join("', '", $out));
			case 'params' :
				return sprintf("(%s)", join(', ', array_keys($out)));
			case 'values' :
				return sprintf("('%s')", join("', '", $out));
			default : return $out;
		}
	}
	
	public function src($file, $dir = '', $strip = false) {
		$url = sprintf("/_inc/", (!$strip ? HTTP : ''), $this->set['site_version']);
		
		if($dir == 'gfx') $url = str_replace('_inc', 'gfx', $url);
		elseif(!empty($dir)) $url .= sprintf("_%s/", $dir);
		return $url.$file;
	}
	
	public function href() {
		$args = func_get_args();
		if(isset($args[0]) && is_array($args[0])){
			$args = $args[0];
		}
		
		
		$url = implode('/', $args);
		return preg_replace('#[\/]{2,}#', '/', sprintf("%s%s", HTTP, $url));
	}
	
	public function currentPage() {
		return $this->href($this->page, $this->v1, $this->v2, $this->v3, $this->v4);
	}
	
	public function uri($page = '') {
		$u = HTTP != '/' ? str_replace(HTTP, '', $_SERVER['REQUEST_URI']) : $_SERVER['REQUEST_URI'];
		if($page === '' || $page === 0)
			return ($page === '' ? $u : substr($u, 0, strrpos($u, '/')));
		
		preg_match_all('@\b[\w\d-_]+\b@', $u, $m);
		return in_array($page, $m[0]);
	}
	
	public function ispage($page = '') {
		return ($page == $this->page ? true : false);
	}
	
	public function get($strip = 0, $uri = '', $e = array()) {
		$e['final'] = false;
		$pos = strrpos($this->uri(), '?');
		
		if($pos || preg_match('@^\?@', $this->uri())) {
			$uri = substr($this->uri(), $pos + 1);
			
			$e['full'] = explode('&', $uri);
			if(count($e) > 0) {
				foreach($e['full'] as $f) {
					$ex = explode('=', $f);			
					if(count($ex) > 0)
						$e['final'][$ex[0]] = isset($ex[1]) ? $ex[1] : '';
				}
			}
		} else $pos = strlen($this->uri()) + 1;
		
		switch($strip) {
			case 1:
				return substr($this->uri(), 0, $pos);
			case 2:
				return preg_replace('|^(.{8})|', '$1'.HTTP, substr($this->uri(), $pos));
			default:
				return $e['final'];
		}
	}
	
	public function file_content($url, $curl = 0) {
		
		if(ini_get('allow_url_fopen') == 0 || $curl == 1) {
			$ch = curl_init();
			curl_setopt_array($ch, array(CURLOPT_URL => $url,
										 CURLOPT_HEADER => false,
										 CURLOPT_RETURNTRANSFER => true));
			$con = curl_exec($ch);
			curl_close($ch);
		} else {
			if(!$con = @file_get_contents($url))
				$this->file_content($url, 1);
		}
		
		return (!$con ? "Failed to retrieve." : $con);
	}
	
	private function p() {
		$this->p = 1;
		foreach($_GET as $key => $value) {
			if(preg_match('/^p=([0-9]+)$/', $value, $match)) break;
			else unset($key);
		}
		
		if(isset($key)) {
			if($key == 'page') {
				$_GET[$key] = 'home';
			}
		$this->p = $match[1];
		}
	}
	
	private function timer() {
		$time = explode(" ", microtime()); 
		return $time[1] + $time[0];
	}
	
	public function end_timer($deci = 3) {
		$load = $this->timer() - $this->stimer;
		return number_format($load, $deci);	
	}
	
	public function generate($length = 7, $num = 4, $ast = 1, $out = '') {
		
		$num = $num <= 0 ? 1 : $num;
		$letters = str_split('AaBbCcDdEeFfGgHhiJjKkLMmNnOoPpQqRrSsTtUuVvWwXxYyZz');
		$numbers = str_split('0123456789');
		
		$places  = range(1, $length);
		$asterix = rand(1, sizeof($places)); unset($places[$asterix]);
		for($n = 0; $n < $num; $n++) {
			$nums[]  = rand(1, sizeof($places)); unset($places[$nums[$n]]);
		}
		
		for($i = 1; $i <= $length; $i++) {
			if($asterix == $i && $ast == 1) $out .= '*';
			elseif(in_array($i, $nums)) $out .= $numbers[rand(0, sizeof($numbers) - 1)];
			else $out .= $letters[rand(0, sizeof($letters) - 1)];
		}
		return $out;
	}
	
	private function settings() {
		if(!preg_match("/install\.php$/", $_SERVER['SCRIPT_NAME']))
			$this->set = $this->_array($this->_query("select * from #_settings"));
	}
	
	public function send($url) {
		if(isset($url)) {
			header("location: $url");
			exit;
		}
		return false;
	}
	
	public function debug($a) {
		if(sizeof($a) == 0) {
			echo "No result";
			return;
		}
		echo "<pre>";
		print_r($a);
		echo "</pre>";
	}
}
?>