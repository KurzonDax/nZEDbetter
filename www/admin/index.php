<?php

require_once("config.php");
require_once(WWW_DIR."/lib/adminpage.php");

$page = new AdminPage();

$page->title = "Admin Home Page";
$page->content = $page->smarty->fetch('index.tpl');
$page->render();

?>
