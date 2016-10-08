<?php
if($g->v1 == 'fotoalbum') {
	
	$side = $faq->get($g->v1);
	?>
		<h1 class='<?php echo $admin[$g->v1]['class']; ?>'><?php echo $admin[$g->v1]['name']; ?></h1>
		<div class='con push'>
			<?php
			success('reverse');
			suspended('reverse');
			?>
			<div class='upload show'>
				<h3>Ladda upp bild</h3>
				<input type='text' name='<?php echo $g->generate(5, 2, 0); ?>' value='' placeholder='Rubrik (frivilligt)' />
				<input type='file' id='<?php echo $g->generate(5, 2, 0); ?>' value='' data-page='<?php echo $g->v1; ?>' name='<?php echo $g->generate(5, 2, 0); ?>' tabindex='-1' />
			</div>
			
			<h5>Uppladdade bilder</h5>
			<div class=''>
				<div class='table'>
					<p class='thb'></p>
					<p class='name'><b>Rubrik</b></p>
					<p class='size'><b>Storlek</b></p>
					<p class='modify'><b>Hantera</b></p>
				</div>
				
				<?php echo $houses->album(); ?>
				<div class='navigate'>
					<a class='page-prev submit radius navigate' href='#'>Gå tillbaka</a>
				</div>
			</div>
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
	<?php
}
?>