<?php
if($g->page == 'admin') {
	
	if(!defined('USER_ID'))
		$g->send($g->href('auth'));
		
	$headline = $house ? $house->h_name : $uInfo['user_first'];
	?>
	
		<div class='content'>
			<div class='header'>
				<p class='start'>Du är inloggad som</p>
				<h2><?php echo $headline; ?></h2>
				<a href='<?php echo $g->href('logout'); ?>' class='login radius'>Logga ut</a>
			</div>
			<div class='main'>
				<?php
				$link = sprintf("portal/%s/admin.php", $g->v1);
				if(file_exists($link) &&
					( (!isset($admin[$g->v1]['rank']) && USER_RANK < 3) ||
					(isset($admin[$g->v1]['rank']) && USER_RANK >= $admin[$g->v1]['rank'])) )
					include($link);
				else {
					$g->send($g->href('404', '?referens='.$g->currentPage()));
				}
				?>
			</div>
		</div>
		
	<?php
}
?>