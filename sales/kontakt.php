<?php
if($g->page == 'kontakt') {
	include('header.php');
	bbcode($txts->kontakt_slider_text);
	?>
		<div class='slider contact'>
			<div class='content'>
				<div class='text'>
					<h2><?php echo $txts->kontakt_slider_title; ?></h2>
					<?php echo $txts->kontakt_slider_text; ?>
				</div>
			</div>
		</div>
		
		<div class='content contact'>
			<div class='con'>
				<?php success('reverse'); ?>
				<h4>Fråga oss</h4>
				<form action='<?php echo $g->href($g->page, 'skicka'); ?>' method='post'>
				<?php if($g->v1 == 'skicka') $houses->contact(false); ?>
					<div class='divide'>
						<input type='text' name='cname' placeholder='Namn' />
						<input type='text' name='cmail' placeholder='E-postadress' />
					</div>
					<input type='text' name='csubject' placeholder='Ämne' class='extend' />
					<textarea name='cmsg' placeholder='Skriv meddelande här...'></textarea>
				</form>
				<a href='#' class='submit mail radius'>Skicka</a>
			</div>
			<div class='side'>
				<h4>Kontakt info</h4>
				<ul class='list contact'>
					<li class='hus'>
						<b>Besöksadress</b>
						<p>Tobaksspinnargatan 6, 117 36 Stockholm</p>
					</li>
					<li class='epost'>
						<b>E-postadress</b>
						<p><?php echo email('info@viihuset.se'); ?></p>
					</li>
					<li class='tfn'>
						<b>Telefon</b>
						<p>073-692 63 44</p>
					</li>
				</ul>
				<div class='ext'>
					<a href='<?php echo $g->href('anvandaravtal'); ?>'>Sekretess &amp; användaravtal</a>
					<p>Org nr. 969768-9231</p>
					<p>Godkända för F-skatt</p>
				</div>
			</div>
		</div>

	<?php
}
?>