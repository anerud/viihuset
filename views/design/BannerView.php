<?php
require_once("interfaces/iView.php");

class BannerView implements iView{

	private $errors;
	private $banner;

	public function __construct($banner, $errors){
		$this->banner = $banner;
		$this->errors = $errors;
	}

	public function render(){

		?>
        <link rel='stylesheet' href="/css/semantic.css" />
        <link rel='stylesheet' href="/css/dropdown.css" />
        <link rel='stylesheet' href="/css/spectrum.css" />
        <script src="/js/spectrum.js"></script>
        <script src="/js/semantic.js"></script>
        <script src="/js/dropdown.js"></script>

		<form method='post' class='form' autocomplete='off' enctype="multipart/form-data">


            <div class="form-section">
                <div class="form-input-whole">
                    <div class="text-title">Sidhuvud</div>
					<input type='text' hidden=true name='bannerLink' value='<?php echo $this->banner->bannerLink;?>' class="form-input-basic">
					<input type="file" name="imageToUpload" id="imageToUpload">
                </div>
				<hr>

                <p class="text-title">Text i sidhuvud</p>

				<hr>

                 <div class="form-input-third">
                    <h5 class='form-description'>Rubrik</h5>
                   <input type='text' name='bannerText' value='<?php echo $this->banner->bannerText;?>' class="form-input-basic">
		        </div>
                 <div class="form-input-sixths">
                    <h5 class='form-description'>Font</h5>
                    <select name='font' class="form-input-basic-select">
                        <option value='helvetica' <?php echo $this->banner->font == "helvetica" ? "selected" : ""?>>Helvetica</option>
                        <option value='roboto' <?php echo $this->banner->font == "roboto" ? "selected" : ""?>>Roboto</option>
                    </select>
		        </div>
                 <div class="form-input-sixths">
                    <h5 class='form-description'>Textstorlek</h5>
                        <select name='font-size' class="form-input-basic-select">
						<?php
							for ($x = 12; $x <= 92; $x=$x+4) {
								$selected = $this->banner->fontSize == $x ? "selected" : "";
                            echo "<option value='".$x."' ".$selected.">".$x."px</option>";
							}
					 	?>
                        </select>
                </div>

            </div>


            <table width="100%">
                <tr>

                    <td colspan="1">

                    </td>
                </tr>
                <tr>
                    <td width="15%">
                        <span class="textcolorGray" style="font-weight: 500;">Välj värg på text</span>
                    </td>
                    <td width="8%">
                        <input id="textColor" type = "hidden" name="textColor" value='<?php echo $this->banner->textColor; ?>'>
                    </td>
                    <td width="1%">
                        <input type='checkbox' name='shadow' <?php echo $this->banner->shadow == true ? "checked" : "" ?> />
                    </td>
                    <td width="10%">
                        <span class="textUppercase textcolorDarkGray form-description">Skugga</span>
                    </td>
                    <td width="20%">
                        <span class="textcolorGray" style="font-weight: 500;">Placering av text</span>
                    </td>
                    <td width="20%">
                        <select name='text-align' class="form-input-basic-select">
                            <option value='left' <?php echo $this->banner->textAlign == "left" ? "selected" : ""?>>Vänster</option>
                            <option value='center' <?php echo $this->banner->textAlign == "center" ? "selected" : ""?>>Mitten</option>
                            <option value='right' <?php echo $this->banner->textAlign == "right" ? "selected" : ""?>>Höger</option>
                        </select>
                    </td>
                </tr>

            </table>

			<br>
        	<p class="text-title">Bredd på sidhuvud</p>
			<hr>

			<input type=radio name="max_width" value=0 <?php echo $this->banner->max_width  == 0 ? "checked=\"checked\"" : "";?>>
				Standard bredd
			</input>
			<img src='/gfx/banner_half.jpg'>

			<input type=radio name="max_width" value=1 <?php echo $this->banner->max_width  == 1 ? "checked=\"checked\"" : "";?>>
				Anpassad till max skärmbredd
			</input>
			<img src='/gfx/banner_max.jpg'>

			<br>
			<br>
			<br>

            <div id="savecon">
                <button id="save" class="button-blue">Spara</button>
                <div id="loader" style="display: none;"></div>
            </div>
			<input hidden=true id="submitButton" type='submit' name='submit' value='Spara' />
			<script>
	            $("#save").click(
					function(){
                        $("#textColor").val($("#textColor").val());
						$("#submitButton").submit();
                	}
				);


                $("#textColor").spectrum({
                    color: $("#textColor").val(),
                    preferredFormat: "hex",
                    showButtons:false,
                    move: function(e){
                        e = e.toHexString();
                        newBgColor = e;
                        $(".headertitle").style("color", e, "important");
                    }
                });
			</script>
		</form>

		<?php

	}

}

?>
