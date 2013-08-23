<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Randy
 * Date: 8/18/13
 * Time: 4:17 AM
 * To change this template use File | Settings | File Templates.
 */

require(dirname(__FILE__)."/config.php");
require_once(WWW_DIR."/lib/binaries.php");
require_once(WWW_DIR."/lib/groups.php");
require_once(WWW_DIR."/lib/framework/db.php");
require_once(WWW_DIR."/lib/consoletools.php");
require_once(WWW_DIR."/lib/site.php");
require_once(WWW_DIR."/lib/category.php");
require_once(WWW_DIR."/lib/groups.php");

$db = new DB();
$consoletools = new ConsoleTools();
$echooutput = true;

if ($echooutput)
    echo "\n\033[1;33m[".date("H:i:s A")."] Stage 3 -> Delete collections smaller/larger than minimum size/file count from group/site setting.\033[0m\n";
$stage3 = TIME();
//$where = (!empty($groupID)) ? " AND c.groupID=".$groupID : '';
if ($echooutput)
    echo "\nProcessing collections...\n";
$collections = $db->queryDirect("SELECT ID, filesize, totalFiles FROM collections WHERE filecheck=5");
$totalColCount = $db->getNumRows($collections);
// print_r($db->fetchArray($collections));
if($totalColCount)
{
    $colsProcessed = 0;
    $colsDeleted = 0;
    $totalColsProcessTime = microtime(true);
    //$db->setAutoCommit(false);
    while($fuckyou = $db->fetchAssoc($collections));
    {
        $colsProcessed ++;
        print_r($fuckyou);
        $consoletools->overWrite("Examining collection ".$consoletools->percentString($colsProcessed, $totalColCount)." ID=".$fuckyou['ID']);
        $tooLittleTooMuch = false;
        /*if($colRow['groupsize'] != 0  && !is_null($colRow['groupsize']) && $colRow['filesize'] < $colRow['groupsize']) $tooLittleTooMuch = true;
        elseif($this->siteMinFileSize != 0 && !is_null($this->siteMinFileSize) && $colRow['filesize'] < $this->siteMinFileSize) $tooLittleTooMuch = true;
        elseif($colRow['groupfiles'] != 0 && !is_null($colRow['groupfiles']) && $colRow['totalFiles'] < $colRow['groupfiles']) $tooLittleTooMuch = true;
        elseif($this->siteMinFileCount != 0 && !is_null($this->siteMinFileCount) && $colRow['totalFiles'] < $this->siteMinFileCount) $tooLittleTooMuch = true;
        elseif($this->siteMaxFileSize !=0 && !is_null($this->siteMaxFileSize) && $colRow['filesize'] > $this->siteMaxFileSize) $tooLittleTooMuch = true;
        // See if we got a hit
        if($tooLittleTooMuch)
        {
            $db->query("UPDATE collections SET filecheck=5 WHERE ID=".$colRow['ID']);
            $colsDeleted ++;
        }*/


    }
    //$db->Commit();
    //$db->setAutoCommit(true);
    $totalColsProcessTime = microtime(true) - $totalColsProcessTime;
    if ($echooutput)
    {
        echo "\n\nTotal collections marked for deletion: ".$colsDeleted."\n";
        echo "Total processing time: ".number_format($totalColsProcessTime,4)." Average per collection: ".number_format(($totalColsProcessTime/$totalColCount),4)."\n";
    }
}
