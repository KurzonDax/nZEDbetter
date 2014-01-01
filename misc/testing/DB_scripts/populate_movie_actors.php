<?php
/**
 * Project: nZEDbetter
 * User: Randy
 * Date: 12/14/13
 * Time: 1:12 PM
 * File: populate_movie_actors.php
 * 
 */
require_once(dirname(__FILE__) . "/../../../www/config.php");
require_once(WWW_DIR . "lib/framework/db.php");
require_once(WWW_DIR . "lib/consoletools.php");

$db = new DB();

echo "\nNow converting embedded movie actors from movie info table to the new movieActors table\n\n";
$allMovies = $db->queryDirect("SELECT ID, actors FROM movieinfo");
$movieCount = $db->getNumRows($allMovies);

if($movieCount > 0)
{
    $moviesProcessed = 0;
    $consoleTools = new ConsoleTools();

    while($movie = $db->fetchAssoc($allMovies))
    {
        $moviesProcessed ++;
        $actorIDs = array();
        $consoleTools->overWrite("Processing actors for movie ".$consoleTools->percentString($moviesProcessed, $movieCount));

        $actorArr = explode(",", $movie['actors']);
        foreach($actorArr as $actor)
        {
            $actor = trim($actor);
            if($actorExists = $db->queryOneRow("SELECT ID FROM movieActors WHERE name = '" . $actor . "' OR name LIKE '" . $actor . "%'"))
            {
                $db->query("INSERT IGNORE INTO movieIDtoActors (movieID, actorID) VALUES (" . $movie['ID'] . ", " . $actorExists['ID'] . ")");
            }
            else
            {
                $newActor = $db->queryInsert("INSERT INTO movieActors (name) VALUES ('" . $actor . "')");
                // echo $db->Error();
                $db->query("INSERT IGNORE INTO movieIDtoActorID (movieID, actorID) VALUES (" . $movie['ID'] . ", " . $newActor . ")" );
            }
        }

    }
}
exit ("\nFinished at ". date("H:i:s A") . "\n");
