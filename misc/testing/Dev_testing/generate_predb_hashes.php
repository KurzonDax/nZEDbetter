<?php
/**
 * Project: nZEDbetter
 * User: Randy
 * Date: 12/11/13
 * Time: 1:50 PM
 * File: generate_predb_hashes.php
 * 
 */
require_once("/var/www/nZEDbetter/www/config.php");
require_once(WWW_DIR.'/lib/framework/db.php');
require_once(WWW_DIR.'/lib/consoletools.php');
$db = new DB();
$consoleTools = new ConsoleTools();
echo "\nGenerating hashes for predb release names.  This process will take quite a while to complete.";
echo "\nWorking in batches of 10,000\n";
$limit = 10000;
$batch = 0;
do {
    echo "\nQuerying batch ".$batch."\n";
    $predbReleases = $db->queryDirect("SELECT ID, title FROM predb WHERE md2='' ORDER BY ID ASC LIMIT 10000 OFFSET " . ($batch * 10000));
    $count = 0;
    $db->setAutoCommit(false);
    if($predbReleases != false)
    {
        $totalReleases = $db->getNumRows($predbReleases);
        while($releaseRow = $db->fetchAssoc($predbReleases))
        {
            $count ++;
            preg_match('/[- ](?!.+[- ])(.+)/', $releaseRow['title'], $releaseGroup);
            $consoleTools->overWrite("Working on batch ".$batch." release ".$consoleTools->percentString($count, $totalReleases)."  group ".$releaseGroup[1]);

            $db->query('UPDATE predb SET md2='.$db->escapeString(hash('md2', $releaseRow['title'], false)).', md4=' . $db->escapeString(hash('md4', $releaseRow['title'], false)) .
                ', sha1=' . $db->escapeString(hash('sha1', $releaseRow['title'], false)) . ', ripemd128=' . $db->escapeString(hash('ripemd128', $releaseRow['title'], false)) .
                ', ripemd160=' . $db->escapeString(hash('ripemd160', $releaseRow['title'], false)) . ', tiger128_3=' . $db->escapeString(hash('tiger128,3', $releaseRow['title'], false)) .
                ', tiger160_3=' . $db->escapeString(hash('tiger160,3', $releaseRow['title'], false)) . ', tiger128_4=' . $db->escapeString(hash('tiger128,4', $releaseRow['title'], false)) .
                ', tiger160_4=' . $db->escapeString(hash('tiger160,4', $releaseRow['title'], false)) . ', haval128_3=' . $db->escapeString(hash('haval128,3', $releaseRow['title'], false)) .
                ', haval160_3=' . $db->escapeString(hash('haval160,3', $releaseRow['title'], false)) . ', haval128_4=' . $db->escapeString(hash('haval128,4', $releaseRow['title'], false)) .
                ', haval160_4=' . $db->escapeString(hash('haval160,4', $releaseRow['title'], false)) . ', haval128_5=' . $db->escapeString(hash('haval128,5', $releaseRow['title'], false)) .
                ', haval160_5=' . $db->escapeString(hash('haval160,5', $releaseRow['title'], false)) . ', releaseGroup=' . $db->escapeString($releaseGroup[1]) .
                ' WHERE ID=' . $releaseRow['ID']);
            // echo "\n".$db->Error();
        }
        $batch ++;
        $db->Commit();
        echo "\nBatch ".($batch-1)." complete.";
    }
    else
        $batch = -1;
} while ($batch != -1);
$db->setAutoCommit(true);
exit ("\nUpdate complete.  That was tough work.\n");
