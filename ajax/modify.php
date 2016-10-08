<?php
session_start();
if(!isset($_SERVER['HTTP_REFERER']) || preg_match('#/([\w\d-]+)\.php#i', $_SERVER['HTTP_REFERER'])) exit;

require_once('../../_inc/globals.class.php'); $g = new Globals();
include('../../'.$g->src('functions.php', '', true));

require_once('../../'.$g->src('auth.class.php', 'classes', true));
$auth = new Auth;

if(isset($_POST['key']) && isset($_POST['type'])) {
	
	$key = $g->html_reverse(urldecode($_POST['key']));
	$type = $_POST['type'];
	
	switch($type) {
		case 'delete' :
			$qry = $g->_query("select a_type from #_attachments where house_id = ? and a_name = ?", HOUSE_ID, $key);
			if($g->_count($qry) == 1) {
				$tt  = $g->_result($qry);
				
				$dir = sprintf("../../uploads/%s/%s/%s", HOUSE_ID, $tt, $key);
				echo $g->_exec("delete from #_attachments where house_id = ? and a_name = ?", HOUSE_ID, $key);
				
				if($tt == 'images') {
					$imgs[] = image($key, HOUSE_ID, $tt, '', 0, '../../');
					$imgs[] = image($key, HOUSE_ID, $tt, 'list', 0, '../../');
					$imgs[] = image($key, HOUSE_ID, $tt, 'small', 0, '../../');
					$imgs[] = image($key, HOUSE_ID, $tt, 'icon', 0, '../../');
					$imgs = array_map('maintainAjax', $imgs);
					foreach($imgs as $i) remove_dir($i);
				} else remove_dir($dir);
			}
		break;
		case 'edit' :
		 	$json = json_decode($key);
			$g->_exec("update #_attachments set a_title = ? where house_id = ? and a_name = ?", $json->key, HOUSE_ID, $json->file);
			
			echo !empty($json->key) ? $json->key : '-';
		break;
		case 'remove' :
			
			if(!defined('USER_ID') || USER_RANK < 3) return;
			
			$u = $g->_object($g->_query("select house_id as house, user_rank as rank from #_users where user_id = ?", $key));
			
			if($u->rank == 5) {
				echo "Den här användaren är Superadmin och kan inte tas bort på grund av säkerhetsskäl.";
				return;
			}
				
			logs("@user has removed @house.", $key);
			$g->_exec("delete from #_users where user_id = ?", $key);
			if($u->house != 0) {
				$g->_exec("delete from #_attachments where house_id = ?", $u->house);
				$g->_exec("delete from #_houses where h_id = ?", $u->house);
				$g->_exec("delete from #_info where house_id = ?", $u->house);
				$g->_exec("delete from #_posts where house_id = ?", $u->house);
				
				$dir = sprintf('../../uploads/%s', $u->house);
				remove_dir($dir);
			}
		break;
	}
}
?>