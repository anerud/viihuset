<?php
if($g->v1 == 'hem' || $g->v1 == 'omforeningen' || $g->v1 == 'omgivningen') {
	$qry = $g->_query("select * from #_posts where house_id = ?
					  	and p_section = ? and p_status = 'publish'
						order by p_order asc", HOUSE_ID, $g->v1);
	
	if($g->v2 == 'skicka')
		$houses->content($g->v1);
	
	$side = $faq->get($g->v1);
	?>
		<h1 class='<?php echo $admin[$g->v1]['class']; ?>'><?php echo $admin[$g->v1]['name']; ?></h1>
		<div class='con'>
			<?php
			success();
			suspended();
			?>
			<form action='<?php echo $g->href('admin', $g->v1, 'skicka'); ?>' method='post' class='form disable'>
			<?php
			if($g->_count($qry) == 0) {
				$i = 1;
				?>
				<div class='i-wrap' data-rel='s<?php echo $i; ?>'>
					<h3>Stycke</h3>
					<?php dropdown('Välj rubrik', $i, $g->v1); ?>
					
					<?php include('sales/bb.php'); ?>
					<textarea name='p_content_<?php echo $i; ?>' id='txt_<?php echo $i; ?>' placeholder='Skriv text här...'></textarea>
					<div class='attached'>
						<div class='bb'>
							<a href='#' data-rel='attach' tabindex='-1'><img src='<?php echo $g->src('bbFile.png', 'gfx'); ?>' alt='' /></a>
						</div>
						<h5>Bifogade filer</h5>
						<p>Inga filer är hittills bifogade.</p>
					</div>
					<div class='upload'>
						<h3>Ladda upp fil</h3>
						<input type='text' name='<?php echo $g->generate(5, 2, 0); ?>' value='' placeholder='Rubrik' />
						<input type='file' id='<?php echo $g->generate(5, 2, 0); ?>' value='' data-page='<?php echo $i.'_'.$g->v1; ?>' name='<?php echo $g->generate(5, 2, 0); ?>' tabindex='-1' />
					</div>
					<a href='#' data-rel='<?php echo $i; ?>' class='submit remove radius'>Ta bort stycke + filer</a>
					<div class='clear'></div>
				</div>
				<?php
			} else {
				$res = $g->_object($qry, 1);
				$i = 1;
				
				foreach($res as $r) {
				$last = $i == count($res) ? ' last' : '';
				
				$att = $houses->attachments($r->p_order);
				$pAtt = $att == null ? "<p>Inga filer är hittills bifogade.</p>" : $att;
				?>
				<div class='i-wrap' data-rel='s<?php echo $r->p_order; ?>'>
					<h3>Stycke</h3>
					<?php dropdown($r->p_title, $r->p_order, $g->v1); ?>
					<?php include('sales/bb.php'); ?>
					<textarea name='p_content_<?php echo $r->p_order; ?>' id='txt_<?php echo $r->p_order; ?>' placeholder='Skriv text här...'><?php echo $r->p_content; ?></textarea>
					<div class='attached'>
						<div class='bb'>
							<a href='#' data-rel='attach' tabindex='-1'><img src='<?php echo $g->src('bbFile.png', 'gfx'); ?>' alt='' /></a>
						</div>
						<h5>Bifogade filer</h5>
						<?php echo $pAtt; ?>
					</div>
					<div class='upload'>
						<h3>Ladda upp dokument</h3>
						<input type='text' name='<?php echo $g->generate(5, 2, 0); ?>' value='' placeholder='Rubrik' />
						<input type='file' id='<?php echo $g->generate(5, 2, 0); ?>' value='' data-page='<?php echo $r->p_order.'_'.$g->v1; ?>' name='<?php echo $g->generate(5, 2, 0); ?>' tabindex='-1' />
					</div>
					<a href='#' data-rel='<?php echo $r->p_order; ?>' class='submit remove radius'>Ta bort stycke + filer</a>
					<div class='clear'></div>
				</div>
				<?php
				$i++;
				}
			} ?>
			</form>
			<div class='add'>
				<a href='#' data-rel='<?php echo $g->v1; ?>' class='submit addnew radius'>Nytt stycke</a>
			</div>
				
			<a href='#' class='submit save radius'>Spara &amp; Publicera</a>
			<div class='navigate'>
				<a class='page-prev submit radius navigate' href='#'>Gå tillbaka</a>
				<a class='page-next submit radius navigate' href='#'>Nästa sida</a>
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