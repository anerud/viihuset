<?php
require_once("interfaces/iView.php");

class PaginationView implements iView{
	
	private $maxPage;

	public function __construct($maxPage) {
		$this->maxPage = $maxPage;
	}

	public function render(){
			$current =  isset($_GET['page']) ? $_GET['page'] : 1;
			
            if($this->maxPage <= 1) {
                return;
            }
            
			?>
			<nav role="navigation">
				<ul class="cd-pagination no-space textcolor4">
					<?php
						if($current > 1+ 2){
							echo '<li class="button left double"><a class="textcolor4 bgcolor1" href="?page=1">First</a></li>';					
						}
					
						if($current > 1){
							echo '<li class="button left"><a class="textcolor4 bgcolor1" href="?page='.($current-1).'">Prev</a></li>';					
						}
				
						$start = max(1, $current-2);
						$end = min($this->maxPage, $current+2);
						
						if($start > 1){
							echo '<li><span class="textcolor4 bgcolor1">...</span></li>';
						}
						
						for($i = $start; $i<=$end;$i++){
							$tempArr = $_GET;
							$tempArr["page"] = $i;
							$newPageQuery = http_build_query($tempArr);							
							echo '<li><a class="'.($current == $i ? "bgcolor3 textcolor1 " : " bgcolor1 textcolor4 ").($start == $end ? " only " : "").'" href="?'.$newPageQuery.'">'.$i.'</a></li>';					
						}	
						if($end < $this->maxPage){
							echo '<li><span class="textcolor4 bgcolor1">...</span></li>';
						}
						
						if($current < $this->maxPage){
							echo '<li class="button right"><a class="textcolor4 bgcolor1" href="?page='.($current+1).'">Next</a></li>';		
						}
						
						if($current < $this->maxPage - 2){
							echo '<li class="button right double"><a class="textcolor4 bgcolor1" href="?page='.($this->maxPage).'">Last</a></li>';		
						}
					?>
				</ul>
			</nav> 
			
		<?php
	}
	
}

?>