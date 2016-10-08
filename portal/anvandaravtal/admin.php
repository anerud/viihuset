<?php
if($g->v1 == 'anvandaravtal') {
	
	function agreement() {
		global $g;
		
		$post = isset($_POST['p_content']) ? trim($_POST['p_content']) : '';
		if(empty($post)) errors('Något innehåll måste skrivas.');
		
		$g->_exec("update #_settings set site_agreement = ?, site_agreement_time = ?", $post, time());
		
		cookie('success', "Sekretess &amp; användaravtalet har blivit uppdaterat!");
		$g->send($g->href($g->page, $g->v1));
	}
	?>
		<h1 class='<?php echo $admin[$g->v1]['class']; ?>'><?php echo $admin[$g->v1]['name']; ?></h1>
		<div class='con'>
			<div class='i-wrap'>
			<?php success('reverse'); ?>
				<form action='<?php echo $g->href('admin', $g->v1, 'skicka'); ?>' method='post' class='form disable'>
					<?php if($g->v2 == 'skicka') agreement(); ?>
					<?php include('sales/bb.php'); ?>
					<textarea name='p_content' id='txt' placeholder='Skriv text här...' class='big'><?php echo $g->set['site_agreement']; ?></textarea>
				</form>
			<a href='#' class='submit save radius'>Spara &amp; Publicera</a>
			</div>
		</div>
	<?php
}
?>
					