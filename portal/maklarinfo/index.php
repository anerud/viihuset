<?php
if($g->v1 == 'maklarinfo') {
	
	$preview = $g->v2 == 'preview' && (!defined('HOUSE_ID') || HOUSE_ID != $house->h_id);
	?>
	<div class='con maklarinfo'>
		<div class='info'>
			<h3>Information till mäklaren</h3>
	<?php if($preview) { ?>
	<p>Du har inte tillåtelse att titta på den här sidan.</p>
	<?php } else {
		$info = $houses->getInfo($g->v2);
		if(!$info) {
		?>
			<p>Föreningen har inte lagt in någon information ännu.</p>
		<?php
		} else {
		
			$houses->i_formed();
			
			$houses->i_year_built();
			
			$houses->i_living_area();
			
			$houses->i_fee();
			
			$houses->i_parking();
			
			$houses->i_apartments();
			
			$houses->i_heating();
			
			$houses->i_admin();
			
			$houses->i_image();
			
			$houses->i_entry_exit();
			
			$houses->i_transfer();
			
			$houses->i_legal_person();
			
			$houses->i_reperation();
			
			$houses->i_electricity();
			
			$houses->i_fee_incr_decr();
			
			$houses->i_known_changes();
			
			$houses->i_common_areas();
			
			$houses->i_second_hand();
			
			$houses->i_multiple_residents();
			
			$houses->i_additional();
			
			$att = $houses->attachments($g->v1, $house->h_id);
			if($att != null) { ?>
			<div class='attached p70'>
				<h5>Bifogade filer</h5>
				<?php echo $att; ?>
			</div>
			<?php }
		}
	}
	?>
		</div>
	</div>
	<?php
}
?>