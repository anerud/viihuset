<?php
if($g->v1 == 'fotoalbum') {
	$qry = $g->_query("select * from #_attachments where house_id = ? and a_type = 'images' order by a_id desc", $houses->info->h_id);
	?>
		<div class='con fotoalbum'>
			<div class='info'>
				<h3>Fotoalbum</h3>
				<?php
				if($g->_count($qry) == 0) {
					?>
					<p>Inga bilder har blivit uppladdade till fotoalbumet.</p>
					<?php
				} else {
				$i = 1; $res = $g->_object($qry, 1);
				foreach($res as $img) {
					$first = !empty($last) ? ' first' : '';
					$last  = $i % 3 == 0 ? ' last' : '';
					?>
				<div class='image<?php echo $last.$first; ?>'>
					<div class='item'>
						<a href='<?php echo image($img->a_name, $img->house_id, $img->a_type); ?>' rel='shadowbox[house]' title='<?php echo $g->replace($img->a_title); ?>'><img src='<?php echo image($img->a_name, $img->house_id, $img->a_type, 'list'); ?>' alt='' /></a>
					</div>
				</div>
					<?php
				++$i;
				} }
				?>
			</div>
		</div>
	<?php
}
?>