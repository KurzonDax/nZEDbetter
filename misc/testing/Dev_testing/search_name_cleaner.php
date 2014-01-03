<?php
/**
 * Project: nZEDbetter
 * User: Randy
 * Date: 1/3/14
 * Time: 6:31 AM
 * File: search_name_cleaner.php
 * 
 */
require_once("config.php");
require_once(WWW_DIR . "lib/namecleaning.php");
require_once(WWW_DIR . "lib/consoletools.php");
require_once(WWW_DIR . "lib/movie.php");
$nameCleaning = new nameCleaning();
$consoleTools = new ConsoleTools();
$movies = new Movie();
do
{
    $text = $consoleTools->getUserInput("\nEnter the text to be cleaned: ");
    echo "\nCleaning Result: " . $cleanText = $nameCleaning->movieCleaner($text) . "\n";
    echo "\nParsing Result:\n";
    $cleanName = $movies->parseMovieSearchName($nameCleaning->movieCleaner($text));
    print_r($cleanName);
    echo "\n";
} while ($text != 'quit');
exit;