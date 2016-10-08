<?php
session_start();
if(!isset($_SERVER['HTTP_REFERER']) || preg_match('#/([\w\d-]+)\.php#i', $_SERVER['HTTP_REFERER'])) exit;

require_once('../../_inc/globals.class.php'); $g = new Globals();
include('../../'.$g->src('functions.php', '', true));

require_once('../../'.$g->src('auth.class.php', 'classes', true));
$auth = new Auth;

if(isset($_POST['key']) && isset($_POST['type'])) {
	
	$key = $g->html_reverse(($_POST['key'])); // urldecode
	$type = $_POST['type'];
	
	$json = json_decode($key);
	switch($type) {
		case 'reg':
			if(!empty($json->email) && !!preg_match("#[\w\d./-]+@[\w\d./-]+\.[\w]{2,4}#", $json->email)) {
				$r_type = isset($json->type) ? $json->type : 'member';
				$g->_query("INSERT INTO #_board_regs (house_id, r_email, r_type, r_created_at) VALUES (?, ?, ?, ?)", array(HOUSE_ID, $json->email, $r_type, time()));
				echo "success";
			} else {
				echo "error";
			}
		break;
		case 'post':
			parse_str($json->form, $form);
			if(empty($form['title']))
				echo "title";
			elseif(empty($form['message']))
				echo "message";
			else {
				$where = array();
				
				if(isset($form['board'])) $where[] = $form['board'];
				if(isset($form['email_all'])) {
					$where[] = $form['email_all'];
					$qry = $g->_query("SELECT DISTINCT(r_email) FROM #_board_regs WHERE house_id = ?", HOUSE_ID);
				}
				else if(isset($form['email_board'])) {
					$where[] = $form['email_board'];
					$qry = $g->_query("SELECT DISTINCT(r_email) FROM #_board_regs WHERE house_id = ? AND r_type = 'board'", HOUSE_ID);
				}
				
				if(isset($qry) && $g->_count($qry) > 0) {
					$house = $g->_array($g->_query("SELECT h_name, h_perma FROM #_houses WHERE h_id = ?", HOUSE_ID));
					require_once('../../'.$g->src('mail.class.php', 'classes', true));
					$mail = new Email("sendout.html");
					$mail->bind(array(
						'houseName' => $house['h_name'],
						'subject' => $form['title'],
						'message' => nl2br(preg_replace('@\[(?<tag>\w+)](.*?)\[\/(?P=tag)]@', '$2', $form['message'])),
						'boardLink' => $g->server.$g->href($house['h_perma'], 'anslagstavlan')
					));
					
					$s = 0;
					while($email = $g->_result($qry)) {
						$mail->recipients($email);
						if($mail->send('html', $house['h_name']) === true) {
						}
					++$s;
					}
					if($g->_count($qry) == $s)
						echo "sent!";
				}
					
				$join = join(' | ', $where);
				$g->_query("INSERT INTO #_board (house_id, b_title, b_message, b_where, b_created_at) VALUES (?, ?, ?, ?, ?)", array(HOUSE_ID, $form['title'], $form['message'], $join, time()));
				bbcode($form['message']);
				?>
			<div class='message' data-id='<?=$g->_last();?>'>
				<h5><?=$form['title'];?><span><?=dtime(time(), '{"opt":0,"sep":"-"}');?></span></h5>
				<?php if(!empty($join)) { ?>
				<p class="b_where"><?=$join;?></p>
				<?php } ?>
				<div class='mess'>			
					<?=$form['message'];?>
				</div>
				<?php if(isset($form['page']) && $form['page'] == 'admin') { ?>
				<a href='#' class='submit remove radius message'>Ta bort inlägget</a>
				<?php } ?>
			</div>
			<?php
			}
		break;
		case 'e-remove':
			$g->_query("DELETE FROM #_board_regs WHERE house_id = ? AND r_email = ? AND r_type = ?", HOUSE_ID, $json->email, $json->type);
		break;
		case 'remove':
			$g->_query("UPDATE #_board SET b_pub = 0 WHERE house_id = ? AND b_id = ?", HOUSE_ID, $json->post);
		break;
	}
}
?>