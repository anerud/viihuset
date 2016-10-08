<?php
if($g->page == '404' || $_404) {
	?>
	
<?php if(!$_404) {
	$get = $g->get();
	
	$referer = isset($get['referens']) ? $g->server.$get['referens'] : '';
	?>
<div class='content'>
	<div class='header'>
		<p class='start'>Något gick fel!</p>
		<h2>Sidan kunde inte hittas</h2>
		<p class="info"><?php echo $referer; ?></p>
		<div class='search'>
			<img src='<?php echo $g->src('search.png', 'gfx'); ?>' alt='Search' />
			<input type='text' name='search' autocomplete='off' value='' class='normal radius' placeholder='Sök' />
			<div class='results'></div>
		</div>
				<?php if(defined('USER_ID')) { ?>
				<a href='<?php echo $g->href('admin'); ?>' class='begin radiusBottom'>Kontrollpanel</a>
				<a href='<?php echo $g->href('logout'); ?>' class='auth radiusBottom'>Logga ut</a>
				<?php } else { ?>
				<a href='<?php echo $g->href('skapa'); ?>' class='begin radiusBottom'>Börja här!</a>
				<a href='<?php echo $g->href('auth'); ?>' class='auth radiusBottom'>Logga in</a>
				<?php } ?>
	</div>
	<?php } ?>
	<div class='main'>
		<img src='<?php echo $g->src('404.png', 'gfx'); ?>' alt='404 - Sidan kunde inte hittas' />
	</div>
<?php if(!$_404) { ?>
</div>
<?php } } ?>