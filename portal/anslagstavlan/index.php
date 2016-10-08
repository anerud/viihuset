
<script>
    
    function clickRedirectHandler(event) {
  var href = event.target.href;
  if(confirm(href)){
    return true;
  }else{
    event.preventDefault();
    return false;
  }
}

function setRedirectConfirmationDialogs() {
  var nodes = document.querySelectorAll("a");
  for(var i = 0;i<nodes.length; i++){
    nodes[i].addEventListener("click", clickRedirectHandler);
  }
}
    
    </script>





<?php
if($g->v1 == 'anslagstavlan') {
	
	$side = true;
	
	$qboards = $g->_query("SELECT DISTINCT(r_email) FROM #_board_regs WHERE house_id = ? AND r_type = 'board' ORDER BY r_email ASC", HOUSE_ID);
	$qmembers = $g->_query("SELECT DISTINCT(r_email) FROM #_board_regs WHERE house_id = ? AND r_type = 'member' ORDER BY r_email ASC", HOUSE_ID);
	?>
	<?php if($g->page == 'admin') { ?>
	<h1 class='<?php echo $admin[$g->v1]['class']; ?>'><?php echo $admin[$g->v1]['name']; ?></h1>
	<?php } ?>
	<div class='con'>
		<?php if($g->page != 'admin') { ?>
		<div class='info'>
			<div class='important-info special'>
				<h5>Missa ingen viktig information</h5>
				<p><b>Är du boende i huset och vill vara uppdaterad om vad som sker i din<br />förening? Skriv in din e-posadress i fältet nedan.</b></p>
				
				<input type='text' name='register-email' value='' placeholder='Skriv e-postadress här ...' />
				<a class="submit addnew radius reg" href="#">Lägg till mig</a>
			</div>
		</div>
		<?php } else { ?>
		<div class='message'>
			<h5>E-postadresser till styrelsemedlemmar</h5>
			<div class='mess m-list' data-type='board'>
			<?php while($sboard = $g->_result($qboards)) { ?>
				<span class='e-list'><?=$sboard;?> <img src='<?=$g->src('e-remove.png', 'gfx');?>' alt='x' /></span>
			<?php } ?>
			</div>
			<input type='text' name='board-email' value='' placeholder='Skriv e-postadress här ...' />
			<a class="submit addnew radius reg" href="#">Lägg till</a>
		</div>
		
		<div class='message'>
			<h5>E-postadresser till föreningsmedlemmar</h5>
			<div class='mess m-list' data-type='member'>
			<?php while($smember = $g->_result($qmembers)) { ?>
				<span class='e-list'><?=$smember;?> <img src='<?=$g->src('e-remove.png', 'gfx');?>' alt='x' /></span>
			<?php } ?>
			</div>
			<input type='text' name='member-email' value='' placeholder='Skriv e-postadress här ...' />
			<a class="submit addnew radius reg" href="#">Lägg till</a>
		</div>
		<?php } ?>
		
		<div class='info'>
			<div class='important-info'>
				<div class='i-wrap'>
				<form method='post'>
					<h5>Ämne</h5>
					<input type='text' name='title' value='' placeholder='Skriv ämne här ...' class='full' />
					<h5>Meddelande</h5>
					<?php include('sales/bb.php'); ?>
					<textarea name='message' placeholder='Skriv meddelande här ...' id='message' class='minimize'></textarea>
					<?php if($g->page == 'admin') { ?>
					<input type='hidden' name='page' value='<?=$g->page;?>' />
					<div class='check'>
						<input type='checkbox' id='bill' name='board' value='Anslagstavlan' checked />
						<label for='bill'>Publisera på anslagstavlan</label>
					</div>
					<div class='check'>
						<input type='checkbox' id='board' name='email_board' value='Styrelsemedlemmar' />
						<label for='board'>Skicka e-post till styrelsemedlemmar</label>
					</div>
					<div class='check'>
						<input type='checkbox' id='all' name='email_all' value='Styrelsemedlemmar och föreningsmedlemmar' />
						<label for='all'>Skicka e-post till styrelsemedlemmar och föreningsmedlemmar</label>
					</div>
					<a class="submit radius mail" href="#">Skicka</a>
					<?php } else { ?>
					<a class="submit addnew radius pub" href="#">Publisera på anslagstavlan</a>
					<?php } ?>
					<div class='clear'></div>
				</form>
				</div>
			</div>
		</div>
		
		<div id='messages'>
		<?php
		$xt = $g->page != 'admin' ? " AND b_where LIKE 'Anslagstavlan%'" : '';
		$qry = $g->_query("SELECT * FROM #_board WHERE house_id = ? AND b_pub = 1{$xt} ORDER BY b_created_at DESC LIMIT 10", $house->h_id);
		while($res = $g->_object($qry)) {
			bbcode($res->b_message);
			?>
			<div class='message' data-id='<?=$res->b_id;?>'>
				<h5><?=$res->b_title;?><span><?=dtime($res->b_created_at, '{"opt":0,"sep":"-"}');?></span></h5>
				<?php if($g->page == 'admin' && !empty($res->b_where)) { ?>
				<p class="b_where"><?=$res->b_where;?></p>
				<?php } ?>
				<div class='mess'>			
					<?=$res->b_message;?>
				</div>
				<?php if($g->page == 'admin') { ?>
				<a href='#' class='submit remove radius message'>Ta bort inlägget</a>
				<?php } ?>
			</div>
			<?php
		}
		?>
		</div>
	</div>
	
	<?php if($side) { ?>
	<div class='side'>
		<?php
		$qry = $g->_query("SELECT b_title FROM #_board WHERE house_id = ? AND b_pub = 1{$xt} ORDER BY b_created_at DESC", $house->h_id);
		?>
		<h4>Rubriker</h4>
		<ul class='list'>
			<?php if($g->_count($qry) > 0) while($r = $g->_object($qry)) {
				?>
			<li>
				<b><a href='#' class='board'><?php echo $r->b_title; ?></a></b>
			</li>
			<?php } ?>
		</ul>
	<?php
	/* else {
			$side = $faq->get($g->v1);
			?>
		<h4>Vanliga frågor</h4>
		<ul class='list hide'>
		<?php foreach($side as $si) { bbcode($si['a']); ?>
			<li>
				<b><a href='#toggle'><?php echo $si['q']; ?></a></b>
				<?php echo $si['a']; ?>
			</li>
		<?php } ?>
		</ul>
			
			<?php
		} */
	?>
	</div>
	<?php
	}
}
?>