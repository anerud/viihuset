<?php
	
	$check = $house && $g->page != 'admin' ? 'house' : $g->page;
	
	
	$salesurl = sprintf("sales/%s.php", $check);

	
	if(file_exists($salesurl)){
		include($salesurl);
	}else {
		$portalurl = sprintf("portal/%s/index.php", $check);
		if(file_exists($portalurl)){
			include($portalurl);
		}else {
			$g->send('/404?referens='.$g->currentPage());
		}
	}
	
?>