<?php
if($g->v1 == 'bilder') {
	?>
		<h1 class='<?php echo $admin[$g->v1]['class']; ?>'><?php echo $admin[$g->v1]['name']; ?></h1>
		<div class='con push'>
			<?php success('reverse'); ?>
			<?php 
			if($g->v2 == 'skicka')
				$houses->image();
			?>
			<form action='<?php echo $g->href('admin', $g->v1, 'skicka'); ?>' method='post' class='form' autocomplete='off' enctype='multipart/form-data'>
				<h3>Sök förening</h3>
				<div class='search'>
					<img src='<?php echo $g->src('search.png', 'gfx'); ?>' alt='Search' />
					<input type='text' name='search' autocomplete='off' value='<?php echo @$_POST['search']; ?>' class='normal radius' placeholder='Sök' />
					<input type='hidden' name='house' value='<?php echo @$_POST['house']; ?>' />
					<div class='results'></div>
				</div>
				<h3>Välj bild att ladda upp</h3>
				<p>Måtten kommer att bli 766 x 307 pixlar.</p>
				<input type='file' name='bild' />
			</form>
			<a href='#' class='submit save radius'>Ladda upp</a>
		</div>
	<?php
}
?>