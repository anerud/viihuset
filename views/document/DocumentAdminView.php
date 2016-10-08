<?php
require_once("interfaces/iView.php");
require_once("views/listview/ListView.php");
require_once("views/listview/TextListItem.php");
require_once("views/listview/ImageListItem.php");
require_once("views/listview/JSDeleteListItem.php");
require_once("views/listview/LinkListItem.php");
require_once("views/listview/TitleSubtitleListItem.php");
require_once("views/listview/JSCheckboxListItem.php");
require_once("views/listview/JSDropdownListItem.php");
require_once("views/moduleInfo/ModuleInfoAdminView.php");

class DocumentAdminView implements iView{

	private $maxPage;
	private $errors;
	private $documents;
	private $moduleInfo;

	public function __construct($documents, $maxPage, $errors, $moduleInfo) {
		$this->maxPage = $maxPage;
		$this->documents = $documents;
		$this->errors = $errors;
		$this->moduleInfo = $moduleInfo;
	}

	public function render(){

			$moduleView = new ModuleInfoAdminView($this->moduleInfo);
			$moduleView->render();
		?>

		<?php if($this->errors && sizeof($this->errors) > 0){errors($this->errors);}

		echo '<a id="uploadDocumentFormLink" class="button-blue" href="#">Ladda upp dokument</a>';
		echo '<script>'	;
		echo '$("#uploadDocumentFormLink").click(
                function(){
                    $(this).hide();
                    $("#moduleInfoAdminView").hide();
                    $("#uploadDocumentForm").show();
                    $(document).scrollTop(0);
                }
            );';
		echo '</script>';

		?>

		<form id='uploadDocumentForm' style="display:none;" class='form' action="/dokument" method="post" enctype="multipart/form-data">
			<input type="text" name="fileName" id="fileName" class="input-message" placeholder='Titel på dokument'>
    		<br>
            <br>
            <input type="file" name="fileToUpload" id="fileToUpload">
            <br>
            <br>
			<span class='textBold' style="font-size: 14px">Lösenordsnivå för att visa dokument: </span>
			<br>
			<select name="documentUserLevel">
				<?php
				foreach(UserLevels::$userLevels as $key => $val){
					if($val >= UserLevels::$userLevels["brf"] && $val <= UserLevels::$userLevels["admin"]) {
						echo '<option value="'.$key.'" '.($key == $this->moduleInfo->userlevel ? 'selected' : '').'>'.UserLevels::$namesToDescription[$key].'</option>';
					}
				}
				?>
			</select>
			<br>
			<br>
    		<input type="submit" class="button-blue" value="LADDA UPP" name="submit">
		</form>


		<?php

        if($this->documents != null && count($this->documents) > 0){
            echo "<hr/>";

            $headers = array("visa","filtyp","filnamn","behörigheter","radera");
            $headerStyles = array("text-align: left", "text-align: left", "text-align: left", "text-align: right", "text-align: right");

            $rows = array();
            foreach ($this->documents as $doc) {
                $values = array();
                array_push($values, new JSCheckboxListItem("/dokument/toggleVisibility/".$doc->id,$doc->visible, $headerStyles[0]));

                array_push($values, new ImageListItem($doc->extension, $headerStyles[1]));

                array_push($values, new LinkListItem($doc->brf."/dokument/".$doc->id ,new TitleSubtitleListItem($doc->title,$doc->posted, $headerStyles[2]), false));

                array_push($values, new JSDropdownListItem("/dokument/changeLevel/".$doc->id, UserLevels::$namesToDescription, $doc->userlevel, $headerStyles[3]));

                array_push($values, new JSDeleteListItem("/dokument/".$doc->id, $headerStyles[4]));

                $row = array( "values" => $values );
                array_push($rows,$row);
            }

            $listView = new ListView($headers, $rows, $headerStyles);
            $listView->render();
        }

		$view = "PaginationView";
		require_once("views/".$view.".php");
		$viewObject = new $view($this->maxPage);
		$viewObject->render();

        echo "<br>";
	}

}

?>
