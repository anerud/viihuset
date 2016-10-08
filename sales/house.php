<?php
if($house) {
	
	if(!empty($house->h_brf))     $implode[] = $house->h_brf;
	if(!empty($house->h_address)) $implode[] = $house->h_address;
	if(!empty($house->h_postal))  $implode[] = $house->h_postal;
	if(!empty($house->h_town))    $implode[] = $house->h_town;
	
	$head = strpos($house->h_image, 'placeholder.png') != 0 ? '' : sprintf("<img src='%s' alt='%s' />", $houses->info->h_image, $house->h_name);
	?>
	
		<div class='content'>
			<div class='header push'>
				<h2><?php echo $house->h_name; ?></h2>
				<p class='info'>
					<?php echo @implode(' | ', $implode); ?>
				</p>
				<?php if(defined('USER_ID')) { ?>
				<a href='<?php echo $g->href('admin'); ?>' class='login radius'>Kontrollpanel</a>
				<?php } else { ?>
				<a href='<?php echo $g->href('auth'); ?>' class='login radius'>Logga in här</a>
				<?php } ?>
			</div>
			<div class='main'>
				<?php
				
				if($house->h_suspend == 1 && ( !defined('HOUSE_ID') ||
						( $house->h_id != HOUSE_ID && USER_RANK < 3 )) )
					$link = "suspended.php";
				else {
					$link = sprintf("portal/%s/index.php", $g->v1);
					suspended();
				}
				
				echo $link;
				
				if(!file_exists($link)){
					$link = "404.php";
				}
				
				if($link != 'suspended.php' && $link != '404.php' && $g->v1 != 'kontakt'){
					echo $head;
				}
				
				$_404 = true;
				include($link);
				?>
			</div>
		</div>
		
	<?php
}
?>