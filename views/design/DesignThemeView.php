<?php
require_once("interfaces/iView.php");

class DesignThemeView implements iView{

	private $activeDesignPattern;
	private $allDesignPatterns;
	private $errors;

	public function __construct($activeDesignPattern, $allDesignPatterns, $errors) {
		$this->activeDesignPattern = $activeDesignPattern;
		$this->allDesignPatterns = $allDesignPatterns;
		$this->errors = $errors;
	}

	public function render(){
		?>

        <style>
            #themecon{
                display: inline-block;
                -webkit-touch-callout: none; /* iOS Safari */
                -webkit-user-select: none;   /* Chrome/Safari/Opera */
                -khtml-user-select: none;    /* Konqueror */
                -moz-user-select: none;      /* Firefox */
                -ms-user-select: none;       /* IE/Edge */
                user-select: none;
            }

            #info{
                margin-top: 5px;
                display: inline-block;
                vertical-align: top;
            }

            #right,
            #left{
                background-color: #acacac;
                width: 20px;
                height: 20px;
                display: inline-block;
                vertical-align: top;
                padding: 0;
                margin:0;
                margin-top: 4px;
                cursor: pointer;
            }

            #left{
                border-radius: 3px  0 0 3px;
                color: #fff;
                padding-left: 7px;
            }

             #right{
                border-radius: 0 3px   3px 0;
                color: #fff;
                padding-left: 6px;
            }

            #title{
                text-align: center;
                height: 20px;
                width: 200px;
                border: solid 1px #acacac;
                background-color: #fff;
                display: inline-block;
                vertical-align: top;
                padding: 0;
                margin:0;
                margin-top: 4px;
                font-size: 14px;
                padding: 2px;
            }
            #loader{
                display: inline-block;
                padding-top: 12px;
                height: 27px;
            }

        </style>

        <h2>Färdiga designmallar</h2>
        <hr>
        <div id="themecon">
            <div id="info">Klicka på pilarna för att byta tema:</div>
            <div id="left">&laquo;</div><div id="title">None</div><div id="right">&raquo;</div>
        </div>
        <br>
        <br>
        <img style="width: 100%;" id="preview" src="/background/design-themes/aqua/preview.jpg"></img>
        <br>
        <br>
        <div id="savecon">
            <button id="save" class="button-blue">Spara</button>
            <div id="loader" style="display: none;"></div>
        </div>
		<script>
			 insertLoader($("#loader"));

			var designs = <?php  echo json_encode($this->allDesignPatterns); ?>;
			var active = <?php  echo json_encode($this->activeDesignPattern); ?>;
            var currentIndex = -1;

            if(active && active.id){
                for(var i = 0;i < designs.length; i++){
                    var d = designs[i];
                    if(d.id == active.id){
                        currentIndex = i;
                        active = designs[currentIndex];
                        break;
                    }
                }
            }

            if(currentIndex == -1 ){
                designs.unshift(active);
                currentIndex = 0;
            }

            setDesign(designs[currentIndex]);

            function setDesign(design){
                updateColor(1,"#"+design.color1);
                updateColor(2,"#"+design.color2);
                updateColor(3,"#"+design.color3);
                updateColor(4,"#"+design.color4);
				updateButtons("#"+design.color4);
                $(document.body).css("background-color","#"+design.backgroundColor);
                $(document.body).css("background-image","url("+design.backgroundPattern+")");
                $("#title").text(design.name);
                $("#preview").attr("src", design.preview);
            }



			 function updateColor(index, color){

				$(".textcolor"+index).each(function(){
					var t = $(this);
					var os = t.attr("style");
					os = os ? os : "";
					t.css('cssText', os + "color: "+color+" !important;");
				});

				$(".bgcolor"+index).each(function(){
					var t = $(this);
					var os = t.attr("style");
					os = os ? os : "";

					t.css('cssText', os + "background-color: "+color+" !important;");
				});

				$(".bordercolor"+index).each(function(){
					var t = $(this);
					var os = t.attr("style");
					os = os ? os : "";

					t.css('cssText', os + "border-color: "+color+" !important;");
				});

             };

			 function updateButtons(color){
				$(".button-blue").each(function(){
					var t = $(this);
					var os = t.attr("style");
					os = os ? os : "";

					t.css('cssText', os + "background-color: "+color+" !important;");
				});
			 }



            $("#right").click(function(){
                currentIndex++;
                if(currentIndex == designs.length){
                    currentIndex = 0;
                }

                var design = designs[currentIndex];
                setDesign(design);
            });

             $("#left").click(function(){
                currentIndex--;
                if(currentIndex == -1){
                    currentIndex = designs.length-1;
                }
                var design = designs[currentIndex];
                setDesign(design);
            });

            var saving = false;
             $("#save").click(function(){
                 if(!saving){
                    saving = true;
                    var design = designs[currentIndex];
                    $("#loader").show();
                    if(design != active){
                        $.post("/teman",{themeId : design.id }, function(){
                            $("#loader").hide();
                            saving = false;
                        });
                    }else{
                        $("#loader").hide();
                        saving = false;
                    }
                 }
            });

		</script>





		<?php
	}

}

?>
