<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Randy
 * Date: 8/18/13
 * Time: 4:17 AM
 * To change this template use File | Settings | File Templates.
 */

require(dirname(__FILE__) . "/config.php");
// require_once(WWW_DIR . "/lib/binaries.php");
// require_once(WWW_DIR . "/lib/groups.php");
require_once(WWW_DIR . "/lib/framework/db.php");
require_once(WWW_DIR . "/lib/consoletools.php");
// require_once(WWW_DIR . "/lib/site.php");
// require_once(WWW_DIR . "/lib/category.php");
// require_once(WWW_DIR . "/lib/groups.php");
// require_once(WWW_DIR . "/lib/movie.php");
// require_once(WWW_DIR."/lib/tmdb.php");
require_once(WWW_DIR . "/lib/namecleaning.php");
// require_once(WWW_DIR . "/lib/trakttv.php");

$consoletools = new ConsoleTools();
$namecleaner = new nameCleaning();
$stop = false;
do
{
    $inputText = $consoletools->getUserInput("Enter text to clean: ");
    echo "\n".$namecleaner->musicCleaner($inputText, true);
    //echo "\n".$namecleaner->removeDoubleName($inputText)."\n\n";
    unset($inputText);
} while ($stop != true);

exit ("\nAll done...\n");

function checkCleanName()
{
    // $db = new DB();
    $consoletools = new ConsoleTools();
    $namecleaner = new nameCleaning();
    $textToClean = $consoletools->getUserInput("Enter text to clean: ");
    $cleanName = $namecleaner->bookCleaner($textToClean);
    // echo "\n".$db->escapeString($cleanName)."\n";
}