<?php
session_start();
if(!isset($_SERVER['HTTP_REFERER']) || preg_match('#/([\w\d-]+)\.php#i', $_SERVER['HTTP_REFERER'])) exit;

require_once('../../_inc/globals.class.php'); $g = new Globals();
include('../../'.$g->src('functions.php', '', true));

require_once('../../'.$g->src('houses.class.php', 'classes', true));
$houses = new Houses();

require_once('../../'.$g->src('auth.class.php', 'classes', true));
$auth = new Auth;
	
if(isset($_POST['image']) && defined('HOUSE_ID')) {	
	
	$img  = isset($_POST['image']) ? $_POST['image'] : '';
	$text = isset($_POST['name']) ? $_POST['name'] : '';
	$page = isset($_POST['page']) ? $_POST['page'] : '';
	
	// Non album
	$spl = explode('_', $page);
	if(sizeof($spl) > 1) {
		$page = $spl[1];
		$post = $spl[0];
	} else $post = 0;
	
	list($error, $msg, $img, $name) = array('', '', '', $img);
	$imgs = array(
				  'jpg'  => 'image/jpeg',
				  'jpeg' => 'image/pjpeg',
				  'png'  => 'image/png',
				  'gif'  => 'image/gif'
				  );
	$docs = array(
				  'pdf'  => 'application/pdf',
				  'xls'  => 'application/vnd.ms-excel',
				  'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
				  'doc'  => 'application/msword',
				  'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
				  'ppt'  => 'application/vnd.ms-powerpoint',
				  'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation'
				  );

	// Possible types
	$types = $post == 0 ? $imgs : $docs;
	
	if(!in_array($_FILES[$name]['type'], $types))
		$error = sprintf('Filen ingår inte ibland de tillåtna filtyperna %s.', implode(', ', array_keys($types)));
		
	elseif(!empty($_FILES[$name]['error'])) {
		
		switch($_FILES[$name]['error']) {

			case '1':
				$error = 'Filen överskrider max uppladdningsstorleken.';
				break;
			case '2':
				$error = 'Filen överskrider största tillåtna storlek.';
				break;
			case '3':
				$error = 'Filen var endast delvis uppladdad, försök igen.';
				break;
			case '4':
				$error = 'Ingen fil laddades upp.';
				break;
			case '6':
				$error = 'Den temporära mappen existerar inte.';
				break;
			case '7':
				$error = 'Misslyckades att skriva till hårddisken.';
				break;
			case '8':
				$error = 'Filen stoppades av en utökning.';
				break;
			case '999':
			default:
				$error = 'Ingen felkod tillgänglig.';
		}
	} elseif(empty($_FILES[$name]['tmp_name']) || $_FILES[$name]['tmp_name'] == 'none') {
		$error = 'Ingen fil laddades upp...';
	} else {
		$dir = in_array($_FILES[$name]['type'], $imgs) ? 'images' : 'documents';
		
		$qry   = $g->_query("select h_perma from #_houses where h_id = ?", HOUSE_ID);
		$perma = $g->_result($qry);
		
		$override = sprintf("%s_%s_%s", $perma, $g->generate(4, 2, 0), date('Ymd-Hi'));
		
		require_once('../../'.$g->src('imagery.class.php', 'classes', true));
		$cimg = new Imagery(HOUSE_ID, $dir, '../../');
		
		if($dir == 'images') {
			
			if($info = $cimg->upload(900, 700, $name)) {
				$cimg->upload(225, 169, 'list');
				$cimg->upload(68, 68, 'small'); // 68 x 68
				$cimg->upload(25, 25, 'icon', 1);
				
				$size = filesize(sprintf('../../uploads/%s/images/%s', HOUSE_ID, $info['name']));
			} else $msg .= "Filen laddades inte upp.";
		} else {
			preg_match('@\.([\w]{3,4})$@', $_FILES[$name]['name'], $ext);
			$info['name'] = sprintf("%s.%s", $override, $ext[1]);
			$msg = substr($ext[1], 0, 3);
			
			if(!move_uploaded_file($_FILES[$name]['tmp_name'], $cimg->dir.$info['name']))
				$msg .= "Filen laddades inte upp.";
			else {
				$size = filesize($cimg->dir.$info['name']);
				
				$img  = sprintf("<div><span class='delete'>Ta bort</span><span class='edit'>Byt namn</span><a href='%s%s' target='_blank' class='%s'>%s</a></div>", HTTP,	str_replace('../../', '', $cimg->dir.$info['name']), $msg, $text);
			}
		}
		if(isset($size)) {
			preg_match('@\.([\w]{3,4})$@', $_FILES[$name]['name'], $extension);
			
			$g->_exec("insert into #_attachments (house_id, user_id, a_page, a_post, a_type, a_name, a_ext, a_size, a_title, a_uploaded)
								values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
								HOUSE_ID, USER_ID, $page, $post, $dir, $info['name'], substr($extension[1], 0, 3), $size, $text, time());
			if($dir == 'images')
				$msg = $houses->album(HOUSE_ID, 'single', '../../');
		}
#			$msg .= " File Name: " . $_FILES[$name]['name'] . ", ";
#			$msg .= " File Size: " . @filesize($_FILES[$name]['tmp_name']);
		//for security reason, we force to remove all uploaded file
#			@unlink($_FILES[$name]);
	}
	echo "{";
	echo				"error: '" . $error . "',\n";
	echo				"msg: '" . $msg . "',\n";
	echo				"img: '" . $img . "'\n";
	echo "}";
}
?>