<?php
session_start();
if(!isset($_SERVER['HTTP_REFERER']) || preg_match('#/([\w\d-]+)\.php#i', $_SERVER['HTTP_REFERER'])) exit;

require_once('../../_inc/globals.class.php'); $g = new Globals();
include('../../'.$g->src('functions.php', '', true));

if(isset($_POST['key']) && isset($_POST['type'])) {
	
	$key = $g->html_reverse(urldecode($_POST['key']));
	$type = $_POST['type'];
	
	$json = json_decode($key);
	
	switch($type) {
		case 'perma' :
			echo normalize($json->name);
		break;
		case 'search' :
			$key = trim($key);
			if(!empty($key)) :
				$qry = $g->_query("select h_perma, h_name from #_houses where h_name like '%{$key}%' or h_address like '%{$key}%' order by h_name asc limit 12");
				
				if($g->_count($qry) == 0)
					echo "<p>Inga sökträffar</p>";
					
				foreach($g->_object($qry, 1) as $p)
					printf("<p><a href='%s'>%s</a></p>", $g->href($p->h_perma), preg_replace("@($key)@i", '<b>$1</b>', $p->h_name));
			endif;
		break;
		case 'common' :
			$c = $json->number + 1;
			?>
<li>
	<input id='c<?php echo $c; ?>' type='checkbox' name='ca_<?php echo $c; ?>' value='<?php echo $json->val; ?>' checked='checked'/>
	<label for='c<?php echo $c; ?>'><?php echo $json->val; ?></label>
</li>
			<?php
		break;
		case 'fees' :
			$c = $json->number + 1;
			?>
<li>
	<input id='k<?php echo $c; ?>' type='checkbox' name='fee_<?php echo $c; ?>' value='<?php echo $json->val; ?>' checked='checked'/>
	<label for='k<?php echo $c; ?>'><?php echo $json->val; ?></label>
</li>
			<?php
		break;
		case 'repair' :
			$c = !is_numeric($json->number) ? 0 : $json->number + 1;
			?>
<p>- <input type="text" maxlength="11" value="<?php echo $json->val; ?>" name="reparation_<?php echo $c; ?>"></p>
			<?php
		break;
		case 'additional' :
			$c = !is_numeric($json->number) ? 0 : $json->number + 1;
			?>
<p>- <input type="text" maxlength="255" value="<?php echo $json->val; ?>" name="additional_<?php echo $c; ?>"></p>
			<?php
		break;
		case 'addnew' :
		
			require_once('../../'.$g->src('auth.class.php', 'classes', true));
			$auth = new Auth;
			
			$qry = $g->_query("select p_order from #_posts where house_id = ?
							  	and p_section = ? and p_status = 'publish'
								order by p_order desc", HOUSE_ID, $json->dep);
			$order = $g->_count($qry) == 0 ? 1 : $g->_result($qry) + 1;
			
			// Strip 's' from i-wrap data-rel
			$json->last = isset($json->last)
				 ? substr($json->last, 1) : 0;
			
			$i = $order > $json->last ? $order : $json->last + 1;
			?>
<div class='i-wrap' data-rel='s<?php echo $i; ?>'>
	<h3>Stycke</h3>
	<?php dropdown('Välj rubrik', $i, $json->dep); ?>	
	<?php include('../modules/bb.php'); ?>
	<textarea name='p_content_<?php echo $i; ?>' id='txt_<?php echo $i; ?>' placeholder='Skriv text här...'></textarea>
	<div class='attached'>
		<div class='bb'>
			<a href='#' data-rel='attach' tabindex='-1'><img src='<?php echo $g->src('bbFile.png', 'gfx'); ?>' alt='' /></a>
		</div>

		<h5>Bifogade filer</h5>
		<p>Inga filer är hittills bifogade.</p>
	</div>
	<div class='upload'>
		<h3>Ladda upp dokument</h3>
		<input type='text' name='<?php echo $g->generate(5, 2, 0); ?>' value='' placeholder='Rubrik' />
		<input type='file' id='<?php echo $g->generate(5, 2, 0); ?>' value='' data-page='<?php echo $i.'_'.$json->dep; ?>' name='<?php echo $g->generate(5, 2, 0); ?>' tabindex='-1' />
	</div>
	<a href='#' data-rel='<?php echo $i; ?>' class='submit remove radius'>Ta bort stycke + filer</a>
	<div class='clear'></div>
</div>
			<?php
		break;
		
		case 'addnew-admin' :
			$qry = $g->_query("select faq_order from #_faq where faq_section = ?
								order by faq_order asc", $json->dep);
			$order = $g->_count($qry) == 0 ? 1 : $g->_result($qry) + 1;
			
			// Strip 's' from i-wrap data-rel
			$json->last = isset($json->last)
				 ? substr($json->last, 1) : 0;
			
			$i = $order > $json->last ? $order : $json->last + 1;
			?>
<div class='i-wrap' data-rel='s<?php echo $i; ?>'>
	<input type='text' name='faq_question_<?php echo $json->dep.'_'.$i; ?>' value='' placeholder='Fråga' class='extend' />
	<textarea name='faq_answer_<?php echo $json->dep.'_'.$i; ?>' placeholder='Förklaring' class='minimize'></textarea>
	<a href='#' data-rel='<?php echo $i; ?>' class='submit admin remove radius'>Ta bort fråga</a>
	<div class='clear'></div>
</div>
			<?php
		break;
		
		case 'person' :
			$i = $json->last + 1;
		?>
<div class='people' data-rel='<?php echo $i; ?>'>
	<h3>Kontaktperson</h3>
	<div class='divide'>
		<input type='text' name='person_<?php echo $i; ?>' value='' placeholder='Namn' />
		<input type='text' name='role_<?php echo $i; ?>' value='' placeholder='Roll i styrelsen' />
		<input type='text' name='email_<?php echo $i; ?>' value='' placeholder='E-postadress' />
		<input type='text' name='number_<?php echo $i; ?>' value='' placeholder='Telefonnummer' />
	</div>
	<a href='#' class='submit remove radius person'>Ta bort kontaktperson</a>
</div>
<?php
		break;
	}
}
?>