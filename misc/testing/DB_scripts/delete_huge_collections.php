<?php
/**
 * Created by JetBrains PhpStorm.
 * User: KurzonDax
 * Date: 8/13/13
 * Time: 10:23 AM
 *
 * This script will delete collections and associated binaries/parts that have been manually
 * set to filecheck=999.  I run a search in phpmyadmin for collections with a filesize
 * larger than 107374182400 (aka 100 GB) and update those to filecheck=999.
 */
define('FS_ROOT', realpath(dirname(__FILE__)));
require_once(FS_ROOT."/../../../www/config.php");
require_once(FS_ROOT."/../../../www/lib/framework/db.php");
require_once(FS_ROOT."/../../../www/lib/releases.php");
require_once(FS_ROOT."/../../../www/lib/site.php");
require_once(FS_ROOT."/../../../www/lib/consoletools.php");

$db = new Db;
$s = new Sites();
$consoletools = new ConsoleTools();
$site = $s->get();
$timestart = TIME();
$colsToDelete = 0;
$binsToDelete = 0;
$binsDeleted = 0;
$partsdeleted = 0;
$colsprocessed = 0;
$binsdeletedtotal=0;



$largecolsres = $db->queryDirect("SELECT ID FROM collections WHERE filecheck=999");
$colsToDelete = $db->getNumRows($largecolsres);
echo "\033[1;33m\n".date('H:i:s A')." - Deletion of ".$colsToDelete." large collections has begun...\033[0m\n";

if ($colsToDelete)
{
    $db->setAutoCommit(FALSE);
    while($largecolrow=$db->fetchAssoc($largecolsres))
    {
        $colsprocessed ++;
        $binaryres=$db->queryDirect("SELECT ID FROM binaries WHERE collectionID=".$largecolrow["ID"]);
        $binsToDelete = $db->getNumRows($binaryres);
        if($binsToDelete)
        {
            $binsDeleted=0;
            while($binaryrow=$db->fetchAssoc($binaryres))
            {
                $binsDeleted ++;
                $consoletools->overWrite("Processing collection ".$consoletools->percentString($colsprocessed,$colsToDelete)." Binary number ".$binaryrow["ID"]." ".$consoletools->percentString($binsDeleted,$binsToDelete));

                    $db->queryDirect("DELETE parts FROM parts WHERE binaryID=".$binaryrow["ID"]);
                    $partsdeleted = $partsdeleted+ $db->getAffectedRows();


            }
            $db->query("DELETE binaries FROM binaries WHERE collectionID=".$largecolrow["ID"]);
            $binsdeletedtotal = $binsdeletedtotal + $binsToDelete;
        }
    }
    $db->query("DELETE collections FROM collections WHERE filecheck=999");
    $db->Commit();
    $db->setAutoCommit(true);
    Echo "\033[1;33m\n".date('H:i:s A')." - Deletion of ".$colsToDelete."large collections has completed...\033[0m\n";
    echo "In total, we remove ".$binsdeletedtotal." binaries and ".$partsdeleted." parts in ".$consoletools->convertTime(TIME()-$timestart)."\n";
}
else
    echo "No collections found marked for deletiong\n";
exit;


function deleteParts($binaryID)
{
    $db = new Db;

    if(isset($binaryID))
    {
        // Echo "Deleteing parts asspciated with binary ID ".$binaryID;
        $db->queryDirect("DELETE parts FROM parts WHERE binaryID=".$binaryID);
        return $db->getAffectedRows();
    }
    else
        return false;

}