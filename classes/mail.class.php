<?php
class Email {

	private $g, $args;
	private $template, $reci;
	private $to, $sub, $con;
	
	public function __construct($template, $help = 0) {
		$this->g = $GLOBALS['g'];
		$this->template = $template;
		
		$this->name = $this->g->set['site_name'];
		$this->from = $this->g->set['mail_from'];
		$this->reply = $this->g->set['mail_reply'];
		
		if(!preg_match('@\.html$@', $template)) {
			$qry = $this->g->_query("select mail_subject, mail_content from #_emails where mail_template = ?", strval($this->template));
			if($this->g->_count($qry) == 0 && !is_array($template)) return errors("E-post mallen ($template) kunde inte hittas.");
			list($this->sub, $this->con) = is_array($template) ? $template : $this->g->_row($qry);
		}
		else {
			$this->html();
		}
		
		if($help == 1) $this->help();
	}
	
	private function html() {
		$dir = preg_match('@ajax$@', getcwd())
				? '../mail/templates/'
				: sprintf("/mail/templates/", $this->g->set['site_version']);
		$con = $this->g->file_content($dir.$this->template);
		preg_match("@^Subject: (.*?)\r\n@", $con, $sub);
		if(sizeof($sub) == 0) return errors("No subject has been supplied in the template $this->template.");
		
		$this->sub = $sub[1];
		$this->sub  = "=?utf-8?B?".base64_encode($this->sub)."?=";
		
		$this->con = str_replace($sub[0], '', $con);
	}
	
	private function help() {
		preg_match_all("/##([^#]+)##/", $this->sub, $m1);
		preg_match_all("/##([^#]+)##/", $this->con, $m2);
		$this->g->debug(array_unique(array_merge($m1[1], $m2[1])));
	}
	
	public function recipients() {
		$args = func_get_args();
		if(sizeof($args) == 1 && is_array($args[0][0]))
			$args = $args[0];
		
		
		foreach($args as $a) $ar[] = is_array($a) ? "$a[0] <$a[1]>" : $a;
		$this->to = join(', ', $ar);
	}
	
	public function bind($args) {
		preg_match_all("/##([^#]+)##/", $this->sub, $m1);
		foreach($m1[1] as $match) {
			if(preg_match("/##$match##/i", $this->sub) && isset($args[$match]))
				$this->sub = str_replace("##$match##", $args[$match], $this->sub);
		}
		preg_match_all("/##([^#]+)##/", $this->con, $m2);
		foreach($m2[1] as $match) {
			if(preg_match("/##$match##/i", $this->con) && isset($args[$match]))
				$this->con = str_replace("##$match##", $args[$match], $this->con);
		}
	}
	
	public function send($code = 'html', $from = null) {
		
		if(!isset($this->to)) return errors("Ingen mottagare har blivit inkluderad.");
		if($this->sub == null) return;
		
		$from = $from ?: $this->name;
		$head  = "MIME-Version: 1.0\r\n";
		$head .= "Content-Type: text/{$code}; charset=utf-8\r\n";
		$head .= "X-Mailer: PHP/".phpversion()."\r\n";
		//text/html will be expecting a <body> tag
		$head .= "From: $from <$this->from>\r\n";
		$head .= "Reply-To: $this->name <$this->reply>\r\n";
		
		#echo $head;
		#$this->con .= $this->g->set['mail_signature'];
		
		// Remove before final
		if($_SERVER['SERVER_NAME'] == 'localhost') {
			var_dump($head);
			#echo $this->con;
			return true;
			return errors("I won't let you send it from Localhost, cause it will fail!");
		}
			
		// Adds a whole extra second to the timer
		if(!@mail($this->to, $this->sub, $this->con, $head))
			return errors("Ett fel uppstod n&auml;r brevet skulle skickas. ".htmlentities($this->to));
			
		cookie('success', 'Brevet skickades iv&auml;g ordentligt!');
		return true;
	}
	
	public function clear() {
		$this->to = null;
	}
	
	private function destruct() {
		
		cookie('success', 'Brevet skickades iv&auml;g ordentligt!');
		
		// history check
		header('location: ./');
		exit;
	}
}

/* SQL Structure

$install['emails'][] = "mail_id int(11) not null auto_increment primary key";
$install['emails'][] = "mail_template varchar(120) not null";
$install['emails'][] = "mail_subject varchar(255) not null";
$install['emails'][] = "mail_content text not null";
$install['emails'][] = "mail_created_at varchar(25) not null";
$install['emails'][] = "mail_updated_at varchar(25) not null";

$install['settings'][] = "mail_from varchar(150) not null";
$install['settings'][] = "mail_reply varchar(150) not null";

## Raw

*/
?>