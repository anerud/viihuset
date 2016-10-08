<?php
session_start();
if(!isset($_SERVER['HTTP_REFERER']) || preg_match('#/([\w\d-]+)\.php#i', $_SERVER['HTTP_REFERER'])) exit;

require_once('../../_inc/globals.class.php'); $g = new Globals();
include('../../'.$g->src('functions.php', '', true));

require_once('../../'.$g->src('auth.class.php', 'classes', true));
$auth = new Auth;

require_once('../../'.$g->src('houses.class.php', 'classes', true));
$houses = new Houses();
	
if(isset($_POST['key']) && isset($_POST['type'])) {
	
	$key = $g->html_reverse(urldecode($_POST['key']));
	$type = $_POST['type'];
	
	unset($_POST['key']);
	unset($_POST['type']);
	
	$json = json_decode($key);
	foreach($json as $o) {
		$_POST[$o->name] = $o->value;
	}
	//$_POST['house_id'] = HOUSE_ID;
	$houses->saveInfo('preview');
	
	$qry = $g->_query("select h_perma from #_houses where h_id = ?", HOUSE_ID);
	echo $g->server.$g->href($g->_result($qry), 'maklarinfo', 'preview');
}
?>