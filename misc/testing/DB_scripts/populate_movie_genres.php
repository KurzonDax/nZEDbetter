<?php
/**
 * Project: nZEDbetter
 * User: Randy
 * Date: 12/14/13
 * Time: 1:12 PM
 * File: populate_movie_genres.php
 * 
 */
require_once(dirname(__FILE__) . "/../../../www/config.php");
require_once(WWW_DIR . "lib/framework/db.php");
require_once(WWW_DIR . "lib/consoletools.php");

$db = new DB();

echo "\nNow converting embedded movie genres from movie info table to the new movieGenres table\n\n";
$allMovies = $db->queryDirect("SELECT ID, genre FROM movieinfo");
$movieCount = $db->getNumRows($allMovies);

if($movieCount > 0)
{
    $moviesProcessed = 0;
    $consoleTools = new ConsoleTools();

    while($movie = $db->fetchAssoc($allMovies))
    {
        $moviesProcessed ++;
        $genreIDs = array();
        $consoleTools->overWrite("Processing genres for movie ".$consoleTools->percentString($moviesProcessed, $movieCount));

        $genreArr = explode(",", $movie['genre']);
        foreach($genreArr as $genre)
        {
            $genre = trim($genre);
            if($genreExists = $db->queryOneRow("SELECT ID FROM movieGenres WHERE name = '" . $genre . "' OR name LIKE '" . $genre . "%'"))
            {
                $db->query("INSERT IGNORE INTO movieIDtoGenre (movieID, genreID) VALUES (" . $movie['ID'] . ", " . $genreExists['ID'] . ")");

                $genreIDs[] = $genreExists['ID'];
            }
            else
            {
                $newGenre = $db->queryInsert("INSERT INTO movieGenres (name) VALUES ('" . $genre . "')");
                // echo $db->Error();
                $db->query("INSERT IGNORE INTO movieIDtoGENRE (movieID, genreID) VALUES (" . $movie['ID'] . ", " . $newGenre . ")" );
                $genreIDs[] = $newGenre;
            }
        }
        if(array_count_values($genreIDs) > 1)
        {
            foreach($genreIDs as $genreID)
            {
                $similarGenres = $db->queryOneRow("SELECT ID, similarGenres FROM movieGenres WHERE ID=" . $genreID);
                $similarArr = array_unique(array_merge($genreIDs, explode(" ", $similarGenres['similarGenres'])), SORT_NUMERIC);
                if (($key = array_search($genreID, $similarArr)) !== false)
                {
                    unset($similarArr[$key]);
                }
                $db->query("UPDATE movieGenres SET similarGenres = '" . implode(" ", $similarArr) . "' WHERE ID = ". $similarGenres['ID']);
            }
        }

    }
}
exit ("\nFinished at ". date("H:i:s A") . "\n");
