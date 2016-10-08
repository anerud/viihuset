<?php
class AppConfig{

	public $cfg;

	public function __construct() {

		$this->cfg = array(

		/* General */
		'gcharset' => 'utf-8',
		'ghttp' => '/' ,// Folder placement of the project
		'gtimezone' => 'Europe/Stockholm', // Default timezone
		'gport' => false, // Whether or not to include port with server name

		/* SEO */
		'seokeys' => '',
		'seodesc' => 'Föreningens ljuva portal',
		'seoflw' => 'all' ,// meta follow

		/* Database */
		'dbconn' => true ,// whether or not to connnect to database
		'dbprefix' => 'vih_' ,// prefix for database tables
		'dbnames' => 'utf8', // set names for sql
		'dbtype' => 'mysql', // database type

		'dbhost' => 'localhost',
		'dbuser' => 'root',
		'dbpass' => 'vi123huset',
		'dbdata' => 'viihuset_se',

		'constsalt' => 'vi2ikhus13et', // Password salt

		/* Mailgun */
		'mg_key' => 'key-bb944411d9bdbf95a4b3c24f9bacdcd3',
		'mg_domain' => 'viihuset.se'

		);
	}

}
?>
