<?php
if($g->page == 'auth') {
	
	if(defined('USER_ID'))
		$g->send('admin');
		
	$show = " style='display: block;'";
	$hide = " style='display: none;'";
	
	$log_user = isset($_POST['log_user']) ? $_POST['log_user'] : '';
	$side = $faq->get($g->page);
	?>
	
		<div class='content'>
			<div class='header push'>
				<h2>Logga in</h2>
			</div>
			<div class='main'>
				<?php success(); ?>
				<div class='con' id='login'<?php echo $g->v1 != 'losenord' && $g->v1 != 'aterhamta' ? $show : $hide; ?>>
				<form action='<?php echo $g->href($g->page, 'skicka'); ?>' method='post' class='form' autocomplete='off'>
					<h3>Föreningens namn</h3>
					<input type='text' name='log_user' value='<?php echo $log_user; ?>' placeholder='Brf Vi i Huset' tabindex='1' />
					
					<h3>Lösenord</h3>
					<input type='password' name='log_pass' value='' placeholder='Lösenord' tabindex='2' />
					
					<div class='remember'>
						<input id='r' type='checkbox' name='remember' value='1' tabindex='3' />
						<label for='r'>Kom ihåg mig</label>
						<a href='#password' class='forgot'>Glömt lösenordet?</a>
					</div>
				<?php if($g->v1 == 'skicka') $auth->login(); ?>
				</form>
				<a href='#' class='submit radius' tabindex='4'>Logga in</a>
				</div>
				<div class='con' id='password'<?php echo $g->v1 == 'losenord' ? $show : $hide; ?>>
				<form action='<?php echo $g->href($g->page, 'losenord', 'skicka'); ?>' method='post' class='form' autocomplete='off'>
					<h3>Registrerad e-postadress</h3>
					<input type='text' name='lost_email' value='<?php echo @$_POST['lost_email']; ?>' placeholder='E-postadress' tabindex='1' />
					
					<div class='remember'>
						<a href='#login' class='forgot log'>Jag vill logga in istället!</a>
					</div>
				<?php if($g->v1 == 'losenord' && $g->v2 == 'skicka') $auth->pass(); ?>
				</form>
				<a href='#' class='submit radius' tabindex='2'>Erhåll</a>
				</div>
				<div class='con' id='recover'<?php echo $g->v1 == 'aterhamta' ? $show : $hide; ?>>
				<form action='<?php echo $g->href($g->page, 'aterhamta', $g->v2, 'skicka'); ?>' method='post' class='form' autocomplete='off'>
					<h3>Erhåll nytt lösenord</h3>
					<input type='hidden' name='hash' value='<?php echo $g->v2; ?>' />
					<input type='password' name='new' value='' placeholder='Lösenord' tabindex='1' />
					<input type='password' name='re' value='' placeholder='Upprepa lösenord' tabindex='2' />
					
					<div class='remember'>
						<a href='#login' class='forgot log'>Jag vill logga in istället!</a>
					</div>
					<?php if($g->v1 == 'aterhamta' && $g->v3 == 'skicka') $auth->recover(); ?>
				</form>
				<a href='#' class='submit radius' tabindex='3'>Uppdatera</a>
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