<?php
if($g->page == 'start') {
	include('header.php');
	bbcode($txts->hem_slider_text);
	bbcode($txts->hem_thomas_text);
	bbcode($txts->hem_louise_text);
	bbcode($txts->hem_peter_text);
	
	$log_user = isset($_POST['reg_user']) ? $_POST['reg_user'] : '';
	$log_mail = isset($_POST['reg_email']) ? $_POST['reg_email'] : '';
	?>
	
		<div class='slider'>
			<div class='content'>
				<div class='text'>
					<h2><?php echo $txts->hem_slider_title; ?></h2>
					<?php echo $txts->hem_slider_text; ?>
					<div class='bubble'>
						Snabb titt på hur er webbsida kan se ut?<br />
						<a href='<?=$g->href('brfviihuset');?>'>Klicka här</a>
					</div>
				</div>
			</div>
		</div>
		
		<div class='content'>
			<div class='sidebar'>
				<h4>Hos oss får ni</h4>
				<ul class='list'>
					<li>
						<b>Ett effektivare styrelsearbete</b>
						<p>Vi finns här för att avlasta ert styrelsearbete. Vi ser till att rätt person, får rätt information i rätt tid.</p>
					</li>
					<li>
						<b>En egen identitet</b>
						<p>Möjligheten att presentera en skräddarsydd sida, skapad efter era behov. Förutom ett ansikte utåt, finns funktioner som “anslagstavlan” som används för den intärna komunicationen.</p>
					</li>
					<li>
						<b>Mäklarinformation</b>
						<p>Tillsamman med mäklare har vi tagit fram ett formulär som innehåller samtlig information som en mäklaren behöver vid en försäljning.</p>
					</li>
					<li>
						<b>Vi gör jobbet!</b>
						<p>Låt oss <b>skapa</b>, <b>underhålla</b> samt <b>uppdatera</b> er sida! Med abonnemang hos Vi i huset, är uppdateringen av er sida bara ett telefonsamtal bort. Givetvis kan man ändra och uppdatera allt innehåll själv via 
vårt användarvänliga publiceringsverktyg.</p>
					</li>
				</ul>
			</div>
			<div class='con'>
				<div class='pitch'>
					<div class='step1'>
						<span class='f42'>Alltid</span><br />
						<span class='f24'>fullservice</span>
					</div>
					<div class='step2'>
						<span class='f33'>3500 <span>kr</span></span><br />
						<span class='f30'>per år</span>
					</div>
					<div class='step3'>
						<span class='f22'>Nöjd-kund</span><br />
						<span class='f33'>garanti</span>
					</div>
				</div>
				
				<h3>Skapa er webbsida här!</h3>
				<form action='<?php echo $g->href('start', 'skicka'); ?>' method='post' autocomplete='off'>
				<div class='divide'>
					<input type='text' name='reg_user' value='<?=$log_user;?>' placeholder='Föreningens namn ...' tabindex='1' />
					<input type='text' name='reg_email' value='<?=$log_mail;?>' placeholder='E-postadress ...' tabindex='2' />
					<input type='password' name='reg_pass' value='' placeholder='Lösenord ...' tabindex='3' />
					<input type='password' name='reg_re' value='' placeholder='Upprepa lösenord ...' tabindex='4' />
				</div>
				<?php if($g->v1 == 'skicka') $houses->create(); ?>
				<button type='submit'><img src='<?=$g->src('bigReg.png', 'gfx');?>' alt='Registrera förening' /></button>
				</form>
			</div>
		</div>
		
	<?php
}
?>