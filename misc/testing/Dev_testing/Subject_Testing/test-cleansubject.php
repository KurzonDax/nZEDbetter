<?php
require("../../../../www/config.php");
require_once(WWW_DIR."lib/namecleaning.php");
require_once(WWW_DIR."lib/consoletools.php");


	$consoleTools = new ConsoleTools();
    $nameCleaner = new NameCleaning();
    START:

    $text = $consoleTools->getUserInput("Enter text to be cleaned: ");
    echo "\nClean version: " . $nameCleaner->collectionsCleaner($text);
    $consoleTools->getUserInput("\nPress enter to continue.");
    /*// echo "Please input a name now.\n";
	// $name = trim(fgets(fopen("php://stdin","r")));
    $file1 = WWW_DIR."lib/logging/collections_insert.log";
    $lines = file($file1);
    foreach($lines as $line_num => $line)
    {
        echo $line."\n\n";
        echo htmlspecialchars_decode($namecleaner->cleanUnicode($line))."\n\n";
        $consoletools->getUserInput("Press enter to go again, or ctrl-c to quit: \n");
        // echo "<br>";
    }*/

    goto START;


?>
