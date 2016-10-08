<?php
require_once("interfaces/iView.php");
require_once("views/listview/ListView.php");
require_once("views/listview/TextListItem.php");
require_once("views/listview/JSDeleteListItem.php");
require_once("views/listview/LinkListItem.php");
require_once("views/listview/TitleSubtitleListItem.php");
require_once("views/listview/JSCheckboxListItem.php");
require_once("views/listview/JSDropdownListItem.php");
require_once("views/moduleInfo/ModuleInfoAdminView.php");

class PhotoAlbumAdminView implements iView{
	
	private $maxPage;
	private $errors;
	private $albums;
	private $moduleInfo;

	public function __construct($currentAlbum, $albums, $maxPage, $errors,$moduleInfo) {
		$this->currentAlbum = $currentAlbum;
		$this->maxPage = $maxPage;
		$this->albums = $albums;
		$this->errors = $errors;
		$this->moduleInfo = $moduleInfo;
	}

	public function render(){
		
		if($this->currentAlbum->id == null){
			$moduleView = new ModuleInfoAdminView($this->moduleInfo);
			$moduleView->render();
		}
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
                    
                    .remove{
                        color:red;
                        float: left;
                        margin-left: -35px;
                        margin-top: 5px;
                        background-color: rgba(255,255,255,0.4);
                        border-radius: 5px;
                        padding: 5px 10px;  
                        border: 1px solid #3c3c3c;
                    }
                    
                    .remove:hover{
                        background-color: rgba(255,255,255,0.8);
                    }
                </style>
       
                
             
		
		<?php if($this->errors && sizeof($this->errors) > 0){errors($this->errors);} ?>
		
		<?php 
		if($this->currentAlbum->id == null){			
			echo '<a id="createNewPhotoAlbumLink" class="button-blue" href="#">Skapa nytt fotoalbum</a>';			
			echo '<script>'	;	
			echo '$("#createNewPhotoAlbumLink").click(
                    function(){
                        $(this).hide();
                        $("#moduleInfoAdminView").hide();
                        $("#createNewPhotoAlbumForm").show();
                        $(document).scrollTop(0);
                    }
                );';
			echo '</script>';		
		}
		?>
		
		<form id="createNewPhotoAlbumForm" <?php if($this->currentAlbum->id == null){echo 'style="display:none;"'; } ?> method="post" enctype="multipart/form-data">
			<input type="text" name="title" class="input-title" value='<?php echo $this->currentAlbum->title;?>' placeholder='Titel på album'>
			<br><br>
			<textarea name="description" placeholder='Beskrivning av album'><?php echo $this->currentAlbum->description;?></textarea>
			<br>
			<input type="file" name="fileToUpload" id="fileToUpload">
			<br>
    		<input type="submit" class="button-blue" value='<?php echo ($this->currentAlbum->id == null ? "LADDA UPP" : "SPARA")?>' name="submit">
		</form>
			
		<?php
        
        //In basic view and showing all albums 
		if($this->currentAlbum->id == null && count($this->albums) > 0){
            
            echo '<hr>';
            
			$headers = array("visa","Albumtitel","behörigheter","radera");
            $headerStyles = array("text-align: left", "text-align: left", "text-align: right", "text-align: right");
			$rows = array();
			foreach ($this->albums as $album) {
				$values = array();
                
				array_push($values, new JSCheckboxListItem("/fotoalbum/toggleVisibility/".$album->id, $album->visible, $headerStyles[0]));
                
                $datetimearray = explode(" ", $album->posted);
                $date = $datetimearray[0];
				array_push($values, new LinkListItem("/fotoalbum/".$album->id ,new TitleSubtitleListItem($album->title, $date, $headerStyles[1]),false));
                
				array_push($values, new JSDropdownListItem("/fotoalbum/changeLevel/".$album->id, UserLevels::$namesToDescription,$album->userlevel, $headerStyles[2]));
				
				array_push($values, new JSDeleteListItem("/fotoalbum/".$album->id, $headerStyles[3]));
				
				$row = array( "values" => $values );
				array_push($rows,$row);
			}
			
			
			$listView = new ListView($headers, $rows, $headerStyles);
			$listView->render();
			
			$view = "PaginationView";
			require_once("views/".$view.".php");
			$viewObject = new $view($this->maxPage);
			$viewObject->render();
            
            echo "<br>";
            return;
		}
		
        //In a photoalbum and showing images
		if($this->currentAlbum->id != null && $this->currentAlbum->images != null && count($this->currentAlbum->images) > 0) {
                ?>
               <div class="alternatives">
                    <?php 
                    
                        foreach($this->currentAlbum->images as $image){
				        $link = "/fotoalbum/".$this->currentAlbum->id."/".$image->id; 
                            echo "<a target='_blank' href='/".$image->filepath."'><div data-id='".$image->id."' class='alternative' style='background-image:url(/".$image->thumb.")'></div></a><a href='".$link."' class='remove'>X</a>";
                        }
                    ?>
                </div>  
                 
                <script>
                    $(".remove").click(function(e){
                        e.preventDefault();
                        
                        var t = $(this);
                        var img = t.prev();
                        
                        img.remove();
                        t.remove();
                        
                      	
                        
                        $.ajax({
                            url: t.attr("href"),
                            type: 'DELETE',
                            success: function(result) {
                            }
                        });
                        
                    });
                </script>
			<?php 
            return;	
		}

	}
	
}

?>