<?php
if($g->v1 == 'kontakt') {
	if(!empty($house->h_address)) {
	?>
	<div id='google'></div><?php } ?>
	
	<div class='con cInfo'>
		<div class='info'>
			<?php success('reverse'); ?>
			<h3>Kontakta oss</h3>
			<form action='<?php echo $g->href($g->page, $g->v1, 'skicka'); ?>' method='post'>
			<?php
			if($g->v2 == 'skicka') $houses->contact();
			?>
			<div class='divide'>
				<input type='text' name='cname' placeholder='Namn' value='<?php echo @$_POST['cname']; ?>' />
				<input type='text' name='cmail' placeholder='E-postadress' value='<?php echo @$_POST['cmail']; ?>' />
			</div>
			<input type='text' name='csubject' placeholder='Ämne' value='<?php echo @$_POST['csubject']; ?>' class='full' />
			<textarea name='cmsg' placeholder='Skriv meddelande här...'><?php echo @$_POST['cmsg']; ?></textarea>
			</form>
			<a href='#' class='submit mail radius'>Skicka</a>
		</div>
	</div>
	<div class='side'>
		<h4>Kontakta oss</h4>
		<ul class='list contact'>
			
			<li class='hus'>
				<b>Brf / Bf Adress</b>
				<?php echo $house->h_name; if(!empty($house->h_postal)) {?>,<br />
				<?php echo $house->h_postal.' '.$house->h_town; } ?>
				
				<?php if(!empty($house->h_address)) { ?><br /><br />
				<b>Fastighetsadress</b>
				<?php echo $house->h_address; ?>,<br />
				<?php echo $house->h_postal.' '.$house->h_town; ?>
				<?php } ?>
			</li>
			<?php if(!empty($house->h_people)) {
				$json = json_decode($house->h_people);
				?>
			<li class='ppl'>
			<?php $ki = 1; foreach($json as $k => $v) {
				if(empty($v->person)) continue;
				if($ki > 1) echo BR;
				?>
				<b><?php echo $v->person; ?></b><?php if(!empty($v->role)) { ?>
				<?php echo $v->role; ?><br /><?php } if(!empty($v->email)) { ?>
				<?php echo email($v->email); ?><br /><?php } if(!empty($v->number)) { ?>
				<?php echo $v->number; ?><br /><?php } ?>
				<?php $ki++; } ?>
			</li>
			<?php } ?>
		</ul>
	</div>
	<?php
}
?>