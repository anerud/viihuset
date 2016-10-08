<?php
if($g->v1 == 'vanligafragor') {
	$sect = array('basinformation' => 'Basinformation',
				  'hem' => 'Hem',
				  'omforeningen' => 'Om föreningen',
				  'omgivningen' => 'Omgivningen',
				  'maklarinfo' => 'Information till mäklaren',
				  'fotoalbum' => 'Fotoalbum',
				  'anslagstavlan' => 'Anslagstavlan',
				  'auth' => 'Logga in',
				  'skapa' => 'Skapa konto');
	
	if($g->v2 == 'skicka') {
		$faq->save();
	}
	?>
	
	<h1 class='<?php echo $admin[$g->v1]['class']; ?>'><?php echo $admin[$g->v1]['name']; ?></h1>
	<div class="con push">
	<?php success('reverse'); ?>
	<form action='<?php echo $g->href('admin', $g->v1, 'skicka'); ?>' method='post' class='form disable'>
	<?php $ki = 0;
	foreach($sect as $k => $v) {
		$v = $ki != 0 ? "$v<span></span>" : $v;
		$i = 1;
		?>
		<div>
			<h2><?php echo $v; ?></h2>
		<?php
		$qry = $g->_query("select * from #_faq where faq_section = ? order by faq_order asc", $k);
		if($g->_count($qry) == 0) {
			?>
			<div class='i-wrap' data-rel='s1'>
				<input type='text' name='faq_question_<?php echo $k; ?>_1' value='' placeholder='Fråga' class='extend' />
				<textarea name='faq_answer_<?php echo $k; ?>_1' placeholder='Förklaring' class='minimize'></textarea>
				<a href='#' data-rel='<?php echo $k; ?>_1' class='submit remove admin radius'>Ta bort fråga</a>
				<div class='clear'></div>
			</div>
			<?php
		} else {
			while($obj = $g->_object($qry)) {
			?>
			<div class='i-wrap' data-rel='s<?php echo $obj->faq_order; ?>'>
				<input type='text' name='faq_question_<?php echo $k.'_'.$obj->faq_order; ?>' value='<?php echo $obj->faq_question; ?>' placeholder='Fråga' class='extend' />
				<textarea name='faq_answer_<?php echo $k.'_'.$obj->faq_order; ?>' placeholder='Förklaring' class='minimize'><?php echo $obj->faq_answer; ?></textarea>
				<a href='#' data-rel='<?php echo $k.'_'.$obj->faq_order; ?>' class='submit remove admin radius'>Ta bort fråga</a>
				<div class='clear'></div>
			</div>
			<?php } ?>	
	<?php } $ki++; ?>	
			<div class='add admin'>
				<h3>Alternativ</h3>
				<a href='#' data-rel='<?php echo $k; ?>' class='submit addnew admin radius'>Ny fråga</a>
				<a href='#' class='submit save admin radius'>Spara allt</a>
			</div>
		</div>
	<?php } ?>
	
	</form>
	<a href='#' class='submit save end radius'>Spara &amp; Publicera</a>
	</div>
	<div class='side'>
		<h4>Avdelningar</h4>
		<ul class="list">
		<?php foreach($sect as $s) { ?>
			<li>
				<b><a href="#"><?php echo $s; ?></a></b>
			</li>
		<?php } ?>
		</ul>
	</div>
	<?php
}
?>