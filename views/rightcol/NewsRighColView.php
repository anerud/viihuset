<?php
require_once("interfaces/iView.php");
require_once("views/listview/ListView.php");
require_once("views/listview/TextListItem.php");
require_once("views/listview/TextListItemColor4.php");
require_once("views/listview/LinkListItem.php");
require_once("views/listview/TitleSubtitleListItem.php");

class NewsRighColView implements iView{

    private $module;
	private $threads;
	private $brf;
	public function __construct($threads,$brf, $module) {
        $this->module = $module;
		$this->threads = $threads;
		$this->brf = $brf;
	}


	public function render(){
		?>
		<div class="messageBoardDiv rightcolContainer rightModule">

			<div class="rightcolHeader messageBoardHeader bgcolor2">
                <div class='columnarrows'>
                        <div class='columnup' data-module='<?php echo $this->module;  ?>'></div>
                        <div class='columnarrowspace'></div>
                        <div class='columndown' data-module='<?php echo $this->module;  ?>'></div>
                    </div>
                    <div class="title">
				<div class="messageBoardTitle textcolor0">Nyheter</div>
                </div>
			</div>

			<div class="messageBoardBorder rightcolBorder bgcolor4">
				<div class="messageBoardBody rightcolBody bgcolor4">
					<?php
							$rows = array();

							if (count($this->threads) <= 0 ) {
								$values = array();
								array_push($values, new TextListItem("Inga nya inlägg", null));
								$row = array( "values" => $values );
								array_push($rows,$row);
							}

							foreach ($this->threads as $thread) {
								$values = array();

								$preurl = $this->brf == null ? "" : "/".$this->brf;

								array_push($values, new LinkListItem($preurl."/nyheter/".$thread->id, new TextListItemColor4($thread->title, null), false));

								$row = array( "values" => $values );
								array_push($rows,$row);
							}


							$listView = new ListView(null, $rows, null);
							$listView->render();



						?>
				</div>
			</div>
		</div>
		<?php
	}

}

?>
