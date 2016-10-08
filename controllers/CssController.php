<?php
require_once("interfaces/iController.php");

final class CssController implements iController{

	private $dbContext;

	public function init($router, $dbContext){
		$this->dbContext = $dbContext;

		//Brf page
		$router->map('GET', '/css/site/[i:level]/[a:brf]?',
			function($level, $brf) {

				$banner = null;
				$design = null;
				$bg = "/background/design-themes/default/header.jpg";
				$bannerHeight = "250px";
				if($brf != null && $brf != "null" && $level > 0){
					$design = $this->dbContext->getActiveDesignPatternByBrf($brf);
					$banner = $this->dbContext->getBanner($brf);
					if ($banner != null) {
						$bannerHeight = $banner->max_width ? "300px" : "250px";
						if($banner->bannerLink != null && strlen($banner->bannerLink) > 5){
							$bg = $banner->bannerLink;
						}
					}
				}

				if ($design == null) {
					$design = $this->dbContext->getDefaultDesignPattern();
				}

				header('Content-Type: text/css');
				$myfile = fopen("css/newstyle.css", "r") or die("Unable to open file!");
				$css =  fread($myfile,filesize("css/newstyle.css"));

				$css =
				"

				.bgcolor0{
					background-color: #fff !important;
				}

				.bgcolor1{
					background-color: #".$design->color1." !important;
				}

                .bgcolor1.hoverbg:hover{
					background-color: #".$design->color3." !important;
				}


				.bgcolor2{
					background-color: #".$design->color2." !important;
				}

                .bgcolor2.hoverbg:hover{
                    background-color: #".$design->color3." !important;
                }

				.bgcolor3{
					background-color: #".$design->color2." !important;
				}

                .bgcolor3.hoverbg:hover{
					background-color: #".$design->color4." !important;
				}


                .bgcolor4{
					background-color: #".$design->color4." !important;
				}

				// tr:nth-child(even){
				// 	background-color: #fff !important;
				// }

				.textcolor0{
					color: #000 !important;
				}

				.textcolor1{
					color: #".$design->color1." !important;
				}

				.textcolor2{
					color: #".$design->color2." !important;
				}

				.textcolor3{
					color: #".$design->color3." !important;
				}

                .textcolor4{
					color: #".$design->color4." !important;
				}

				.textcolor5{
					color: #fff !important;
				}

				.bordercolor1{
					border-color: #".$design->color1." !important;
				}

				.bordercolor2{
					border-color: #".$design->color2." !important;
				}

				.bordercolor3{
					border-color: #".$design->color3." !important;
				}

                .bordercolor4{
					border-color: #".$design->color4." !important;
				}

				body{
					background-color: #".$design->backgroundColor.";
					background-image: url('".$design->backgroundPattern."');
				}

				.header{
					background-image: url('".$bg."') !important;
					height:".$bannerHeight." !important
				}

                .button-blue{
					background-color: #".$design->color4." !important;
				}

				".$css;

                //If banner was found set shadow and color
                if($banner != null) {
                    $bannerShadow = $banner->shadow ? "text-shadow: 2px 2px 2px #101010 !important;" : "";
                    $css = ".headertitle{
                    color: #".$banner->textColor." !important;
					text-align: ".$banner->textAlign." !important;
					font-size: ".$banner->fontSize."px !important;
					font-family: '".$banner->font."', sans-serif;
                    ".$bannerShadow."
                    }".$css;
                }

				echo $css;

				fclose($myfile);
			},
			NULL,
			function(){return 0;},
			"",
			false
		);

	}

    public function getLinksByLevel($level, $endpointLevel, $brf, $domain){
		return null;
	}

}

?>
