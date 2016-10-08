<?php
if($g->page == 'skapa') {
	
	if(defined('USER_ID'))
		$g->send($g->href('admin'));
	
	$log_user = isset($_POST['reg_user']) ? $_POST['reg_user'] : '';
	$log_mail = isset($_POST['reg_email']) ? $_POST['reg_email'] : '';
	$side = $faq->get($g->page);
	?>
	
		<div class='content'>
			<div class='header push'>
				<h2>Skapa konto</h2>
			</div>
			<div class='main'>
				<div class='con' id='login'>
				<form action='<?php echo $g->href($g->page, 'skicka'); ?>' method='post' class='form' autocomplete='off'>
					<h3>Föreningens namn</h3>
					<input type='text' name='reg_user' value='<?php echo $log_user; ?>' placeholder='Brf Vi i Huset' tabindex='1' />
					
					<h3>E-postadress</h3>
					<input type='text' name='reg_email' value='<?php echo $log_mail; ?>' placeholder='info@viihuset.se' tabindex='2' />
					
					<h3>Lösenord</h3>
					<input type='password' name='reg_pass' value='' placeholder='Lösenord' tabindex='3' />
					<input type='password' name='reg_re' value='' placeholder='Bekräfta lösenord' tabindex='4' />
					
					<div class='remember'>
						<input id='r' type='checkbox' name='remember' value='1' tabindex='5' />
						<label for='r'>Kom ihåg mig</label>
					</div>
				<?php if($g->v1 == 'skicka') $houses->create(); ?>
				</form>
				<a href='#' class='submit radius' tabindex='6'>Skapa och logga in</a>
				</div>
				<div class='side'>
					<h4>Vanliga frågor</h4>
					<ul class='list hide'>
					<?php foreach($side as $si) { bbcode($si['a']); ?>
						<li>
							<b><a href='#toggle'><?php echo $si['q']; ?></a></b>
							<?php echo $si['a']; ?>
						</li>
					<?php } ?>
					</ul>
				</div>
			</div>
		</div>
		
	<?php
}
?>