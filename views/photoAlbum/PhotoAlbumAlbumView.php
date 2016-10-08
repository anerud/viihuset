<?php
require_once("interfaces/iView.php");

class PhotoAlbumAlbumView implements iView{
	
	private $errors;
	
	public function __construct($album,$errors){
		$this->errors = $errors;
		$this->album = $album;
	}

	public function render(){
	
	?>
	
		<div class='content'>
			<div class='main'>
				<?php
                
                echo "<h2>".$this->album->title."</h2>";
                echo "<hr>";
                echo "<span>".$this->album->description."</span>";
                echo "<hr>";

                ?>
                
                <style>
                    .alternatives{
                        margin-top: 10px;
                        clear: both;
                    }
                    
                    .alternative{
                        height: 130px;
                        width: 130px;
                        float: left;
                        cursor: pointer;
                    }
                </style>
                
                <div class="alternatives">
                    <?php 
                    
                        foreach($this->album->images as $image){
                            echo "<a target='_blank' href='/".$image->filepath."'><div data-id='".$image->id."' class='alternative' style='background-image:url(/".$image->thumb.")'></div></a>";
                        }
                    ?>
                </div>  
			</div>
		</div>
		
		<?php
	}
	
}

?>