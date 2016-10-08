<?php
	$ac = ' ac';
?>
		<div class='content'>
			<div class='header'>
				<div class='logo'>
					<a href='<?php echo $g->href(); ?>'><img src='<?php echo $g->src('husetLogo.png', 'gfx'); ?>' alt='Vi i Huset.se' /></a>
				</div>
				<ul class='nav'>
					<li><a href='<?php echo $g->href(''); ?>' class='hem link<?php if($g->page == 'start') echo $ac; ?>'>Hem</a></li>
					<li><a href='<?php echo $g->href('kontakt'); ?>' class='kontakt link<?php if($g->page == 'kontakt') echo $ac; ?>'>Kontakta oss</a></li>
				</ul>
				<div class='search'>
					<img src='<?php echo $g->src('search.png', 'gfx'); ?>' alt='Search' />
					<input type='text' name='search' autocomplete='off' value='' class='big radius' placeholder='Sök brf här ...' />
					<div class='results'></div>
				</div>
				<?php if(defined('USER_ID')) { ?>
				<a href='<?php echo $g->href('admin'); ?>' class='begin radiusBottom'>Kontrollpanel</a>
				<a href='<?php echo $g->href('logout'); ?>' class='auth radiusBottom'>Logga ut</a>
				<?php } else { ?>
				<a href='<?php echo $g->href('auth'); ?>' class='init radiusBottom'>Logga in här</a>
				<?php } ?>
			</div>
		</div>