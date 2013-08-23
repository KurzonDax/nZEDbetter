<?php
require("../../../../www/config.php");
require_once(WWW_DIR."lib/namecleaning.php");
require_once(WWW_DIR."lib/consoletools.php");

if(!isset($argv[1]))
	exit('You must start the script like this : php test-cleansubject.php true'."\n");
else
{
	$consoletools = new ConsoleTools();
    $namecleaner = new NameCleaning();
    START:

    // echo "Please input a name now.\n";
	// $name = trim(fgets(fopen("php://stdin","r")));
    $file1 = WWW_DIR."lib/logging/collections_insert.log";
    $lines = file($file1);
    foreach($lines as $line_num => $line)
    {
        echo $line."\n\n";
        echo htmlspecialchars_decode($namecleaner->cleanUnicode($line))."\n\n";
        $consoletools->getUserInput("Press enter to go again, or ctrl-c to quit: \n");
        // echo "<br>";
    }

    goto START;
}

?>
