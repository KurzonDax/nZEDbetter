<?php
require("../../../../www/config.php");
require_once(WWW_DIR."lib/namecleaning.php");


	echo "Please input a name now.\n";
	$name = trim(fgets(fopen("php://stdin","r")));
	$namecleaner = new NameCleaning();
	echo $namecleaner->releaseCleaner($name)."\n";


?>
