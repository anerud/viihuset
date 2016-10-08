<?php
require_once("interfaces/iView.php");

class ListView implements iView{
	
	private $headers;
	private $rows;
    private $headerStyles;

	public function __construct($headers, $rows, $headerStyles) {
		$this->headers = $headers;
		$this->rows = $rows;
        $this->headerStyles = $headerStyles;
	}

	public function render(){
			?>
			<table class="listView">
				<thead>
					<?php
						if($this->headers != null){
							for ($i = 0; $i < count($this->headers); $i++) {
                                $head = $this->headers[$i];
                                $style = $this->headerStyles != null
                                    && count($this->headerStyles) > $i 
                                    && $this->headerStyles[$i] != null 
                                    ? " style=\"".$this->headerStyles[$i]."\"" 
                                    : "";
								echo "<th class='textcolorGray'".$style.">".$head."</th>";	
							}
						}
					?>
				</thead>
				<tbody>
					<?php
						foreach($this->rows as $row){
							echo "<tr class='bgcolor1'>";
							foreach($row["values"] as $value){
								echo "<td>";
								$value->render();
								echo "</td>";
							}
							echo "</tr>";	
						}
					?>
				</tbody>
			</table>
		<?php
	}
	
}

?>