<?php
require_once("interfaces/iView.php");
require_once("views/listview/LinkListItem.php");
require_once("views/listview/ImageListItem.php");
require_once("views/listview/TextListItem.php");
require_once("views/listview/ListView.php");

class DocumentView implements iView{

	private $maxPage;
	private $documents;
	private $moduleInfo;

	public function __construct($documents, $maxPage, $moduleInfo) {
		$this->maxPage = $maxPage;
		$this->documents = $documents;
		$this->moduleInfo = $moduleInfo;
	}

	public function render(){


		require_once("views/moduleInfo/ModuleInfoView.php");
		(new ModuleInfoView($this->moduleInfo))->render();


		if($this->documents != null && count($this->documents) > 0){
            echo "<div id='documentListView'>";

            $headers = array("filtyp","filnamn","datum");
            $headerStyles = array("text-align: left", "text-align: left", "text-align: right");

            $rows = array();
            foreach ($this->documents as $doc) {
                $values = array();

                array_push($values, new ImageListItem($doc->extension, $headerStyles[0]));

                $datetimearray = explode(" ", $doc->posted);
                $date = $datetimearray[0];

                array_push($values, new LinkListItem("/".$doc->brf."/dokument/".$doc->id, new TextListItem($doc->title, $headerStyles[1]), false));

                array_push($values, new TextListItem($date, $headerStyles[2]));

                $row = array( "values" => $values );
                array_push($rows,$row);
            }

            $listView = new ListView($headers, $rows, $headerStyles);
            $listView->render();

            $view = "PaginationView";
            require_once("views/".$view.".php");
            $viewObject = new $view($this->maxPage);
            $viewObject->render();

            echo "</div>";
        }

	}

}

?>
