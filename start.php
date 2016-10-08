<?php

require_once('classes/auth.class.php');
$auth = new Auth;
$uInfo = $auth->user();

require_once('classes/faq.class.php');
$faq = new FAQ;

$pages = array('auth' => 'Logga in'
			  // 'skapa' => 'Skapa konto'
			   );
$_404 = false;
$txts = json_decode($g->set['site_texts']);

// Adminsidor för huset
$admin = array('basinformation' => array('name' => 'Basinformation', 'class' => 'bas'),
			   'hem' => array('name' => 'Hem', 'class' => 'hem'),
			   'omforeningen' => array('name' => 'Om föreningen', 'class' => 'forening'),
			   'omgivningen' => array('name' => 'Omgivningen', 'class' => 'omgivning'),
			   'maklarinfo' => array('name' => 'Info till mäklaren', 'class' => 'maklarinfo'),
			   'fotoalbum' => array('name' => 'Fotoalbum', 'class' => 'fotoalbum'),
			   'anslagstavlan' => array('name' => 'Anslagstavlan', 'class' => 'anslagstavlan'),
			   #'kontroll' => array('name' => 'Skapa användare', 'class' => 'bas', 'rank' => 4),
			   'anvandarlista' => array('name' => 'Användarlista', 'class' => 'bas', 'rank' => 4),
			   'texter' => array('name' => 'Allmänna texter', 'class' => 'forening', 'rank' => 4),
			   'vanligafragor' => array('name' => 'Vanliga frågor', 'class' => 'forening', 'rank' => 4),
			   'bilder' => array('name' => 'Föreningsbilder', 'class' => 'fotoalbum', 'rank' => 4),
			   'anvandaravtal' => array('name' => 'Sekretess &amp; avtal', 'class' => 'bas', 'rank' => 4),
			   'logg' => array('name' => 'Loggar', 'class' => 'forening', 'rank' => 5)
			   );

// Undersidor till huset
$menu  = array('hem' => array('name' => 'Hem', 'class' => 'hem'),
			   'omforeningen' => array('name' => 'Om föreningen', 'class' => 'forening'),
			   'omgivningen' => array('name' => 'Omgivningen', 'class' => 'omgivning'),
			   'maklarinfo' => array('name' => 'Info till mäklaren', 'class' => 'maklarinfo'),
			   'fotoalbum' => array('name' => 'Fotoalbum', 'class' => 'fotoalbum'),
			   'anslagstavlan' => array('name' => 'Anslagstavlan', 'class' => 'anslagstavlan'),
			   'kontakt' => array('name' => 'Kontakta oss', 'class' => 'kontakt')
			   );

$vih    = array('start' => array('name' => 'Hem', 'class' => 'hem'),
				'kontakt' => array('name' => 'Kontakta oss', 'class' => 'kontakt'),
				'anvandaravtal' => array('name' => 'Sekretess &amp; avtal', 'class' => 'bas')
				);

	require_once('classes/houses.class.php');
	$houses = new Houses();
	$house  = $houses->info;

$revisionMenu = $houses->menu($menu);

// Quickfix for admin splash
if($g->page == 'admin' && empty($g->v1)){
	$g->v1 = HOUSE_ID != 0 ? 'basinformation' : 'anvandarlista';
}
if($house && empty($g->v1)) list($g->v1) = array_keys($revisionMenu);

// Corporate pages
$corp = in_array($g->page, array_keys($vih)) && $g->page != 'anvandaravtal' ? ' corp' : '';
/*
$copy = empty($corp)
			? sprintf("<a href='%s' class='home'>Gå till Vi i huset.se</a>", $g->href())
			: sprintf("<a href='%s' class='home'>Copyright &copy; 2013 Vi i huset.se</a>", $g->href());
*/
$copy = sprintf("<a href='%s' class='home'>Copyright &copy; %d Vi i huset.se</a>", $g->href(), date('Y'));
$logoLink = defined('HOUSE_ID') && $house ? $g->href($house->h_perma) : $g->href();
?>
<!doctype html>
<html>
<!--
         -~~~~~~~~~$$$$$$
         ~~~~~~~~~.$$$**$$
         ~~~~~~~~~$$$"~~`$$
         ~~~~~~~~$$$"~~~~$$
         ~~~~~~~~$$$~~~~.$$
         ~~~~~~~~$$~~~~..$$
         ~~~~~~~~$$~~~~.$$$
         ~~~~~~~~$$~~~$$$$
         ~~~~~~~~~$$$$$$$$
         ~~~~~~~~~$$$$$$$
         ~~~~~~~.$$$$$$*
         ~~~~~~$$$$$$$"
         ~~~~.$$$$$$$
         ~~~$$$$$$"`$
         ~$$$$$~~~~~$$.$..
         $$$$$~~~~$$$$$$$$$$.
         $$$$~~~.$$$$$$$$$$$$$
         $$$~~~~$$$*~`$~~$*$$$$   ******************************
         $$$~~~`$$"~~~$$~~~$$$$   This site is made by Peter
         3$$~~~~$$~~~~$$~~~~$$$   Ljunggren with motivation
         ~$$$~~~$$$~~~`$~~~~$$$   from his piano improvisations
         ~`*$$~~~~$$$~~$$~~$      ******************************
         ~~~$$$$~~~~~~~$$~$$"
         ~~~~~$$*$$$$$$$$$"
         ~~~~~~~~~~````~$$
         ~~~~~~~~~~~~~~~`$
         ~~~~~~~~..~~~~~~$$
         ~~~~~~$$$$$$~~~~$$
         ~~~~~$$$$$$$$~~~$$
         ~~~~~$$$$$$$$~~~$$
         ~~~~~~$$$$$"~~.$$
         ~~~~~~~"*$$$$$$
-->
<head>
	<meta charset='utf-8' />
	<title><?php title(); ?></title>
	<link rel='shortcut icon' href='<?php echo "/favicon.ico"; ?>?v=2' type='image/x-icon' />
	<link rel='stylesheet' href='<?php echo '/css/shadowbox.css'; ?>?v=1' />
	<link rel='stylesheet' href='<?php echo '/css/style.css'; ?>?v=2' />
	<meta name='description' content='<?php echo $g->m; ?>' />
	<meta name="robots" content="<?php echo $g->f; ?>" />
	<meta name="author" content="Peter Ljunggren" />
	<meta name="SKYPE_TOOLBAR" content="SKYPE_TOOLBAR_PARSER_COMPATIBLE" />
	<?php if($house && $g->v1 == 'kontakt') { ?>
	<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCijUlbBHOANZZ2KkxuuRlgNyAXWmndA9E&sensor=false"></script>
	<?php if(!empty($house->h_address)) { ?>
	<script>address = "<?php printf("%s, %s %s", $house->h_address, $house->h_postal, $house->h_town); ?>";</script>
	<?php } } ?>
	<script>c_http = "<?php echo HTTP; ?>", c_ver = "<?php echo $g->set['site_version']; ?>";</script>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head>
<body>
	<div class='wrap<?php echo $corp; if($g->page == 'admin') echo ' admin'; ?>'>
		<?php if(empty($corp)) { ?>
		<div class='sidebar'>
			<div class='logo'>
				<a href='<?php echo $logoLink; ?>'><img src='/gfx/brfLogo.png' alt='Vi i Huset.se' /></a>
			</div>
			<?php sideNav(); ?>
		</div>
		<?php } ?>
		
		<?php include('modules.php'); ?>
		<div class='clear'></div>
		
		<div class='footer'>
        	<div class='enclose'>
                <?php echo $copy; ?>
                <ul class='nav'>
				<?php
				$footer = $g->page == 'admin' ? $admin :
					(empty($corp) && $house ? $revisionMenu : $vih);
				
				foreach($footer as $ak => $av) {
					if( (isset($av['rank']) && defined('USER_ID') && USER_RANK < $av['rank'])
						|| (!isset($av['rank']) && defined('USER_ID') && USER_RANK > 3 && $g->page == 'admin')) continue;
					
					if($ak == 'start') $ak = '';
					$href = !empty($corp) || $g->page == 'anvandaravtal' || $g->page == '404' ? $g->href($ak) : $g->href($g->page, $ak);
					?>
					<li><a href='<?php echo $href; ?>' class='link <?php echo $av['class']; ?>'><?php echo $av['name']; ?></a></li>
					<?php
				}
				?>
                </ul>
			</div>
        </div>
	</div>
    
	<script src='<?php echo '/js/jquery.js'; ?>'></script>
	<script src='<?php echo '/js/plugins.js'; ?>'></script>
	<script src='<?php echo '/js/globals.js'; ?>'></script>
    
	<?php if($_SERVER['SERVER_NAME'] != 'localhost') { ?>
	<script>
	(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
	(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
	m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
	})(window,document,'script','//www.google-analytics.com/analytics.js','ga');
	
	ga('create', 'UA-43749475-1', 'viihuset.se');
	ga('send', 'pageview');
	</script>
	<?php } ?>
</body>
</html>