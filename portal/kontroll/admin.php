<?php
if($g->v1 == 'kontroll') {
	?>
		<h1 class='<?php echo $admin[$g->v1]['class']; ?>'><?php echo $admin[$g->v1]['name']; ?></h1>
		<div class='con push'>
			<?php success('reverse'); ?>
			<?php 
			if($g->v2 == 'skapa')
				$houses->create();
			?>
			<form action='<?php echo $g->href('admin', $g->v1, 'skapa'); ?>' method='post' class='form' autocomplete='off'>
				<h3>Adress</h3>
				<div class='divide'>
					<input type='text' name='address' id='name' placeholder='Föreningens adress' value='<?php echo @$_POST['address']; ?>' />
					<input type='text' name='user_email' placeholder='E-postadress' value='<?php echo @$_POST['user_email']; ?>' />
				</div>					
				<input type='text' name='user_name' id='perma' class='extend' placeholder='Förhandsvisning på adress' readonly='readonly' value='<?php echo @$_POST['user_name']; ?>' />
				
				<h3>Kontotyp</h3>
				<div class='remember'>
					<input id='r1' type='radio' name='type' value='user' checked='checked' />
					<label for='r1'>Förening</label>
					<input id='r2' type='radio' name='type' value='admin' />
					<label for='r2'>Admin</label>
				</div>
			</form>
			<a href='#' class='submit save radius'>Skicka inbjudan</a>
		</div>
	<?php
}
?>