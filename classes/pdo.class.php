<?php
/*** PDO mySQL class    [22/07/2011]
  *
  * review api.php for usage
  *
***/

class SQL {
	
	private $db, $st;
	public $cfg;
	private static $regEx = "placeholders?|db|tables?|fields?|params?|sort";
	
	public function __construct() {
		$conf = new AppConfig();
		
		$this->cfg = $conf->cfg;

		if($cfg['conn']) {
			$this->open();
		}
			
		if(isset($_POST)) { foreach ($_POST as $key => $value) $_POST[$key] =  @stripslashes($this->replace($value)); }
		if(isset($_GET)) { foreach ($_GET as $key => $value) $_GET[$key] = utf8_encode($this->replace($value)); }
	}
	
	private function config() {
		foreach($this->cfg['const'] as $key => $value) {
			if(!defined(strtoupper($key)))
				define(strtoupper($key), $value);
		}
		
		/* Dummy fixes */
		$this->cfg['g']['http'] = sprintf("%s%s%s", (substr($this->cfg['g']['http'], 0, 1) != '/' ? '/' : ''),
												$this->cfg['g']['http'],
											  (substr($this->cfg['g']['http'], -1) != '/' ? '/' : ''));
		$this->cfg['prefix'] = !preg_match('/_$/', $this->cfg['prefix']) ?
										sprintf("%s_", $this->cfg['prefix']) : $this->cfg['prefix'];
		unset($GLOBALS['cfg']);
	}
	
	private function open() {
		$conn = sprintf("%s:host=%s;dbname=%s", $this->cfg['type'], $this->cfg['host'], $this->cfg['data']);
		try {
			$this->db = new PDO($conn, $this->cfg['user'], $this->cfg['pass'], array(
										PDO::ATTR_PERSISTENT => true,
										PDO::ATTR_ERRMODE    => PDO::ERRMODE_EXCEPTION,
										PDO::ATTR_CASE       => PDO::CASE_NATURAL
										#PDO::MYSQL_ATTR_INIT_COMMAND => "set names latin1"
								));
			
			$this->_exec("set names :db", array(':db' => str_replace('-', '', $this->cfg['names'])));
		} catch(PDOException $e) {
			$this->_error($e);
		}
	}
	
	private function table($str) {
		$s = preg_replace('/\#_([a-zA-Z0-9_]+)/', '$1', $str);
		return $s;
	}
	
	public function params($str) {
		$regEx = "/(?<!['\\\])\?/";
		$args = func_get_args(); 
		array_shift($args);
		if(isset($args[0]) && is_array($args[0]) && isset($args[0][0])) {
			$args = $args[0];
		}
		if(sizeof($args) == 0) return $str;
		preg_match_all($regEx, $str, $matches);
		
		$a = 0; $t = 0;
		$s = array(sizeof($matches[0]), sizeof($args), 0);
		foreach($args as $check) { if(!is_array($check)) $s[2]++; }
		
		foreach($matches[0] as $match) {
			if(isset($args[$a]) && is_array($args[$a])) { $va = $a; ++$a; if(!isset($args[$a])) $end = true; }
			if(!isset($args[$a]) || isset($end)) return "You have not supplied enough parameters; expected {$s[0]} but received only {$s[2]}.";
			$str = preg_replace($regEx, $this->db->quote($args[$a]), $str, 1);
			$t++;
		$a++;
		}
		if(!isset($va) && $s[0] < $s[1]) $va = $s[1] - 1;
		if(isset($va)) {
			if(!isset($args[$va])) $va--;
			if(sizeof($args[$va]) > 0) { $t++;
				foreach($args[$va] as $key => $value) {
					$replace = !preg_match('/:('.self::$regEx.')/', $key) ? $this->db->quote($value) : $value;
					$str = preg_replace("/$key/i", $replace, $str);
				}
			}
		}
		return $str;
	}
	
	public function show() {
		$q = $this->db->query("show tables");
		foreach($q->fetchAll() as $t) {
			$out[] = $t[0];
		}
		return $out;
	}
	
	public function _exec($str) {
		$args = func_get_args(); array_shift($args);
		#if(isset($args[0]) && is_array($args[0]))
		#	$args = $args[0];
		$str = $this->table($this->params($str, $args));
		return $this->attempt($str);
	}
	
	public function _query($str) {
		$args = func_get_args(); 
		array_shift($args);
		$asdf =  $this->table($str);
		$asdfDB = $this->db;
		$asdf123 = $asdfDB->prepare($asdf);
		$this->st[] = $asdf123;
		$id = sizeof($this->st) - 1;
		
		$this->bind($id, $args);
		return $this->attempt($id);
	}
	
	public function _range($str) {
		$args = func_get_args(); array_shift($args);
		$limit = array_pop($args);
		$start = array_pop($args);
		$this->st[] = $this->db->prepare(sprintf("%s limit %d, %d", $this->table($str), $start, $limit));
		$id = sizeof($this->st) - 1;
		
		$this->bind($id, $args);
		return $this->attempt($id);
	}
	
	public function _array($id, $all = 0) {
		if($all == 1)
			return $this->st[$id]->fetchAll(PDO::FETCH_ASSOC);
		return $this->st[$id]->fetch(PDO::FETCH_ASSOC);
	}
	
	public function _row($id) {
		return $this->st[$id]->fetch(PDO::FETCH_NUM);
	}
	
	public function _rows($id) {
		return $this->st[$id]->fetchAll(PDO::FETCH_NUM);
	}
	
	public function _result($id) {
		return $this->st[$id]->fetch(PDO::FETCH_COLUMN);
	}
	
	public function _object($id, $all = 0) {
		if($all == 1)
			return $this->st[$id]->fetchAll(PDO::FETCH_OBJ);
		return $this->st[$id]->fetch(PDO::FETCH_OBJ);
	}
	
	public function _count($id) {
		if($id === '') return 0;
		return $this->st[$id]->rowCount();
	}
	
	public function _last() {
		return $this->db->lastInsertId();
	}
	
	public function _close() {
		$this->db = null;
	}
	
	private function _error($e) {
		
		switch($e->getCode()) {
			case '0' :
			case '1045' :
				$code = array('[1045]', 7);
				break;
			default :
				$code = array(': ', 2);
		}
		printf('%s:%d (SQLState %s) [class %s]%s%s%5$s',
					basename($e->getFile()),
					$e->getLine(),
					$e->getCode(),
					get_class(), BR,
					substr($e->getMessage(), strpos($e->getMessage(), $code[0]) + $code[1])
				);
		exit;
	}
	
	private function type($str) {
		
		if(is_bool($str)) return PDO::PARAM_BOOL; // 5
		elseif(is_int($str)) return PDO::PARAM_INT; // 1
		elseif(is_null($str)) return PDO::PARAM_NULL; // 0
		else return PDO::PARAM_STR; // 2
	}
	
	private function bind($id, $args) {
		if(isset($args[0]) && is_array($args[0]))
			$args = $args[0];
			
		foreach($args as $key => $value) {
			if(is_numeric($key)) { $next = true; break; }
			$this->st[$id]->bindValue($key, $value, $this->type($value));
		}
		if(isset($next)) { $n = 1;
			foreach($args as $value) {
				$this->st[$id]->bindValue($n, $value, $this->type($value));
			$n++;
			}
		}
	}
	
	private function attempt($id) {
		try {
			#var_dump($id);
			if(is_numeric($id)) {
				$this->st[$id]->execute();
				return $id;
			} else {
				return (!preg_match('/^You have not supplied enough parameters;/', $id) ? $this->db->exec($id) : $id);
			}
		} catch(PDOException $e) {
			$this->_error($e);
		}
	}
	
	public function replace($str) {
		if(!is_array($str)) {
			$str = str_replace(array('"', "'", ">", "<", "{", "}", "(", ")"),
									 array('&#34;', '&#39;', '&#62;', '&#60;', '&#123;', '&#125;', '&#40;', '&#41;'), $str);
			if(!get_magic_quotes_gpc()) $str = addslashes($str);
		}
		return $str;
	}
	
	public function html_reverse($text, $x = false) {
		$sub = array('&#34;', '&#39;', '&#62;', '&#60;', '&#123;', '&#125;', '&#40;', '&#41;');
		$obj = array('"', "'", ">", "<", "{", "}", "(", ")");
		
		if($x == 'single') {
			unset($sub[1]);
			unset($obj[1]);
		}
		return str_replace($sub, $obj, $text);
	}
}
?>