<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Randy
 * Date: 8/15/13
 * Time: 1:05 PM
 * This file will search through the collections table and merge duplicate collection
 * hashes in to a single record.  As a part of the process, associated binaries will
 * be updated with the new collection ID.
 */
require_once("/var/www/nZEDb/www/config.php");
require_once("/var/www/nZEDb/www/lib/framework/db.php");
require_once("/var/www/nZEDb/www/lib/consoletools.php");

$db = new DB();
$consoletools = new ConsoleTools();
$safetymode = false;

if(isset($argv['1']) && $argv['1'] == "false")
{
    echo "\n\033[01;32mRunning in safety mode.  No changes to the database will be made.\033[00;37m\n";
    $safetymode = true;
}

$choice = $consoletools->getUserInput("Press 1 to deduplicate Collections, or 2 to deduplicate binaries: ");

if($choice === "1")
{
    echo "\n[".date("H:i:s A")."] Beginning process of merging and deleting duplicate collections\nGetting list of distinct collections...\n";
    $starttime = microtime(false);
    $distinctCols = $db->queryDirect("SELECT * from collections GROUP BY collectionhash");
    $distinctRowCount = $db->getNumRows($distinctCols);
    $colsProcessed = 0;
    $binariesUpdated = 0;
    $colsDeleted = 0;
    // Begin master loop
    echo "Beginning the de-duplicate process.\n";
    $db->setAutoCommit(false);
    while ($masterRow = $db->fetchAssoc($distinctCols))
    {
        $colsProcessed++;
        $masterID = $masterRow['ID'];
        $masterHash = $masterRow['collectionhash'];
        $consoletools->overWrite("Procesing collection ID ".$masterID." which is ".$consoletools->percentString($colsProcessed,$distinctRowCount));

        $duplicateRows = $db->queryDirect("SELECT * FROM collections WHERE collectionhash = ".$db->escapeString($masterHash)." AND ID !=".$masterID);

        if ($db->getNumRows($duplicateRows) > 0)
        {
            $duplicateCount = $db->getNumRows($duplicateRows);
            while ($dupeRow = $db->fetchAssoc($duplicateRows))
            {
                if(!$safetymode)
                    $db->queryDirect("UPDATE binaries SET collectionID = ".$masterID." WHERE collectionID = ".$dupeRow['ID']);
                $binariesUpdated = $binariesUpdated + $db->getAffectedRows();
            }
            if(!$safetymode)
                $db->query("DELETE collections FROM collections WHERE collectionhash = ".$db->escapeString($masterHash)." AND ID !=".$masterID);
            $colsDeleted = $colsDeleted + $db->getAffectedRows();
            if(!$safetymode)
                $db->query("UPDATE collections set dateadded = now(), filecheck=0 WHERE ID = ".$masterID);
        }
    }
    $db->Commit();
    $db->setAutoCommit(true);
    echo "\n[".date("H:i:s A")."] De-duplication process completed in ".$consoletools->convertTime(microtime(false)-$starttime);
    echo "\nUnique collections processed: ".$colsProcessed;
    echo "\nCollections deleted: ".$colsDeleted;
    echo "\nBinaries updated: ".$binariesUpdated."\n\n";
    exit ("Thank you for playing the de-dupe game.\n");
}
elseif($choice === "2")
{
    echo "\n[".date("H:i:s A")."] Beginning process of merging and deleting duplicate binaries\nGetting list of distinct binaries...\n";
    $starttime = microtime(false);
    $distinctBins = $db->queryDirect("SELECT * from binaries GROUP BY binaryhash");
    $distinctRowCount = $db->getNumRows($distinctBins);
    $binsProcessed = 0;
    $partsUpdated = 0;
    $binsDeleted = 0;
    // Begin master loop
    echo "Beginning the de-duplicate process.\n";
    $db->setAutoCommit(false);
    while ($masterRow = $db->fetchAssoc($distinctBins))
    {
        $binsProcessed++;
        $masterID = $masterRow['ID'];
        $masterHash = $masterRow['binaryhash'];
        $masterFileNumber = $masterRow['filenumber'];
        $consoletools->overWrite("Procesing binary ID ".$masterID." which is ".$consoletools->percentString($binsProcessed,$distinctRowCount));

        $duplicateRows = $db->queryDirect("SELECT * FROM binaries WHERE binaryhash = ".$db->escapeString($masterHash)." AND ID !=".$masterID." AND filenumber=".$masterFileNumber);

        if ($db->getNumRows($duplicateRows) > 0)
        {
            $duplicateCount = $db->getNumRows($duplicateRows);
            while ($dupeRow = $db->fetchAssoc($duplicateRows))
            {
                if(!$safetymode)
                    $db->queryDirect("UPDATE parts SET binaryID = ".$masterID." WHERE binaryID = ".$dupeRow['ID']);
                $partsUpdated = $partsUpdated + $db->getAffectedRows();
            }
            if(!$safetymode)
                $db->query("DELETE binaries FROM binaries WHERE binaryhash = ".$db->escapeString($masterHash)." AND ID !=".$masterID);
            $binsDeleted = $binsDeleted + $db->getAffectedRows();
            if(!$safetymode)
                $db->query("UPDATE binaries set partcheck=0 WHERE ID = ".$masterID);
        }
    }
    $db->Commit();
    $db->setAutoCommit(true);
    echo "\n[".date("H:i:s A")."] De-duplication process completed in ".$consoletools->convertTime(microtime(false)-$starttime);
    echo "\nUnique binaries processed: ".$binsProcessed;
    echo "\nBinaries deleted: ".$binsDeleted;
    echo "\nParts updated: ".$partsUpdated."\n\n";
    exit ("Thank you for playing the de-dupe game.\n");
}
elseif($choice === "3")
{
    echo "\n Spitting out some duplicate binary hashes for your perusal. Check bindupes.log\n";
    $distinctBins = $db->queryDirect("SELECT * from binaries GROUP BY binaryhash LIMIT 1000000");
    $distinctRowCount = $db->getNumRows($distinctBins);
    $binsProcessed=0;
    $dupeBinsFound = 0;

    while ($masterRow = $db->fetchAssoc($distinctBins))
    {
        $binsProcessed++;
        $masterID = $masterRow['ID'];
        $masterHash = $masterRow['binaryhash'];
        $masterFileNumber = $masterRow['filenumber'];
        $consoletools->overWrite("Procesing binary ID ".$masterID." which is ".$consoletools->percentString($binsProcessed,$distinctRowCount));

        $duplicateRows = $db->queryDirect("SELECT * FROM binaries WHERE binaryhash = ".$db->escapeString($masterHash)." AND ID !=".$masterID);

        if ($db->getNumRows($duplicateRows) > 0)
        {
            file_put_contents("./bindupes.log","--------------------------------------------------------\n", FILE_APPEND);
            while ($dupeRow = $db->fetchAssoc($duplicateRows))
            {
                file_put_contents("./bindupes.log","---\n".$masterHash." File Number: ".$masterFileNumber." Subject: ".$dupeRow['name']."\n", FILE_APPEND );
                $dupeBinsFound++;
            }

        }
    }
    echo "\n Found ".$dupeBinsFound." duplicate binary hashes.\n";
}
else
    exit("\nYou selected an invalid option.");
