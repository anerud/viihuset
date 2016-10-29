<?php
/***
  * For full reference i advise to check _docs/api.php under "Functions"-commentry
***/

function title() {
	global $g, $pages, $admin, $menu, $login, $house, $vih;

	if(in_array($g->page, array_keys($pages)))
		$title = $pages[$g->page];

	if(in_array($g->page, array_keys($vih)))
		$title = $vih[$g->page]['name'];

	elseif(defined('USER_ID') && $g->page == 'admin' && in_array($g->v1, array_keys($admin)))
		$title = sprintf("Admin: %s", $admin[$g->v1]['name']);

	elseif($house && isset($menu[$g->v1]))
		$title = sprintf("%s: %s", $house->h_name, $menu[$g->v1]['name']);

	elseif($house)
		$title = sprintf("%s", $house->h_name);

	else $title = $g->m;

	$title .= ' &ndash; '.$g->set['site_name'];
	echo $title;
}

function sideNav() {
	global $g, $admin, $houses, $house, $menu, $vih;

	$markup = "<ul class='nav##add##'>##list##</ul>";
	$markup = in_array($g->page, array('start', 'auth', 'admin'))
		? str_replace('##add##', ' admin', $markup) : str_replace('##add##', '', $markup);

	$sel = function($bool) { return ($bool ? ' a' : ''); };

	switch($g->page) {
		case 'start' :
			$li[] = sprintf("<li><a href='%s' class='radiusLeft link hem a'>Start</a></li>", $g->href());
		break;
		case 'auth' :
		case 'skapa' :
			$li[] = sprintf("<li><a href='%s' class='radiusLeft link nyckel%s'>Logga in</a></li>", $g->href('auth'), $sel($g->page == 'auth'));
			#$li[] = sprintf("<li><a href='%s' class='radiusLeft link skapa%s'>Skapa konto</a></li>", $g->href('skapa'), $sel($g->page == 'skapa'));
		break;
		case 'admin' :
			foreach($admin as $ak => $av) {
				if(isset($av['rank']) && defined('USER_ID') && USER_RANK < $av['rank']) continue;
				#if(HOUSE_ID != 0 && isset($av['rank'])) continue;
				if(HOUSE_ID == 0 && !isset($av['rank'])) continue;
				$li[] = sprintf("<li><a href='%s' class='radiusLeft link %s%s'>%s</a></li>",
									$g->href('admin', $ak), $av['class'], $sel($g->v1 == $ak), $av['name']);
			}
		break;
		default :

			$item = $house ? $houses->menu($menu) : $vih;
			foreach($item as $ak => $av) {
				$v1 = $ak == 'start' ? '' : $ak;
				$a  = $ak == 'anvandaravtal' && $ak == $g->page ? $sel(true) : $sel($g->v1 == $ak);
				$href = $house ? $g->href($g->page, $v1) : $g->href($v1);
				$li[] = sprintf("<li><a href='%s' class='radiusLeft link %s%s'>%s</a></li>",
									$href, $av['class'], $a, $av['name']);
			}
	}

	if($g->page == 'admin' && $house)
		$markup .= sprintf("\r\n<a href='%s' class='radius return'>Återgå till hemsidan</a>", $g->href($house->h_perma));

	$markup = str_replace('##list##', join("\r\n", $li), $markup);

	echo $markup;
}

function logs($txt, $user = 0) {
	global $g;

	if($user) {
		$user = $g->_result($g->_query("select user_first from #_users where user_id = ?", $user));

		$txt = str_replace('@user', USER_NAME, $txt);
		$txt = str_replace('@house', $user, $txt);
	}

	$g->_exec("insert into #_logs (log_text, log_time) values (?, ?)", $txt, time());
}

function dropdown($data, $nr, $section) {

	$arr['hem'] = array();

	$arr['omforeningen'] = array('Fastigheten', 'Innegården', 'Lägenheterna', 'Fönster', 'Hiss', 'Trapphus', 'Säkerhet', 'Parkering', 'Tvättstuga', 'Vindsförråd', 'Källarförråd', 'Cykelrum', 'Barnvagnsrum', 'Grillplats', 'Lekplats', 'Trapphuset', 'Årsmöte', 'Vårstädning', 'Höststädning', 'Gemensamma aktiviter', 'Styrelsen', 'Andrahandsuthyrning', 'Delat boende');

	$arr['omgivningen'] = array('Butiker', 'Systembolag', 'Nöjen', 'Dagis', 'Skola', 'Grönområden', 'Motion', 'Parkering', 'Tunnelbana', 'Pendeltåg', 'Buss', 'Områdets historia', 'Områdets utveckling', 'Restauranger', 'Bio', 'Teater', 'Kultur');

	sort($arr[$section]);
	array_unshift($arr[$section], 'Egen rubrik', 'Ingen rubrik');

	$txt = empty($data) ? 'Ingen rubrik' : $data;
	$iTxt = $data == 'Välj rubrik' ? '' : $data;

	$html  = "<div class='droplist uSel'>\r\n";
	$html .= "<div class='dropdown'>{$txt}</div>\r\n";
	$html .= "<ul class='dropdown open'>\r\n";
	foreach($arr[$section] as $sec) {
		$sel = $sec == $data ? " class='a'" : '';
		$html .= "<li{$sel}>{$sec}</li>\r\n";
	}
	$html .= "</ul>\r\n";
	$html .= "<input type='hidden' name='p_title_{$nr}' value='{$iTxt}' />\r\n";
	$html .= "</div>\r\n";
	$html .= "<input type='text' value='{$iTxt}' placeholder='Skriv rubrik här...' class='extend hide' />\r\n";

	echo $html;
}

function strip($str, $it = "'\\") {
	return preg_replace("/[".preg_quote($it)."]/", '', $str);
}

function cookie($name, $value, $time = 3) {
	setcookie($name, $value, time() + $time, '/');
}

function rcookie($name) {
	setcookie($name, '', time() - 3600, '/');
}

function success($class = '', $out = '') {
	$class = !empty($class) ? " $class" : '';
	if(isset($_COOKIE['success'])) {
		echo "<div class='notice success radius{$class}'>";
		echo $_COOKIE['success'];
		echo "</div>\r\n";
	rcookie('success');
	}
}
function suspended($class = '') {
	global $house;
	$class = !empty($class) ? " $class" : '';
	if($house->h_suspend == 1) {
		echo "<div class='notice error radius{$class}'>";
		echo "Detta konto är indraget och behöver åtgärda sina felaktigheter.";
		echo "</div>\r\n";
	}
}

function errors($values) {
	echo "<div class='notice error radius reverse' style='color:red'>";
	if(is_array($values) && count($values) > 0) {
		foreach($values as $error)
			printf("%s<br />\r\n", $error);
	} else echo $values;
	echo "<br>";
	echo "</div>\r\n";
}

function shortstr($text, $length) {

	$last = utf8_encode(substr($text, $length - 1, 1));
	$next = utf8_encode(substr($text, $length, 1));

	if($last == '' || $next == ';') ++$length;
	if(substr($text, -1) == ';' && $last == '&') $length += 5;
	elseif($last == '&' || $last == ' ') --$length;
	elseif(strlen($text) <= $length + 2) $length += 2;
	elseif($last == '#') $length += 3;

	$text = substr($text, 0, $length).(strlen($text) >= $length + 1 ? '...' : '');
	return $text;
}

// {"opt":1, "full": 0, "time":0, "day":1, "dfull":0, "year":0, "sep":"/"}
function dtime($date, $para = '') {
	$json  = json_decode($para);
	$year = date('Y', $date);
	$year = isset($json->year) && $json->year == 0 ? substr($year, 2) : $year;
	$month = datemonths(date('n', $date));
	$day = isset($json->day) && $json->day == 1 ?
					days(date('N', $date), (isset($json->dfull) && $json->dfull == 0 ? 1 : 0)) : '';
	if(!isset($json->opt)) $json->opt = 1;
	#$json->sep = isset($json->sep) ? preg_replace('/[a-zA-Z0-9]+/', '', $json->sep) : '/';
	if(!isset($json->sep)) $json->sep = '/';

	switch($json->opt) {
		case 0: $out = sprintf('%s%s%s%2$s%s', $year, $json->sep, date('m', $date), date('d', $date)); break;
		case 1: $out = sprintf('%s%s%s%2$s%s', date('d', $date), $json->sep, date('m', $date), $year); break;
		case 2: $out = sprintf('%s%s%s%2$s%s', date('m', $date), $json->sep, date('d', $date), $year); break;
		case 3:
			$out = sprintf("%s %d %s %d",
						   		$day, date('j', $date),
								(isset($json->full) && $json->full == 0 ?
									   substr($month, 0, 3) :
									   $month),
								$year
							);
		break;
		case 4:
			$eng = true ? '' : ' of';
			if(!empty($day)) $day .= ' den';
			$out = sprintf('%s %s %s %s, %s',
						   		$day, number_ending(date('j', $date)),
								$eng, (isset($json->full) && $json->full == 0 ?
									   	substr($month, 0, 3) :
									  	$month),
								$year);
		break;
	}

	if(isset($json->time) && $json->time == 1) $out .= date(' H:i', $date);
	return trim($out);
}

function datemonths($m = 0) {

	$m = strlen($m) == 1 ? "0$m" : $m;
	$months = array(
		'01'  => 'januari',
		'02'  => 'februari',
		'03'  => 'mars',
		'04'  => 'april',
		'05'  => 'maj',
		'06'  => 'juni',
		'07'  => 'juli',
		'08'  => 'augusti',
		'09'  => 'september',
		'10' => 'oktober',
		'11' => 'november',
		'12' => 'december'
	);

  return ($m == 0 ? $months : $months[$m]);
}

function days($day, $out = 0) {

	$days = array(
		1  => 'måndag',
		2  => 'tisdag',
		3  => 'onsdag',
		4  => 'torsdag',
		5  => 'fredag',
		6  => 'lördag',
		7  => 'söndag'
	);
	if($out == 1) { //Fix for swedish
		$add = substr($days[$day], 1, 1) == '&' ? strpos($days[$day], ';') + 2 : 3;
		$out = substr($days[$day], 0, $add);
	} else $out = $days[$day];

  return $out;
}

function number_ending($amount) {

	$strlen = substr($amount, strlen($amount) - 1);

	if($strlen == 1 && $amount != 11) $amount = $amount.':a'; #st
	elseif($strlen == 2 && $amount != 12) $amount = $amount.':a'; #nd
	elseif($strlen == 3 && $amount != 13) $amount = $amount.':e'; #rd
	else $amount = $amount.':e'; #th

  return $amount.''; # of
}

function ago($time, $ts = 1) {

	$now = time();
	$dif = $now - $time;
	if($dif < 60) { $out = $dif; $w = 'second'; }
	elseif($dif < 60 * 60) { $out = $dif / 60; $w = 'minute'; }
	elseif($dif < 60 * 60 * 24) { $out = $dif / 60 / 60; $w = 'hour'; }
	elseif($dif < 60 * 60 * 24 * 30) { $out = $dif / 60 / 60 / 24; $w = 'day'; }
	elseif($dif < 60 * 60 * 24 * 365) { $out = $dif / 60 / 60 / 24 / 30; $w = 'month'; }
	else { $out = $dif / 60 / 60 / 24 / 365; $w = 'year'; }
	$out = floor($out);
	if($out != 1) $w .= 's';
	return $w == 'day' ? "ig&aring;r".($ts == 1 ? " vid ".date('H:i', $time): '') : ($w == 'seconds' && $out == 0 ? "precis" : "$out $w sedan"); // yesterday  ||  at  ||  just  ||  ago
}

function units($cash, $dec = 0) { return number_format($cash, $dec, '.', ','); }

function url($url, $dis = '') {

	$link = ($dis == '') ? $url : $dis;
	if(!preg_match("/^(irc|ftp|https?):\/\//i", $url))
		$url = "http://$url";

	$out = "<a href='$url' target='_blank'>$link</a>";
	return $out;
}

function youtube($address, $m = '510|426', $t = '') {

	if($t == 1)
		$tube = $address;

	else {
		$m = preg_replace('/^=/', '', $m);
		$me = explode('|', $m);
		if(substr($me[0], 0, 1) == '=') $me[0] = substr($me[0], 1);

		$where = strpos($address, '?v=') + 3;
	  $address = substr($address, $where, 11);

	  $address = 'http://www.youtube.com/v/'.$address;

	$tube  = "<object width='{$me[0]}' height='{$me[1]}' style='margin: 10px;'>
			  \t\t\t\t\t<param name='movie' value='{$address}?fs=1'></param>
			  \t\t\t\t\t<param name='wmode' value='transparent'></param>
			  \t\t\t\t\t<param name='allowFullScreen' value='true'></param>
			  \t\t\t\t\t<param name='allowScriptAccess' value='true'></param>
			  \t\t\t\t\t<embed src='{$address}?fs=1' type='application/x-shockwave-flash' wmode='transparent' allowfullscreen='true' allowScriptAccess='always' width='{$me[0]}' height='{$me[1]}'></embed>
			  \t\t\t\t</object>";
	}

return $tube;
}

function remove_dir($dirname) {

    if (!file_exists($dirname)) return false;
    if (is_file($dirname)) return unlink($dirname);
	    $dir = dir($dirname);
    while (false !== $entry = $dir->read()) {
        if ($entry == '.' || $entry == '..') continue;
        remove_dir("$dirname/$entry");
    }

    $dir->close();
    return rmdir($dirname);
}

function image($img, $sub = '', $id = '', $thb = '', $out = 0, $ajax = '') {

	$image = sprintf("%suploads/%s%s%s", $ajax,
					 	($sub != '' ? "$sub/" : ''),
						($id  != '' ? "$id/"  : ''),
						($thb != '' ? sprintf("thumbs/%s_%s.", $thb, $img) : "$img."));

	$image = preg_replace('@\.(jpg|png|gif)\.$@', '.', $image);

		if(file_exists($image.'jpg')) $image .= 'jpg';
		elseif(file_exists($image.'png')) $image .= 'png';
		elseif(file_exists($image.'gif')) $image .= 'gif';
		else return false;

	$image = preg_replace('#(\.\.\/)*#', '', $image);
	$image = $out == 0 ? HTTP.$image : "<img src='".HTTP.$image."' alt='' />";
	return $image;
}

function maintainAjax($link) {
	return str_replace(HTTP, '../../', $link);
}

function plural($count, $sing, $plur, $para = array()) {
	global $g;
	return ($count == 1 ?
				strip($g->params($sing, $para)) :
				str_replace('@count', $count, strip($g->params($plur, $para))));
}

function antispambot($emailaddy, $mailto=0) {
	$emailNOSPAMaddy = '';
	srand ((float) microtime() * 1000000);
	for ($i = 0; $i < strlen($emailaddy); $i = $i + 1) {
		$j = floor(rand(0, 1+$mailto));
		if ($j==0) {
			$emailNOSPAMaddy .= '&#'.ord(substr($emailaddy,$i,1)).';';
		} elseif ($j==1) {
			$emailNOSPAMaddy .= substr($emailaddy,$i,1);
		} elseif ($j==2) {
			$emailNOSPAMaddy .= '%'.zeroise(dechex(ord(substr($emailaddy, $i, 1))), 2);
		}
	}
	$emailNOSPAMaddy = str_replace('@','&#64;',$emailNOSPAMaddy);
	return $emailNOSPAMaddy;
}

function zeroise($number, $threshold) {
	return sprintf('%0'.$threshold.'s', $number);
}

function email($mail, $wrap = true) {
	return $wrap
		? sprintf('<a href=\'mailto:%s\' class=\'email\'>%1$s</a>', antispambot($mail))
		: antispambot($mail);
}

function normalize($str, $lower = true, $proper = false) {

	$specials = array(
		'Š'=>'S', 'š'=>'s', 'Ð'=>'Dj','Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A',
		'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E', 'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I',
		'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U',
		'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss','à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a',
		'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i',
		'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u',
		'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y', 'ƒ'=>'f'
	);

	$str = strtr($str, $specials);
	$str = str_replace(array('&', ' ', '--'), array('-och-', '-', '-'), $str);
    $str = trim(preg_replace('/[^\w\d_@. -]/si', '', $str));
	#$str = preg_replace('/-{2,}/', '-', $str); // Replace multiple dashes with a single
	if(!$proper)
		$str = str_replace('-', '', $str);

    return $lower ? strtolower($str) : $str;
}

function bbcode(&$con, $special = false) {

	$con = sprintf("<p>%s</p>", $con);
	$con = preg_replace("@(\r\n){2}@s", "</p><p>", $con);

	if($special)
		$con = preg_replace("#\[h\](.*?)\[\/h\]#si", "<span class='headline'>$1</span>", $con);

	$con = preg_replace("#\[b\](.*?)\[\/b\]#si", "<b>$1</b>", $con);
	$con = preg_replace("#\[u\](.*?)\[\/u\]#si", "<u>$1</u>", $con);
	$con = preg_replace("#\[i\](.*?)\[\/i\]#si", "<i>$1</i>", $con);
	$con = preg_replace("#\[li\](.*?)\[\/li\]#si", "<span class='li'>$1</span>", $con);

	$con = preg_replace("#([\w\d./-]+@[\w\d./-]+\.[\w]{2,4})#se", 'email("$1")', $con);
	$con = preg_replace("#\[url=(\"|\'|\&\#34;|\&\#39;)?(.*?)(\"|\'|\&\#34;|\&\#39;)?\](.*?)\[/url\]#sei", 'url("\\2", "\\4")', $con);
	$con = preg_replace("#\[url\](.*)\[\/url\]#sei", 'url("$1")', $con);

	$con = nl2br($con);
}


function fixLink($vars, $link){
	foreach($vars as $key => $value) {
		$link = str_replace($key,$value,$link);
	}
	return $link;
}

?>
