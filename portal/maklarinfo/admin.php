<?php
if($g->v1 == 'maklarinfo') {
	
	if($houses->getInfo('published', HOUSE_ID))
		$obj = $houses->obj;
	else ini_set('display_errors', false);
	
	$rep  = json_decode($obj->i_reparation);
	$rok  = json_decode($obj->i_rok);
	$fees = !empty($obj->i_fee) ? get_object_vars(json_decode($obj->i_fee)) : array();
	$com  = !empty($obj->i_ca) ? get_object_vars(json_decode($obj->i_ca)) : array();
	$add  = !empty($obj->i_additional) ? get_object_vars(json_decode($obj->i_additional)) : array();
	
	// Parking
	$park[] = $obj->i_parking == 0 ? " checked='checked'" : '';
	$park[] = $obj->i_parking == 1 ? " checked='checked'" : '';
	
	// Legal person
	$law[] = $obj->i_accept_legal_person == 0 ? " checked='checked'" : '';
	$law[] = $obj->i_accept_legal_person == 1 ? " checked='checked'" : '';
	
	// Fee incr decr
	$fid[] = $obj->i_fee_incr_decr === 0 ? " checked='checked'" : '';
	$fid[] = $obj->i_fee_incr_decr == 'incr' ? " checked='checked'" : '';
	$fid[] = $obj->i_fee_incr_decr == 'decr' ? " checked='checked'" : '';
	
	// Known changes
	$kn[] = $obj->i_known_changes == 0 ? " checked='checked'" : '';
	$kn[] = $obj->i_known_changes == 1 ? " checked='checked'" : '';
	
	$att = $houses->attachments($g->v1, $house->h_id);
	$pAtt = $att == null ? "<p>Inga filer är hittills bifogade.</p>" : $att;
					
	if($g->v2 == 'skicka') {
		$houses->saveInfo();
	}
	$admin[$g->v1]['name'] = "Information till mäklaren";
	?>

	<h1 class='<?php echo $admin[$g->v1]['class']; ?>'><?php echo $admin[$g->v1]['name']; ?></h1>
	<div class='con <?php echo $admin[$g->v1]['class']; ?>'>
		<?php
		suspended();
		success();
		
		bbcode($txts->maklarinfo_ingress_text);
		?>
		<div class='info'>
			<?php echo $txts->maklarinfo_ingress_text; ?>
		</div>
		
		<form action='<?php echo $g->href('admin', $g->v1, 'skicka'); ?>' method='post' class='form push-up disable'>
			<div class='number'>
				<b>1)</b> Föreningen bildades år <input type='text' name='i_formed' class='half-norm' value='<?php echo $obj->i_formed; ?>' maxlength='11' />
			</div>
			<div class='number'>
				<b>2)</b> Fastigheten byggdes år <input type='text' name='i_year_built' class='half-norm' value='<?php echo $obj->i_year_built; ?>' maxlength='11' /> och totalrenoverades år <input type='text' name='i_renovated' class='half-norm' value='<?php echo $obj->i_renovated; ?>' maxlength='12' />
			</div>
			<div class='number'>
				<b>3)</b> Föreningens totala boarea uppgår till <input type='text' name='i_living_area' class='small' value='<?php echo $obj->i_living_area; ?>' maxlength='6' /> kvadratmeter.
			</div>
			<div class='number'>
				<b>4)</b> I avgiften ingår:<br />
				<ul class='rok'>
				<?php
					$fee = array('Värme', 'VA', 'Bredband', 'Kabel-tv', 'Förråd');
					$ki = 1;
					foreach($fees as $c => $f) {
						if(in_array($c, array_keys($fee))) continue;
						$fee[$c] = $f;
					}
					
					$cCount = sizeof($fee);
					$cSize = $cCount > 10 ? 6 : 4;
					foreach($fee as $k => $f) {
						$cc = in_array($k, array_keys($fees)) ? " checked='checked'" : '';
						?>
					<li>
						<input id='k<?php echo $k; ?>' type='checkbox' name='fee_<?php echo $k; ?>' value='<?php echo $f; ?>'<?php echo $cc; ?>/>
						<label for='k<?php echo $k; ?>'><?php echo $f; ?></label>
					</li>
						<?php
					if($ki++ % $cSize == 0 && $ki < $cCount) echo "</ul><ul class='rok'>";
					}
					?>
				</ul>
				<!--
				<div class='clear'>
					Kabel-tv/internet leverantör är <input type='text' name='i_cable_provider' value='<?php echo $obj->i_cable_provider; ?>' class='big' maxlength='120' />
				</div>
				-->
				<p class='clear'><i>I avgiften ingår även:</i></p>
				<input type='text' name='fees' value='' />
				<a class="submit addnew radius blue" href="#">Lägg till</a>
			</div>
			<div class='number'>
				<b>5)</b> Garageplats eller parkeringsplats <input type='radio' name='i_parking' value='1'<?php echo $park[1]; ?> /> följer <input type='radio' name='i_parking' value='0'<?php echo $park[0]; ?> /> följer ej med bostadsrätten.
				<div class='push-out'>
					<p>Möjlighet till parkering finns <input type='text' name='i_parking_lot' class='big' value='<?php echo $obj->i_parking_lot; ?>' maxlength='120' /></p>
				</div>
			</div>
			<div class='number'>
				<b>6)</b> I föreningen finns <input type='text' name='i_apartments' class='small' value='<?php echo $obj->i_apartments; ?>' maxlength='4' /> lägenheter och <input type='text' name='i_facilities' class='small' value='<?php echo $obj->i_facilities; ?>' maxlength='4' /> lokal/er.<br />
				<p>Uppdelning enligt följande:</p>
				<ul class='rok'>
				<?php for($r = 1; $r <= 8; $r++) {
					$r_txt = $r == 8 ? "St 8 rok eller större." : "St $r rok";
					?>
					<li><input type='text' name='rok_<?php echo $r; ?>' class='mini' value='<?php echo $rok->{$r}; ?>' maxlength='3' /> <?php echo $r_txt; ?></li>
				<?php 
					if($r == 4) echo "</ul><ul class='rok'>";
				} ?>
				</ul>
			</div>
			<div class='number'>
				<b>7)</b> Huset uppvärmning sker genom <input type='text' name='i_heating' class='normal' value='<?php echo $obj->i_heating; ?>' maxlength='120' />, leverantör är <input type='text' name='i_heating_provider' class='normal' value='<?php echo $obj->i_heating_provider; ?>' maxlength='120' />
			</div>
			<div class='number'>
				<b>8)</b> Föreningens tekniska förvaltning sköts av <input type='text' name='i_admin_technical' class='big' value='<?php echo $obj->i_admin_technical; ?>' maxlength='120' /> och den ekonomiska
				<p>förvaltningen sköts av <input type='text' name='i_admin_economical' class='big' value='<?php echo $obj->i_admin_economical; ?>' maxlength='120' /></p>
			</div>
			<div class='number'>
				<b>9)</b> Beställning av mäklarbild skickas till <input type='text' name='i_image_economical' class='big' value='<?php echo $obj->i_image_economical; ?>' maxlength='120' />
			</div>
			<div class='number'>
				<b>10)</b> Ansökan om in- och utträde i föreningen skickas till <input type='text' name='i_entry_exit' class='big' value='<?php echo $obj->i_entry_exit; ?>' maxlength='120' />
			</div>
			<div class='number'>
				<b>11)</b> Föreningen tar ut en överlåtelseavgift om <input type='text' name='i_transfer_charge' class='medium' value='<?php echo $obj->i_transfer_charge; ?>' maxlength='120' /> som betalas av <input type='text' name='i_transfer_charge_payer' class='normal' value='<?php echo $obj->i_transfer_charge_payer; ?>' maxlength='120' />
				<p>och en pantsättningsavgift om <input type='text' name='i_pawn_fee' class='medium' value='<?php echo $obj->i_pawn_fee; ?>' maxlength='120' /> som betalas av <input type='text' name='i_pawn_fee_payer' class='normal' value='<?php echo $obj->i_pawn_fee_payer; ?>' maxlength='120' /></p>
			</div>
			<div class='number'>
				<b>12)</b> Föreningen <input type='radio' name='i_accept_legal_person' value='1'<?php echo $law[1]; ?> /> accepterar <input type='radio' name='i_accept_legal_person' value='0'<?php echo $law[0]; ?> /> accepterar ej juridisk person som medlem.
			</div>
			<div class='number'>
				<b>13)</b> Följande reparationer / ombyggnader har gjorts på fastigheten:
				<?php
				$rAtt = array('pipes' => 'Fastigheten är stambytt år',
							  'electricity' => 'Elstigar är bytta år',
							  'stairwells' => 'Trapphus renoverades år',
							  'washing' => 'Tvättmaskiner byttes ut år',
							  'facade' => 'Fasaden renoverades/målades om år');
				?>
				<div class='push-out'>
					<?php
					foreach((array)$rep as $r => $v) {
						if(!is_numeric($r)) continue;
						$rAtt[$r] = '';
					}
					foreach($rAtt as $r => $v) {
						$class = is_numeric($r) ? 'massive' : 'half-norm';
						$len   = is_numeric($r) ? 255 : 11;
						if(!isset($rep->{$r})) $rep->{$r} = '';
						?>
					<p>- <?php echo $v; ?> <input type='text' name='reparation_<?php echo $r; ?>' class='<?php echo $class; ?>' value='<?php echo $rep->{$r}; ?>' maxlength='<?php echo $len; ?>' /></p>
						<?php
					}
					?>
					<p><i>Lägg till egna renoveringar (gjord renovering och årtal)</i></p>
					<input type='text' name='repair' value='' />
					<a class="submit addnew radius blue" href="#">Lägg till</a>
				</div>
			</div>
			<div class='number'>
				<b>14)</b> Fastighetens elleverantör är <input type='text' name='i_electricity_provider' class='big' value='<?php echo $obj->i_electricity_provider; ?>' maxlength='120' />
			</div>
			<div class='number'>
				<b>15)</b> Föreningen har i dagsläget planerat en avgiftshöjning / avgiftssänking:
				<div class='push-out'>
					<p><input type='radio' name='i_fee_incr_decr' value='incr'<?php echo $fid[1]; ?> /> Ja, avgiftshöjningen / <input type='radio' name='i_fee_incr_decr' value='decr'<?php echo $fid[2]; ?> /> avgiftssänking som avser <input type='text' name='i_fee_percent' class='mini' value='<?php echo $obj->i_fee_percent; ?>' maxlength='3' /> % av nuvarande avgift fr.o.m. <input type='text' name='i_fee_start' class='half-norm' value='<?php echo $obj->i_fee_start; ?>' maxlength='12' placeholder='åååå-mm-dd' /></p>
					<p><input type='radio' name='i_fee_incr_decr' value='0'<?php echo $fid[0]; ?> /> Nej, planerar ej avgiftshöjningen / avgiftssänking</p>
				</div>
			</div>
			
			<div class='number'>
				<b>16)</b> Föreningen har följande genensamhetsutrymmen:<br />
				<ul class='rok'>
				<?php
					$ca = array('Tvättstuga', 'Innergård', 'Samlingslokal', 'Övernattningslägenhet', 'Takterass', 'Grillplats', 'Garage');
					$ci = 1;
					foreach($com as $c => $f) {
						if(in_array($c, array_keys($ca))) continue;
						$ca[$c] = $f;
					}
					
					$cCount = sizeof($ca);
					$cSize = $cCount > 10 ? 6 : 4;
					//$cSize = $cCount > 15 ? 5 : $cSize;
					foreach($ca as $c => $f) {
						$cc = in_array($c, array_keys($com)) ? " checked='checked'" : '';
						?>
					<li>
						<input id='c<?php echo $c; ?>' type='checkbox' name='ca_<?php echo $c; ?>' value='<?php echo $f; ?>'<?php echo $cc; ?>/>
						<label for='c<?php echo $c; ?>'><?php echo $f; ?></label>
					</li>
						<?php
						if($ci++ % $cSize == 0 && $ci < $cCount) echo "</ul><ul class='rok'>";
					}
					?>
				</ul>
				<div class='clear'></div>
				<p><i>Lägg till egna genensamhetsutrymmen</i></p>
				<input type='text' name='common' value='' />
				<a class="submit addnew radius blue" href="#">Lägg till</a>
			</div>
			<div class='number'>
				<b>17)</b> Vid andrahandsuthyrning av bostad gäller följande:<br />
				<p><textarea name='i_second_hand'><?php echo $obj->i_second_hand; ?></textarea></p>
			</div>
			<div class='number'>
				<b>18)</b> Vid delat boende där en eller fler parter ej bor permanent gäller följande:<br />
				<p><textarea name='i_multiple_residents'><?php echo $obj->i_multiple_residents; ?></textarea></p>
			</div>
			<div class='number'>
				<b>19)</b> Lägg till egen information:<br />
				<?php
				foreach($add as $r => $v) {
					?>
				<p>- <input type='text' name='additional_<?php echo $r; ?>' class='massive' value='<?php echo $v; ?>' /></p>
					<?php
				}
				?>
				<input type='text' name='additional' value='' />
				<a class="submit addnew radius blue" href="#">Lägg till</a>
			</div>
			<div class='number i'>
				<b>20)</b> Bilagor
				<div class='push-out'>
					<div class='attached i'>
						<div class='bb'>
							<a href='#' data-rel='attach' tabindex='-1'><img src='<?php echo $g->src('bbFile.png', 'gfx'); ?>' alt='' /></a>
						</div>
						<h5>Bifogade filer</h5>
						<?php echo $pAtt; ?>
					</div>
					<div class='upload'>
						<h3>Ladda upp dokument</h3>
						<input type='text' name='<?php echo $g->generate(5, 2, 0); ?>' value='' placeholder='Rubrik' />
						<input type='file' id='<?php echo $g->generate(5, 2, 0); ?>' value='' data-page='<?php echo '1_'.$g->v1; ?>' name='<?php echo $g->generate(5, 2, 0); ?>' tabindex='-1' />
					</div>
				</div>
			</div>
		
			<?php $side = $faq->get($g->v1);
			if($side) { ?>
			<div class='side full'>
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
			<?php } ?>
		</form>
		
		<a href='#preview' class='submit save radius preview'>Förhandsvisa</a>
		<a href='#' class='submit save radius'>Spara &amp; Publicera</a>
		<p class='preview'>
			Om förhandsvisa inte öppnar en ny ruta,<br />kan du besöka följande länk: <a href='<?php echo $g->server.$g->href($house->h_perma, $g->v1, 'preview'); ?>' target='_blank'>Förhandsvisa</a>
		</p>
		<div class='navigate'>
			<a class='page-prev submit radius navigate' href='#'>Gå tillbaka</a>
			<a class='page-next submit radius navigate' href='#'>Nästa sida</a>
		</div>
	</div>
	<?php
}
?>