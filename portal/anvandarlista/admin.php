<?php
if($g->v1 == 'anvandarlista') {
	$qry = $g->_query("select * from #_users order by user_rank desc");
	?>
		<h1 class='<?php echo $admin[$g->v1]['class']; ?>'><?php echo $admin[$g->v1]['name']; ?></h1>
		<div class='con push'>
		<?php		
		if(is_numeric($g->v2)) {
			$grab = $g->_query("select user_id, user_pass, user_rank from #_users where user_id = ? and user_rank < 3", $g->v2);
			if($g->_count($grab)) {
				$res = $g->_object($grab);
				
				cookie(PRE.'override_id', $res->user_id);
				cookie(PRE.'override_pid', $res->user_pass);
				cookie(PRE.'override_rank', $res->user_rank);
				
				cookie('success', 'Du har nu lyckats byta användare.');
				logs("@user has logged in as @house.", $res->user_id);
				
				$g->send($g->href('admin'));
			} else errors("Du kan inte växla till en den här användaren!");
		}
		?>
			<div>
				<div class='table t-admin'>
					<p class='thb'></p>
					<p class='name'><b>Rubrik</b></p>
					<p class='size'><b>Skapad</b></p>
					<p class='modify'><b>Hantera</b></p>
				</div>
				<?php
				foreach($g->_object($qry, 1) as $obj) {
					$user = $obj->user_rank < 3
						? sprintf('<a href="%s" title="%2$s" target="_blank">%s</a>', $g->href($obj->user_name), $obj->user_first)
						: sprintf("%s (admin)", $obj->user_name);
					?>
				<div class='table t-admin'>
					<p class='thb id'>#<?php echo $obj->user_id; ?></p>
					<p class='name'><?php echo $user; ?></p>
					<p class='size'><?php echo dtime($obj->user_created_at, '{"opt":3, "full":0}'); ?></p>
					<p class='modify'>
						<a href='#'>Växla</a> /
						<a href='#'>Ta bort</a>
					</p>
					<p class='sure'>Inloggningen gäller tills du stänger webbläsarfönstret eller loggar ut.<br />Är du säker på att du vill växla till denna användare? <a href='#change'>Ja</a> / <a href='#close'>Nej</a></p>
					<p class='sure'>Är du säker på att du vill ta bort all information och bifogade filer? <a href='#remove'>Ja</a> / <a href='#close'>Nej</a></p>
				</div>
					<?php
				}
				?>
			</div>
		</div>
<?php
}
?>