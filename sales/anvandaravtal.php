<?php
if($g->page == 'anvandaravtal') {
	?>
<div class='content'>
	<div class='header push'>
		<p class='start'></p>
		<h2>Sekretess &amp; användaravtal</h2>
		<p class="info">Senast uppdaterad: <?php echo dtime($g->set['site_agreement_time'], '{"opt":4, "full":1, "day":1}'); ?></p>
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
	<div class='main avtal'>
		<?php
			bbcode($g->set['site_agreement'], true);
			echo $g->set['site_agreement'];
		?>
	</div>
</div>

	<?php
}
?>