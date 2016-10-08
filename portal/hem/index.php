<?php
if($g->v1 == 'hem' || $g->v1 == 'omforeningen' || $g->v1 == 'omgivningen') {
	
	$qry = $g->_query("select * from #_posts where house_id = ?
					  	and p_status = 'publish' and p_section = ?
						order by p_order asc", $house->h_id, $g->v1);
	
	$side = false;
	?>
		<div class='con'>
			<?php
			if($g->_count($qry) == 0) {
				?>
				<div class='info'>
					<h3>Innehåll saknas</h3>
					<p>Föreningen har inte lagt in någon information ännu.</p>
				</div>
				<?php
			} else {
				$res = $g->_object($qry, 1);
				$c = 1;
				foreach($res as $r) {
					bbcode($r->p_content);
					// Add up arrow to all headlines except first
					$span = $c != 1 ? '<span></span>' : '';
					$att = $houses->attachments($r->p_order, $house->h_id);
					?>
				<div class='info'>
					<?php if(!empty($r->p_title)) { $side = true; ?>
					<h3><?php echo $r->p_title.$span; ?></h3><?php } ?>
					<?php echo $r->p_content; ?>
					
					<?php if($att != null) { ?>
					<div class='attached'>
						<h5>Bifogade filer</h5>
						<?php echo $att; ?>
					</div>
					<?php } ?>
				</div>
				<?php ++$c;
				}
			}
			?>
		</div>
		<?php if($side) { ?>
		<div class='side'>
			<h4>Rubriker</h4>
			<ul class='list'>
				<?php if(isset($res)) foreach($res as $r) {
					$class = empty($r->p_title) ? " class='hide'" : '';
					?>
				<li<?php echo $class; ?>>
					<b><a href='#'><?php echo $r->p_title; ?></a></b>
				</li>
				<?php } ?>
			</ul>
		</div>
		<?php } ?>
	<?php
}
?>