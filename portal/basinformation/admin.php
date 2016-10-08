<?php
if($g->v1 == 'basinformation') {
	
	if($g->v2 == 'skicka')
		$houses->base();
		
	$side = $faq->get($g->v1);
	?>
		<h1 class='<?php echo $admin[$g->v1]['class']; ?>'><?php echo $admin[$g->v1]['name']; ?></h1>
		<div class='con push'>
			<?php
			success('reverse');
			suspended('reverse');
			?>
			<form action='<?php echo $g->href('admin', $g->v1, 'skicka'); ?>' method='post' class='form'>
				<h3>Föreningens namn</h3>
				<input type='text' name='h_name' value='<?php echo $house->h_name; ?>' placeholder='Skriv Brf namn här...' class='extend' />
				
				<h3>Föreningens besöksadress</h3>
				<div class='divide'>
					<input type='text' name='h_address' value='<?php echo $house->h_address; ?>' placeholder='Gatuadress' />
					<input type='text' name='h_postal' value='<?php echo $house->h_postal; ?>' placeholder='Postnummer' />
					<input type='text' name='h_town' value='<?php echo $house->h_town; ?>' placeholder='Stad' />
					<input type='text' name='h_country' value='<?php echo $house->h_country; ?>' placeholder='Land' />
				</div>
				<?php $i = 1;
				if(!empty($house->h_people)) {
				foreach(json_decode($house->h_people) as $v) {
					if(empty($v->email)) continue;
				?>
				<div class='people' data-rel='<?php echo $i; ?>'>
					<h3>Kontaktperson</h3>
					<div class='divide'>
						<input type='text' name='person_<?php echo $i; ?>' value='<?php echo $v->person; ?>' placeholder='Namn' />
						<input type='text' name='role_<?php echo $i; ?>' value='<?php echo $v->role; ?>' placeholder='Roll i styrelsen' />
						<input type='text' name='email_<?php echo $i; ?>' value='<?php echo $v->email; ?>' placeholder='E-postadress' />
						<input type='text' name='number_<?php echo $i; ?>' value='<?php echo $v->number; ?>' placeholder='Telefonnummer' />
					</div>
					<a href='#' class='submit remove radius person'>Ta bort kontaktperson</a>
				</div>
				<?php $i++; } } if($i == 1) { ?>
				<div class='people' data-rel='<?php echo $i; ?>'>
					<h3>Kontaktperson</h3>
					<div class='divide'>
						<input type='text' name='person_<?php echo $i; ?>' value='' placeholder='Namn' />
						<input type='text' name='role_<?php echo $i; ?>' value='' placeholder='Roll i styrelsen' />
						<input type='text' name='email_<?php echo $i; ?>' value='' placeholder='E-postadress' />
						<input type='text' name='number_<?php echo $i; ?>' value='' placeholder='Telefonnummer' />
					</div>
					<a href='#' class='submit remove radius person'>Ta bort kontaktperson</a>
				</div>
				<?php } ?>
				<a href='#' class='submit addnew radius person'>Lägg till kontaktperson</a>
				
				<h3 class='push-up'>Föreningens bild</h3>
				<img src='<?php echo $house->h_image; ?>' alt='' class='huvudbild' />
				<p>För att göra er hemsida så snygg som möjligt vill vi retuschera och besäkra er bild.</p>
				
				<a target='_blank' href='mailto:<?php echo email('info@viihuset.se', false); ?>?subject=<?php echo rawurlencode("Föreningens bild till ".$house->h_name); ?>' class='submit mail radius image'>Skicka bild till oss</a>
			</form>
			<a href='#' class='submit save radius'>Spara &amp; Publicera</a>
			<div class='navigate'>
				<a class='page-next submit radius navigate' href='#'>Nästa sida</a>
			</div>
		</div>
		<div class='side'>
			<h4>Basinformation</h4>
			<ul class='list hide'>
			<?php foreach($side as $si) { bbcode($si['a']); ?>
				<li>
					<b><a href='#toggle'><?php echo $si['q']; ?></a></b>
					<?php echo $si['a']; ?>
				</li>
			<?php } ?>
			</ul>
		</div>
	<?php
}
?>