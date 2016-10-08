<?php
if($g->v1 == 'texter') {
	$sect = array('hem' => array('name' => 'Hem',
								 'inputs' => array('slider', 'thomas', 'louise', 'peter'),
								 'titles' => array('slider', 'Thomas Samsioe', 'Louise Klingborg', 'Peter Samsioe')
								 ),
				  'om-oss' => array('name' => 'Om oss',
									'inputs' => array('slider', 'background', 'problem', 'solution'),
									'titles' => array('slider', 'background', 'problem', 'solution')
									),
				  'kontakt' => array('name' =>'Kontakta oss',
									 'inputs' => array('slider'),
									 'titles' => array('slider')
									 ),
				  'maklarinfo' => array('name' => 'Mäklarinformation',
										'inputs' => array('ingress'),
										'titles' => array('Mäklarinfoingress')
										)
				  );
	
	if($g->v2 == 'skicka') {
		$g->_exec("update #_settings set site_texts = ?", addcslashes(json_encode($g->collect()), '\\'));
		cookie('success', 'Allmänna texter har blivit uppdaterade!');
		$g->send($g->href($g->page, $g->v1));
	}
	?>
	
	<h1 class='<?php echo $admin[$g->v1]['class']; ?>'><?php echo $admin[$g->v1]['name']; ?></h1>
	<div class="con push">
	<?php success('reverse'); ?>
	<form action='<?php echo $g->href('admin', $g->v1, 'skicka'); ?>' method='post' class='form disable'>
	<?php $ki = 0;
	foreach($sect as $k => $v) {
		$v['name'] = $ki != 0 ? "{$v['name']}<span></span>" : $v['name'];
		$i = 1;
		?>
		<div>
			<h2><?php echo $v['name']; ?></h2>
			<?php $d = 0; foreach($v['inputs'] as $inp) {
				
				$name = sprintf("%s_%s_title", $k, $inp);
				$text = sprintf("%s_%s_text", $k, $inp);
				$title = $inp == $v['titles'][$d] ? $txts->{$name} : $v['titles'][$d];
				$disable = $inp != $v['titles'][$d] ? " readonly='readonly'" : '';
				?>
			<div class='i-wrap'>
				<input type='text' name='<?php echo $name; ?>' id='<?php echo $name; ?>' value='<?php echo $title; ?>' placeholder='Rubrik' class='extend'<?php echo $disable; ?> />
				<?php include('sales/bb.php'); ?>
				<textarea name='<?php echo $text; ?>' id='<?php echo $text; ?>' placeholder='Text' class='minimize'><?php echo $txts->{$text}; ?></textarea>
			</div>
			<?php ++$d; } ?>
		</div>
	<?php ++$ki; } ?>
	</form>
	<a href='#' class='submit save end radius'>Spara &amp; Publicera</a>
	</div>
	<div class='side'>
		<h4>Avdelningar</h4>
		<ul class="list">
		<?php foreach($sect as $s) { ?>
			<li>
				<b><a href="#"><?php echo $s['name']; ?></a></b>
			</li>
		<?php } ?>
		</ul>
	</div>
	<?php
}
?>