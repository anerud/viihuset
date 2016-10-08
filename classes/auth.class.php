<?php
## Prerequisits
##	 - globals.class.php
##	 - email.class.php (needed for recovery)
	
class Auth {
	
	private $g, $esc;
	private $user, $pass;
	private $cookie, $db, $name, $url;
	private $errors = array();
	
	private static $salt = SALT;
	
	public function __construct() {
		$this->g  = $GLOBALS['g'];
		$this->db = '#_users';
		
		$this->logged();
		$this->logout();
	}
	
	private function logged() {
		if( (isset($_COOKIE[PRE."remember_id"]) && isset($_COOKIE[PRE."remember_pid"]) && !isset($_SESSION[PRE."override"]) )
			|| (isset($_COOKIE[PRE."override_id"]) && isset($_COOKIE[PRE."override_pid"]) )) {
			$_SESSION[PRE."user"] = isset($_COOKIE[PRE."override_id"]) ? $_COOKIE[PRE."override_id"] : $_COOKIE[PRE."remember_id"];
			$_SESSION[PRE."pass"] = isset($_COOKIE[PRE."override_pid"]) ? $_COOKIE[PRE."override_pid"] : $_COOKIE[PRE."remember_pid"];
			$_SESSION[PRE."rank"] = isset($_COOKIE[PRE."override_rank"]) ? $_COOKIE[PRE."override_rank"] : $_COOKIE[PRE."remember_rank"];
			
			if(isset($_COOKIE[PRE."override_rank"]))
				$_SESSION[PRE."override"] = true;
		}

		if(isset($_SESSION[PRE."user"]) && isset($_SESSION[PRE."pass"])) {
			
			$query = $this->g->_query("select user_id, house_id, user_name, user_rank from $this->db
									  	where user_id = ? and user_pass = ?",
										array($_SESSION[PRE."user"], $_SESSION[PRE."pass"]));
			if($this->g->_count($query) == 1) {
				extract($this->g->_array($query));
				if(!defined('USER_ID')) define('USER_ID', $user_id);
				if(!defined('USER_NAME')) define('USER_NAME', $user_name);
				if(!defined('USER_RANK')) define('USER_RANK', $user_rank);
				if(!defined('HOUSE_ID')) define('HOUSE_ID', $house_id);
			}
		}
	}
	
	public function login() {
		$this->user = isset($_POST['log_user']) ? $_POST['log_user'] : '';
		if(!strpos($this->user, '@')) $this->user = normalize($this->user);
		
		$this->pass = isset($_POST['log_pass']) ? $this->encrypt($_POST['log_pass']) : '';
		if(isset($_POST['house'])) $this->pass = $_POST['log_pass'];
		
		$query = $this->g->_query("select user_id, user_name, user_rank, user_pass from $this->db
								  	where (user_email = ? or user_name = ?) and user_pass = ?", $this->user, $this->user, $this->pass);
		
		if($this->g->_count($query) == 0)
			$this->errors[] = "Adress eller lösenord är felaktigt.";
		
		if(sizeof($this->errors) == 0) {
			extract($this->g->_array($query));
			$this->g->_exec("update $this->db set user_ip = ? where user_name = ?", array($this->g->ip, $user_name));
			
			if($user_rank == 0) { $this->esc = 'logout'; $this->logout(); }
			if(isset($_POST["remember"]) && $_POST["remember"] == 1) {
				$this->cookie(PRE."remember_id", $user_id, 60 * 60 * 48 * 7);
				$this->cookie(PRE."remember_pid", $user_pass, 60 * 60 * 48 * 7);
				$this->cookie(PRE."remember_rank", $user_rank, 60 * 60 * 48 * 7);
			}
			
			$_SESSION[PRE."user"] = $user_id;
			$_SESSION[PRE."pass"] = $user_pass;
			$_SESSION[PRE."rank"] = $user_rank;
			
			if($user_rank >= 3)
				logs("@house has logged in.", $user_id);
			
			$this->rcookie('user_name');
			#$this->cookie = "Du är inloggad som $user_name!";
			$get = $this->g->get();
			$this->esc = $get != false && isset($get['return']) ?
				$get['return'] : 'admin';
			$this->destruct();
		}
		return errors($this->errors);
	}
		
	public function logout($exe = false) {
		
		if($this->g->page == 'logout' || $this->esc == 'logout' || $exe) {
			
			session_unset();
			session_destroy(); 
			unset($_SESSION[PRE."user"]);
			
			$this->rcookie(PRE."remember_id");
			$this->rcookie(PRE."remember_pid");
			$this->rcookie(PRE."remember_rank");
			
			$this->rcookie(PRE."override_id");
			$this->rcookie(PRE."override_pid");
			$this->rcookie(PRE."override_rank");
			
			$this->cookie = "Du har loggats ut ordentligt!";
			$get = $this->g->get();
			$this->esc = $get != false && isset($get['return']) ?
				$get['return'] : 'admin';
			
			$this->destruct();
		}
	}
	
	private function rcookie($name) {
		setcookie($name, '', time() - 3600, '/');
	}
	private function cookie($name, $value, $time = 3) {
		setcookie($name, $value, time() + $time, '/');
	}
	
	public function security($go = true) {
		global $uInfo;
		$pass[] = isset($_POST['old']) ? $_POST['old'] : '';
		$pass[] = isset($_POST['new']) ? $_POST['new'] : '';
		$pass[] = isset($_POST['repeat']) ? $_POST['repeat'] : '';
		
		if(!empty($pass[0])) {
			if($uInfo['user_pass'] != $this->encrypt($pass[0])) $this->errors[] = "Ditt nuvarande lösenord matchar inte.";
			elseif(strlen($pass[1]) < 6) $this->errors[] = "Det nya lösenordet måste vara längre än fem tecken.";
			elseif($pass[1] != $pass[2]) $this->errors[] = "Det gamla och nya lösenordet matchar inte.";
		} else $this->errors[] = "Du måste fylla i formuläret nedan för att fortsätta.";
		
		if(sizeof($this->errors) == 0) {
			$npw = $this->encrypt($pass[1]);
			$qa = array();
			
			if(!empty($pass[0])) {
				$qa[] = "user_pass = :pass";
				$qc[] = " ditt lösenord";
				$_SESSION[PRE."pass"] = $npw;
			}
			
			$this->g->_exec("update $this->db set :params where user_id = :id",
								array(':params' => join(', ', $qa), ':pass' => $npw, ':id' => USER_ID)
							);
			
			$this->cookie = sprintf("Du har lyckats ändra%s!", join(' och', $qc));
			$this->esc = 'admin'; $this->destruct();
		}
		return errors($this->errors);
	}
	
	public function user($id = 0, $mean = 0) {
		$fld = preg_match("/@/", $id) ? 'user_email' : 'user_id';
		
		$id  = $id == 0 && defined('USER_ID') ? USER_ID : $id;
		$qry = $this->g->_query("select * from $this->db where $fld = ?", $id);
		if($this->g->_count($qry) == 0) return;
		
		$res = $mean == 0 ? $this->g->_array($qry) : $this->g->_row($qry);
		return $res;
	}
	
	public function pass() {
	
		$this->email = isset($_POST['lost_email']) ? $_POST['lost_email'] : '';
		$query = $this->g->_query("select user_first from $this->db where user_email = ?", $this->email);
			if($this->g->_count($query) == 0 || empty($this->email))
				return errors("Inget konto kunde hittas med denna e-postadress.");
		$result = $this->g->_result($query);
		$hash   = $this->encrypt($this->g->generate());
		$link   = $this->g->server.$this->g->href('auth', 'aterhamta', $hash);
		
		require_once('mail.class.php');
		$mail = new Email("recover.html");
		$mail->recipients(array($result, $this->email));
		$mail->bind(array('siteName' => $this->g->set['site_name'], 'fullName' => $result, 'generated' => $link, 'ip' => $this->g->ip));
		
		if($mail->send('html') === true) {
			$this->g->_exec("update $this->db set user_hash = ?, user_requested = ? where user_email = ?", $hash, time(), $this->email);
			$this->cookie = "Ett e-post har skickats till $this->email. Ta även en titt i din Skräppost för säkerhets skull.";
			$this->esc = 'auth'; $this->destruct();
		}
	}
	
	public function recover() {
		$c = $this->g->collect();
		
		if(sizeof($c) != 3) return errors("Det saknas några värden...");
		$qry = $this->g->_query("select * from $this->db where user_hash = ? and user_requested < ? limit 1", $c['hash'], time() + 60 * 60 * 24);
		if($this->g->_count($qry) == 0)
			return errors("Ingen aktiv återhämtningsprocess var funnen. Var god starta om processen.");
			
		else {
			if(strlen($c['new']) < 6) $this->errors[] = "Det nya lösenordet måste vara längre än fem tecken.";
			elseif($c['new'] != $c['re']) $this->errors[] = "Det gamla och nya lösenordet matchar inte.";
			
			if(sizeof($this->errors) == 0) {
				$this->g->_exec("update $this->db set
									user_hash = '',
									user_requested = '',
									user_pass = ?
								where user_hash = ?", $this->encrypt($c['new']), $c['hash']);
				$this->cookie = "Du har lyckats sätta ett nytt lösenord för ditt konto.";
				$this->esc = 'admin'; $this->destruct();
			}
			return errors($this->errors);
		}
	} 
	
	public function banned() {
		$q = $this->g->_query("select * from $this->db where user_ip = ? and user_rank = 0", $this->g->ip);
		return ($this->g->_count($q) == 0 ? false : true);
	}
	
	private function encrypt($pass) {
		$salt = sha1(md5($pass).self::$salt);
		return md5($salt.$pass);
	}
	
	private function destruct() {
		$destination = $this->esc != '' ? $this->g->href($this->esc) : '';
		$this->cookie('success', $this->cookie);
		
		if(isset($_SESSION[PRE.'return'])) {
			$destination = $_SESSION[PRE.'return'];
			unset($_SESSION[PRE.'return']);
		}
		header("location: $destination");
		exit;
	}
}
?>