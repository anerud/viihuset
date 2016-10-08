<?php
class houses {
	
	private $g, $errors, $esc, $cookie;
	public $info, $coll;
	
	public function __construct() {
		global $g;
		$this->g = $g;
		$this->errors = array();
		
		$this->house();
	}
	
	public function house() {
		
		$check = defined('USER_ID') && $this->g->page == 'admin'
					? sprintf("h_id = '%d'", HOUSE_ID)
					: sprintf("h_perma = '%s' or h_perma_proper = '%s'", $this->g->page, $this->g->page);
					
		$qry = $this->g->_query("select * from #_houses where {$check}");
		if($this->g->_count($qry) == 1) {
			$this->info = $this->g->_object($qry);
			
			$img  = image($this->info->h_perma, $this->info->h_id);
			$this->info->h_image = $img ? $img : $this->g->src('placeholder.png', 'gfx');
		}
		
		else $this->info = false;
	}
	
	public function content($dep) {
		$req  = array('p_title', 'p_content');
		$coll = $this->g->collect();
		$out  = array();
		$error = array();
		$attach = array();
		
		foreach($coll as $k => $v) {
			// strip off numbers
			preg_match('@(.*)_(\d+)$@', $k, $m);
			// enough counts
			if(count($m) == 3) {
				// number ][ field
				$out[$m[2]][$m[1]] = $v;
				$out[$m[2]]['p_order'] = $attach[] = $m[2];
				
				// Check requirements
				if(stristr(implode(', ', $req), $m[1]))
					$error[$m[2]] = true;
			}
		}
		// Arrange insertions
		foreach($out as &$o) {
												
			$o['user_id'] = USER_ID;
			$o['house_id'] = HOUSE_ID;
			$o['p_status'] = 'publish';
			$o['p_section'] = $dep;
			$o['p_created_at'] = time();
			
			if(empty($o['p_title']) && empty($o['p_content'])) continue;
			$sql[] = $this->g->impl('values', $o);
		}
		
		if(isset($sql)) {
			// Get first key
			list($key) = array_keys($out);
			// Set SQL params
			$fields = $this->g->impl('params', $out[$key]);
			// Glue values
			$params = implode(', ', $sql);
			
			// Remove documents and posts
			$this->remove($dep);
			
			// Insert new posts
			$this->g->_exec("insert into #_posts :fields values :params", array(':fields' => $fields, ':params' => $params));
		}
		else // Remove stuff if it's the only post
			$this->remove($dep);
			
		if(sizeof($this->errors) > 0)
			return errors($this->errors);
		else {
			$this->cookie = "Er information har blivit uppdaterad!";
			$this->destruct();
		}
	}
	
	private function remove($dep) {
		
		// Remove documents
		$attach = array_unique($attach);
		$qry = $this->g->_query("select p_order from #_posts where house_id = ? and p_status = 'publish' and p_section = ?", HOUSE_ID, $dep);
		while($res = $this->g->_result($qry)) {
			
			if(!in_array($res, $attach)) {
				$qm = $this->g->_query("select a_name from #_attachments where house_id = ? and a_page = ?", HOUSE_ID, $dep);
				while($rm = $this->g->_result($qm)) {
					$dir = sprintf("uploads/%s/documents/%s", HOUSE_ID, $rm);
					remove_dir($dir);
				}
				$this->g->_exec("delete from #_attachments
									where house_id = ? and a_page = ? and a_post = ?",
									HOUSE_ID, $dep, $res).BR;
				#echo "Remove attachments from $res".BR;
			}
		}
		
		// Remove previous posts
		$this->g->_exec("delete from #_posts where house_id = ? and p_status = 'publish' and p_section = ?", HOUSE_ID, $dep);
	}
	
	public function attachments($order, $house = 0, $out = array()) {
		$house = $house !== 0 ? $house : HOUSE_ID;
		if(!is_numeric($order)) {
			$qry = $this->g->_query("select * from #_attachments
										where house_id = ? and a_page = ? and a_type = 'documents'
										order by a_title asc", $house, $order);
		} else {
			$qry = $this->g->_query("select * from #_attachments
										where house_id = ? and a_post = ? and a_page = ? and a_type = 'documents'
										order by a_title asc", $house, $order, $this->g->v1);
		}
		if($this->g->_count($qry) == 0) return null;
		foreach($this->g->_object($qry, 1) as $res) {
			$mb = sprintf(" <span>(%s MB)</span>", round($res->a_size / 1024 / 1024 * 100) / 100);
			$out[] = sprintf("<div>%s<a href='%s' class='%s' target='_blank'>%s%s</a></div>",
							 	($this->g->page == 'admin' ? "<span class='delete'>Ta bort</span><span class='edit'>Byt namn</span>" : ''),
							 	sprintf("%suploads/%d/documents/%s", HTTP, $house, $res->a_name), $res->a_ext, $res->a_title,
								($this->g->page != 'admin' ? $mb : ''));
		}
		return implode("\r\n", $out);
	}
	
	public function album($house = 0, $amount = 'all', $ajax = '', $out = array()) {
		$house = $house !== 0 ? $house : HOUSE_ID;
		$sort  = $amount == 'all' ? 'desc' : 'desc limit 1';
		$qry = $this->g->_query("select * from #_attachments
									where house_id = ? and a_type = 'images'
									order by a_id {$sort}", $house);
		if($this->g->_count($qry) == 0) return null;
		foreach($this->g->_object($qry, 1) as $res) {
			$name = empty($res->a_title) ? '-' : $res->a_title;
			$mb   = sprintf("%s MB", round($res->a_size / 1024 / 1024 * 100) / 100);
			$thb  = image($res->a_name, $house, 'images', 'icon', 1, $ajax);
			$full = image($res->a_name, $house, 'images', '', 0, $ajax);
					
			$str  = "<div class='table'>";
			$str .= "<p class='thb'><a href='{$full}' rel='shadowbox' title='".$this->g->replace($res->a_title)."'>{$thb}</a></p>";
			$str .= "<p class='name'>{$name}</p>";
			$str .= "<p class='size'>{$mb}</p>";
			$str .= "<p class='modify'><a href='#' class='edit'>Ändra</a> / <a href='#' class='delete'>Ta bort</a></p>";
			$str .= "</div>";
			$out[] = $str;
		}
		
		return implode("\r\n", $out);
	}
	
	public function base() {
		$coll = $this->g->collect();
		
		foreach($coll as $k => $v) {
			if(preg_match('@^h_@', $k)) continue;
			$spec[$k] = $v;
			unset($coll[$k]);
		}
		// Loop thru special ones
		foreach($spec as $k => $v) {
			$s = explode('_', $k);
			$push[$s[1]][$s[0]] = $v;
		}
		$coll['h_people'] = $this->json_utf($push);
		
		$this->g->_exec("update #_houses set :params
							where h_id = ?", HOUSE_ID,
							array(':params' => $this->g->impl('update', $coll)));
		
		$this->cookie = "Din basinformation har uppdaterats!";
		$this->destruct();
	}
	
	public function create() {
		$coll = $this->g->collect();
		
		$address = $coll['reg_user'];
		$pass    = $coll['reg_pass'];
		$rem     = isset($coll['remember']) ? $coll['remember'] : 0;
		$coll['user_name'] = normalize($coll['reg_user']);
		$coll['user_name_proper'] = normalize($coll['reg_user'], true, true);
		$coll['user_email'] = $coll['reg_email'];
		
		$coll['user_pass'] = $this->encrypt($coll['reg_pass']);
		$coll['user_first'] = $address;
		
		$coll['user_version'] = SITE_VERSION;
		$coll['user_created_at'] = time();
		
		$qry  = $this->g->_query("select count(user_id) from #_users where user_name = ? limit 1", $coll['user_name']);
		$mail = $this->g->_query("select count(user_id) from #_users where user_email = ? limit 1", $coll['user_email']);
		
		if(empty($coll['user_name']) || empty($coll['reg_pass']))
			$this->errors[] = "Alla fält måste fyllas i.";
		
		if(!empty($coll['reg_pass']) && $coll['reg_pass'] != $coll['reg_re'])
			$this->errors[] = "Dina lösenord matchar inte.";
			
		if(!empty($coll['user_email']) && !preg_match("#([\w\d./-]+@[\w\d./-]+\.[\w]{2,4})#i", $coll['user_email']))
			$this->errors[] = "E-postadressen är inte giltig.";
		
		if($this->g->_result($qry) > 0)
			$this->errors[] = "Det finns redan ett konto med denna adress.";
		if(!empty($coll['user_email']) && $this->g->_result($mail) > 0)
			$this->errors[] = "Denna e-postadress är redan registrerad.";
		
		$person = array(array(
			'person' => '',
			'role' => '',
			'email' => $coll['reg_email'],
			'number' => ''
		));
			
		unset($coll['reg_user']);
		unset($coll['reg_email']);
		unset($coll['reg_pass']);
		unset($coll['reg_re']);
		unset($coll['remember']);
		
		if(sizeof($this->errors) == 0) {
			// Create user
			$this->g->_exec("insert into #_users :params", array(':params' => $this->g->impl('insert', $coll)));
			$user_id = $this->g->_last();
			
			// Create house connected to the user
			$this->g->_exec("insert into #_houses (user_id, h_perma, h_perma_proper, h_name, h_people, h_created) values (?, ?, ?, ?, ?, ?)", $user_id, $coll['user_name'], $coll['user_name_proper'], $address, json_encode($person, JSON_FORCE_OBJECT), time());
			$house_id = $this->g->_last();
			
			// Set house_id for the new user
			$this->g->_exec("update #_users set house_id = ? where user_id = ?", $house_id, $user_id);
			
			/*
			// Set default posts for house
			$this->g->_exec("insert into #_posts (user_id, house_id, p_status, p_title, p_content, p_section, p_order, p_created_at) values
								(:user, :house, :status, :title, :content, 'hem', 1, :time),
								(:user, :house, :status, :title, :content, 'omforeningen', 1, :time),
								(:user, :house, :status, :title, :content, 'omgivningen', 1, :time)",
								array(':user' => $user_id, ':house' => $house_id, ':status' => 'publish', ':time' => time(),
									  ':title' => 'Innehåll saknas', ':content' => 'Ingen information har blivit inlagd ännu.'));
			*/
			
			// Välkomen
			/*
			require_once('mail.class.php');
			$mail = new Email('register.html');
			$mail->recipients(array($coll['user_name'], $coll['user_email']));
			$mail->bind(array('user' => $address, 'password' => $pass, 'recover' => $this->g->server.$this->g->href('auth', 'losenord')));
			$mail->send();
			*/
			
			unset($_POST);
			$_POST['house'] = $house_id;
			$_POST['remember'] = $rem;
			$_POST['log_user'] = $coll['user_name'];
			$_POST['log_pass'] = $coll['user_pass'];
			
			global $auth;
			$auth->login();
		}
		return errors($this->errors);
	}
	
	public function image() {
		
		$coll = $this->g->collect();
		
		if(empty($coll['house']))
			errors('Du måste välja en förening');
			
		else {
			$qry = $this->g->_query("select h_id from #_houses where h_perma = ? or h_perma_proper = ?", $coll['house'], $coll['house']);
			
			$GLOBALS['override'] = $coll['house'];
			
			require_once('imagery.class.php');
			$cimg = new Imagery($this->g->_result($qry));
			
			$test = $coll['house'];
			if($info = $cimg->upload(766, 307, 'bild', true)) {
				$this->cookie = sprintf("Bilden har laddats upp för %s. <b><a target='_blank' href='%s'>Besök förening</a>.</b>", $coll['search'], $this->g->href($coll['house']));
				$this->destruct();
			}
		}
	}
	
	public function contact($house = true, $error = false, $arr = array()) {
		$coll = $this->g->collect();
		
		foreach($coll as $k => $v) {
			if(empty($v)) $error = true;
		}
		
		if($error) $this->errors[] = "Alla fält måste vara ifyllda.";
		if($this->info && empty($this->info->h_people))
			$this->errors[] = "Föreningen har inte lagt till några kontaktpersoner.";
		else {
			if($this->info) {
				foreach(json_decode($this->info->h_people) as $ppl) {
					if(empty($ppl->email)) continue;
					$arr[] = array($ppl->person, $ppl->email);
				}
				$name = $this->info->h_name;
				if(sizeof($arr) == 0) $this->errors[] = "Föreningen har inte lagt till några kontaktpersoner.";
			} else {
				$arr = array('Vi i Huset', 'info@viihuset.se');
				$name = 'Vi i Huset';
			}
		}		
		if(sizeof($this->errors) == 0) {
			if(sizeof($arr) > 0) {
				require_once($this->g->src('mail.class.php', 'classes', true));
				$mail = new Email('contact.html');
				$mail->recipients($arr);
				$mail->bind(array('fullName' => $name, 'subject' => $coll['csubject'], 'name' => $coll['cname'], 'email' => $coll['cmail'], 'message' => nl2br($coll['cmsg'])));
				$mail->send();
			}
			$this->cookie = "Ditt meddelande har skickats!";
			if(!$house) $this->esc = 'kontakt';
			$this->destruct();
		}
		return errors($this->errors);
	}
	public function menu($menu) {
		
		if(!$this->info) return;
		$hem = $this->g->_query("select count(*) from #_posts where house_id = ? and p_section = ? and p_status = 'publish'", $this->info->h_id, 'hem');
		$forening = $this->g->_query("select count(*) from #_posts where house_id = ? and p_section = ? and p_status = 'publish'", $this->info->h_id, 'omforeningen');
		$omgivning = $this->g->_query("select count(*) from #_posts where house_id = ? and p_section = ? and p_status = 'publish'", $this->info->h_id, 'omgivningen');
		$info = $this->g->_query("select count(*) from #_info where house_id = ?", $this->info->h_id);
		$foto = $this->g->_query("select count(*) from #_attachments where house_id = ? and a_page = ?", $this->info->h_id, 'fotoalbum');
		
		
		
		if($this->g->_result($hem) == 0) unset($menu['hem']);
		if($this->g->_result($forening) == 0) unset($menu['omforeningen']);
		if($this->g->_result($omgivning) == 0) unset($menu['omgivningen']);
		if($this->g->_result($info) == 0) unset($menu['maklarinfo']);
		if($this->g->_result($foto) == 0) unset($menu['fotoalbum']);
		
		return $menu;
	}
	// Get last post ID for New Post insertion
	public function insert($hID) {
		
		$qry = $this->g->_query("select p_order from #_posts where house_id = ? and p_status = 'publish' order by p_order desc limit 1", $hID);
		return $this->g->_count($qry) == 0 ? 1 : $this->g->_result($qry) + 1;
		
	}
	
	public function saveInfo($type = 'published') {
		
		$this->coll = $this->g->collect();
		// Unset addition inputs
		#unset($this->coll['common']);
		
		$spec = array();
		// Loop thru all values to shift out special ones
		foreach($this->coll as $k => $v) {
			if(!preg_match('@^i_@', $k)) {
				$spec[$k] = $v;
				unset($this->coll[$k]);
			}
		}
		// Loop thru special ones
		foreach($spec as $k => $v) {
			$s = explode('_', $k);
			if(!isset($s[1])) continue;
			// Special reparation
			if( ($s[0] == 'reparation' || $s[0] == 'additional') && empty($v)) continue;
			$push[$s[0]][$s[1]] = $v;
		}
		// Create JSON from special ones
		foreach($push as $k => $v) {
			$this->coll['i_'.$k] = $this->json_utf($v);
		}
		
		$this->coll['i_status'] = $type;
		$this->coll['house_id'] = HOUSE_ID;
		
		$this->g->_exec("delete from #_info where house_id = ? and i_status = ?", HOUSE_ID, $type);
			
		$this->g->_exec("insert into #_info :params", array(':params' => $this->g->impl('insert', $this->coll)));
		$this->cookie = "Du har uppdaterad din mäklarinformation!";
		if($type != 'preview') $this->destruct();
		
	}
	
	public function getInfo($type, $id = 0) {
		$type = $type != 'preview' ? 'published' : $type;
		$id = $id == 0 ? $this->info->h_id : $id;
		
		$qry = $this->g->_query("select * from #_info where house_id = ? and i_status = ? order by i_id desc limit 1", $id, $type);
		if($this->g->_count($qry) == 1) {
			$this->obj = $this->g->_object($qry);
			return true;
		}
		return false;
	}
	
	public function i_formed() {
		if(empty($this->obj->i_formed)) return;
		$txt = "Föreningen bildades år #i_formed";
		
		$this->wrap(
					array('#i_formed'),
					array($this->obj->i_formed),
					$txt
					);
	}
	
	public function i_year_built() {
		if(empty($this->obj->i_year_built)) return;
		$txt = "Fastigheten byggdes år #i_year_built";
		
		if(!empty($this->obj->i_renovated))
			$txt .= " och totalrenoverades år #i_renovated";
			
		$this->wrap(array('#i_year_built', '#i_renovated'),
					array($this->obj->i_formed, $this->obj->i_renovated),
					$txt
					);
	}
	
	public function i_living_area() {
		if(empty($this->obj->i_living_area)) return;
		$txt = "Föreningens totala boarea uppgår till #i_living_area kvadratmeter";
			
		$this->wrap(array('#i_living_area'),
					array($this->obj->i_living_area),
					$txt
					);
	}
	
	public function i_fee() {
		if(empty($this->obj->i_fee)) return;
		
		$txt = "I avgiften ingår: #i_fee";
		$fees = implode(', ', get_object_vars(json_decode($this->obj->i_fee)));
		$fees = $this->och($fees);
		
		if(!empty($this->obj->i_cable_provider))
			$txt .= ".<br />Kabel-tv/internet leverantör är #i_cable_provider";
			
		$this->wrap(array('#i_fee', '#i_cable_provider'),
					array($fees, $this->obj->i_cable_provider),
					$txt
					);
	}
	
	public function i_parking() {
		$txt = "Garageplats eller parkeringsplats #i_parking med bostadsrätten";
		$park = $this->obj->i_parking == 1 ? "följer" : "följer ej";
		
		if(!empty($this->obj->i_parking_lot))
			$txt .= ".<br />Möjlighet till parkering finns #i_parking_lot";
			
		$this->wrap(array('#i_parking_lot', '#i_parking'),
					array($this->obj->i_parking_lot, $park),
					$txt
					);
			
	}
	
	public function i_apartments() {
		if($this->obj->i_apartments == 0 && $this->obj->i_facilities == 0) return;
		$txt = "I föreningen finns #i_apartments lägenheter och #i_facilities lokal/er";
		
		foreach(json_decode($this->obj->i_rok) as $k => $v) {
			if(!empty($v) || $v === 0) {
				$print = true;
				$out[] = sprintf("%dst %s rok", $v, $k).($k == 8 ? ' eller större' : '');
			}
		}
		
		if($print) {
			$txt .= ". Uppdelning enligt följande:<br />";
			$txt .= implode(', ', $out);
			$txt = $this->och($txt);
		}
		
		$this->wrap(array('#i_apartments', '#i_facilities'),
					array($this->obj->i_apartments, $this->obj->i_facilities),
					$txt
					);
	}
	
	public function i_heating() {
		if(empty($this->obj->i_heating)) return;
		
		$txt = "Huset uppvärmning sker genom #i_heating";
		
		if(!empty($this->obj->i_heating_provider))
			$txt .= ", leverantör är #i_heating_provider";
			
		$this->wrap(array('#i_heating_provider', '#i_heating'),
					array($this->obj->i_heating_provider, $this->obj->i_heating),
					$txt
					);
	}
	
	public function i_admin() {
		if(empty($this->obj->i_admin_technical)) return;
		
		$txt = "Föreningens tekniska förvaltning sker via #i_admin_technical";
		
		if(!empty($this->obj->i_admin_economical))
			$txt .= " och den ekonomiska<br />förvaltningen sker via #i_admin_economical";
			
		
		$this->wrap(array('#i_admin_technical', '#i_admin_economical'),
					array($this->obj->i_admin_technical, $this->obj->i_admin_economical),
					$txt
					);
	}
	
	public function i_image() {
		if(empty($this->obj->i_image_economical)) return;
		
		$txt = "Ansökan om mäklarbild skickas till #i_image_economical";
		
		$this->wrap(array('#i_image_economical'),
					array($this->obj->i_image_economical),
					$txt
					);
	}
	
	public function i_entry_exit() {
		if(empty($this->obj->i_entry_exit)) return;
		
		$txt = "Ansökan om in- och utträde i föreningen skickas till #i_entry_exit";
		
		$this->wrap(array('#i_entry_exit'),
					array($this->obj->i_entry_exit),
					$txt
					);		
	}
	
	public function i_transfer() {
		if(empty($this->obj->i_transfer_charge)) return;
		
		$txt = "Föreningen tar ut en överlåtelseavgift om #i_transfer_charge";
		
		if(!empty($this->obj->i_transfer_charge_payer))
			$txt .= " som betalas av #i_transfer_charge_payer";
			
		if(!empty($this->obj->i_pawn_fee))
			$txt .= "<br />och en pantsättningsavgift om #i_pawn_fee";
			
		if(!empty($this->obj->i_pawn_fee_payer))
			$txt .= " som betalas av #i_pawn_fee_payer";
			
		$this->wrap(array('#i_transfer_charge_payer', '#i_transfer_charge', '#i_pawn_fee_payer', '#i_pawn_fee'),
					array($this->obj->i_transfer_charge_payer, $this->obj->i_transfer_charge, $this->obj->i_pawn_fee_payer, $this->obj->i_pawn_fee),
					$txt
					);		
	}
	
	public function i_legal_person() {
		$txt = "Föreningen #i_accept juridisk person som köpare";
		$acc = $this->obj->i_accept_legal_person == 1 ? "accepterar" : "accepterar ej";
		
		$this->wrap(array('#i_accept'),
					array($acc),
					$txt
					);
	}
	
	public function i_reperation($out = array(), $print = false) {
		
		$trans = array('pipes'       => 'Fastigheten är stambytt år ',
					   'electricity' => 'Elstigar är bytta år ',
					   'stairwells'  => 'Trapphus renoverades år ',
					   'washing'     => 'Tvättmaskiner byttes ut år ',
					   'facade'      => 'Fasaden renoverades/målades om år ');
		
		if(!empty($this->obj->i_reparation)) {
		foreach(json_decode($this->obj->i_reparation) as $k => $v) {
			if(!empty($v)) {
				$print = true;
				$out[] = !isset($trans[$k]) ? sprintf("%s", $v) : sprintf("%s%s", $trans[$k], $v);
			}
		} }
		
		if($print)
			$this->wrap(array(),
						array(),
						implode('.<br />', $out)
						);
	}
	
	public function i_electricity() {
		if(empty($this->obj->i_electricity_provider)) return;
		
		$txt = "Fastighetens elleverantör är #i_electricity_provider";
		
		$this->wrap(array('#i_electricity_provider'),
					array($this->obj->i_electricity_provider),
					$txt
					);
	}
	
	public function i_fee_incr_decr() {
		$txt = "Föreningen har i dagsläget planerat en avgiftshöjning / avgiftssänking:<br />";
		switch($this->obj->i_fee_incr_decr) {
			case 'incr' :
			case 'decr' :
				$val = $this->obj->i_fee_incr_decr == 'incr' ? 'avgiftshöjning' : 'avgiftssänkning';
				$txt .= "Ja, {$val} som avser #i_fee_percent% av nuvarande avgift fr.o.m. #i_fee_start";
			break;
			default:
				$txt .= "Nej, planerar ej avgiftshöjningen / avgiftssänking";
		}
		
		$this->wrap(array('#i_fee_percent', '#i_fee_start'),
					array($this->obj->i_fee_percent, $this->obj->i_fee_start),
					$txt
					);
	}
	
	public function i_known_changes() {
		$txt = "Föreningen #i_changes förändringar eller omläggningar i föreningens lån, räntesats eller dyl";
		$acc = $this->obj->i_known_changes == 1 ? "känner till" : "känner ej till";
		
		$this->wrap(array('#i_changes'),
					array($acc),
					$txt
					);
	}
	
	public function i_common_areas() {
		if(empty($this->obj->i_ca)) return;
		
		$txt = "Föreningen har följande genensamhetsutrymmen:<br />#i_ca";
		$incl = implode(', ', get_object_vars(json_decode($this->obj->i_ca)));
		$incl = $this->och($incl);
		
		$this->wrap(array('#i_ca'),
					array($incl),
					$txt
					);
	}
	
	public function i_second_hand() {
		if(empty($this->obj->i_second_hand)) return;
		
		$txt = "Vid andrahandsuthyrning av bostad gäller följande:<br />#i_second_hand";
		
		$this->wrap(array('#i_second_hand'),
					array(nl2br($this->obj->i_second_hand)),
					$txt
					);
	}
	
	public function i_multiple_residents() {
		if(empty($this->obj->i_multiple_residents)) return;
		
		$txt = "Vid delat boende där en eller fler parter ej bor permanent gäller följande:<br />#i_multiple_residents";
		
		$this->wrap(array('#i_multiple_residents'),
					array(nl2br($this->obj->i_multiple_residents)),
					$txt
					);
	}
	
	public function i_additional($out = array(), $print = false) {
		
		if(!empty($this->obj->i_additional)) {
		foreach(json_decode($this->obj->i_additional) as $k => $v) {
			if(!empty($v)) {
				$print = true;
				$out[] = sprintf("%s", $v);
			}
		} }
		
		if($print)
			$this->wrap(array(),
						array(),
						implode('.<br />', $out)
						);
	}
	
	private function wrap($search, $replace, $txt) {
		$txt = preg_replace("#([\w\d./-]+@[\w\d./-]+\.[\w]{2,4})#se", 'email("$1")', str_replace($search, $replace, $txt));
		printf("<div class='number'><b></b>%s.</div>\r\n", $txt);
	}
	
	private function och($txt) {
		return preg_replace('@, ([^,]+)$@', ' och $1', $txt);
	}
	
	private function json_utf($a) {
		return addcslashes(addcslashes(json_encode($a, JSON_FORCE_OBJECT), '\\'), '\\');
	}
	
	public function utf($txt) {
		return preg_replace('@u([a-fA-F0-9]{4})@', '&#x$1;', $txt);
	}
	
	private function encrypt($pass) {
		$salt = sha1(md5($pass).SALT);
		return md5($salt.$pass);
	}
	
	private function destruct() {
		
		if(!empty($this->cookie))
			cookie('success', $this->cookie);
			
		$this->esc = empty($this->esc)
			? $this->g->href($this->g->page, $this->g->v1)
			: $this->g->href($this->esc);
			
		$this->g->send($this->esc);
	}
}
?>