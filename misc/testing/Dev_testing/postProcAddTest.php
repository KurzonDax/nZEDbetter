<?php
/**
 * Project: nZEDbetter
 * User: Randy
 * Date: 11/20/13
 * Time: 9:58 AM
 * File: postProcAddTest.php
 * 
 */
require(dirname(__FILE__) . "/config.php");
require_once(WWW_DIR . "/lib/framework/db.php");
require_once(WWW_DIR . "/lib/consoletools.php");
require_once(WWW_DIR . "/lib/postprocess.php");

$consoleTools = new ConsoleTools();

$releaseID = $consoleTools->getUserInput("\nEnter release ID to post process additional: ");

if(!is_numeric($releaseID) && $releaseID != '')
{
    echo "\nInvalid release ID entered.  Exiting script.\n";
    exit(1);
}
elseif($releaseID=='')
{
    echo "\nGuess you changed your mind.  Goodbye.\n";
    exit(0);
}
$postProcess = new PostProcess(true);
$postProcess->processAdditional('', $releaseID);
exit(0);