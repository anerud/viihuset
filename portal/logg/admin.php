<?php
if($g->v1 == 'logg') {
	$qry = $g->_query("select * from #_logs order by log_time desc");
	?>
	<h1 class='<?php echo $admin[$g->v1]['class']; ?>'><?php echo $admin[$g->v1]['name']; ?></h1>
	<div class="con">
		<?php
		while($obj = $g->_object($qry)) {
			?>
		<div class="number">
			<b>[<?php echo dtime($obj->log_time, '{"opt":3, "time":1, "full":0}'); ?>]</b>
			<?php echo $obj->log_text; ?>
		</div>
		<?php } ?>
	</div>
	<?php
}
?>