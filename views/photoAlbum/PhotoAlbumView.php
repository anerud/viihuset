<?php
require_once("interfaces/iView.php");
require_once("views/listview/ListView.php");
require_once("views/listview/LinkListItem.php");
require_once("views/listview/TextListItem.php");

class PhotoAlbumView implements iView{

	private $errors;
	private $brf;
	private $albums;
	private $moduleInfo;
	private $maxPage;

	public function __construct($moduleInfo,$albums,$maxPage,$errors){
		$this->brf = $moduleInfo->brf;
		$this->moduleInfo = $moduleInfo;
		$this->albums = $albums;
		$this->maxPage = $maxPage;
		$this->errors = $errors;
	}

	public function render(){

	?>

		<div class='content'>
			<div class='push'>
				<?php
				require_once("views/moduleInfo/ModuleInfoView.php");
				(new ModuleInfoView($this->moduleInfo))->render();
				?>
			</div>
			<div class='main'>
				<?php

                if($this->albums != null && count($this->albums) > 0){
                    $headers = array("Fotoalbum", "Datum");
                    $headerStyles = array("text-align: left", "text-align: right");
                    $rows = array();
                    foreach ($this->albums as $album) {
                        $values = array();

                        // Photo album
                        array_push(
                            $values,
                            new LinkListItem(
                                "/".$album->brf."/fotoalbum/".$album->id,
                                new TextListItem(
                                    $album->title,
                                    $headerStyles[0]
                                ),
                                false
                            )
                        );

                        // Date
                        $datetimearray = explode(" ", $album->posted);
                        $date = $datetimearray[0];
                        array_push(
                            $values,
                            new TextListItem(
                                $date,
                                $headerStyles[1]
                            )
                        );

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
                }

                ?>
			</div>
		</div>

		<?php
	}

}

?>
