<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Randy
 * Date: 8/24/13
 * Time: 9:29 AM
 * To change this template use File | Settings | File Templates.
 */


require(dirname(__FILE__)."/config.php");
require_once(WWW_DIR."/lib/framework/db.php");
require_once(WWW_DIR."/lib/consoletools.php");
require_once(WWW_DIR."/lib/site.php");
require_once(WWW_DIR."/lib/category.php");
require_once(WWW_DIR."/lib/groups.php");
require_once(WWW_DIR."/lib/movie.php");
require_once(WWW_DIR."/lib/TMDb.php");
require_once(WWW_DIR."/lib/namecleaning.php");

$s = new Sites();
$site = $s->get();
$tmdb = new TMDb($site->tmdbkey);
$cast = $tmdb->getMovieCast(550);
foreach($cast['cast'] as $name)
    echo $name['name'] . "\n";
exit;







/*
$db = new DB();
$movie = new Movie();
$namecleaning = new nameCleaning();
$category = new Category();
$consoletools = new ConsoleTools();
$echooutput = true;


$fetchWithoutYear = true;


$movieres = $db->queryDirect("SELECT * FROM releases WHERE categoryID IN (2020, 2030, 2040, 2050, 2060, 2070) AND imdbID IS NULL ORDER BY ID ASC");
$moviecount = $db->getNumRows($movieres);
echo "\nFound ".$moviecount." movies to update\n\n";
$processed = 0;
$matchedMovies = 0;
$renamedMovies = 0;
$movedCategory = 0;
while ($movierow=$db->fetchAssoc($movieres))
{
    $processed ++;
    echo "\nWorking on movie ".$consoletools->percentString($processed, $moviecount)." title = ".$movierow['searchname']."\n";

    $updatedCategory = $category->determineCategory($movierow['name'], $movierow['groupID']);
    if($updatedCategory != $movierow['categoryID'])
    {
        echo "This release is being assigned to a new category: ".$updatedCategory."\n";
        $db->query("UPDATE releases SET categoryID=".$db->escapeString($updatedCategory)." WHERE ID=".$movierow['ID']);
        if(!($updatedCategory>2000 && $updatedCategory<2999))
        {
            $movedCategory ++;
            echo "Release is no longer considered a movie.\n";
            usleep(500);
            continue;
        }
    }
    $refinedSearchName = $namecleaning->releaseCleaner($movierow['name']);

    if($refinedSearchName != $movierow['searchname'])
    {
        echo "Updating searchname field in database. Release ID: ".$movierow['ID']."\n";
        echo "Old name:     ".$movierow['searchname']."\n";
        echo "New name:     ".$refinedSearchName."\n";
        $db->query("UPDATE releases SET searchname=".$db->escapeString($refinedSearchName)." WHERE ID=".$movierow['ID']);
        $renamedMovies ++;
        //$consoletools->getUserInput("Press enter to continue: ");
        //usleep(500);
    }
    $refinedSearchName = $namecleaning->movieCleaner($refinedSearchName);

    $movieCleanNameYear = $movie->parseMovieSearchName($refinedSearchName);

    if ($movieCleanNameYear != false && preg_match('/(.+)\(((20|19)\d\d)\)/', $movieCleanNameYear, $matches))
    {
        $movieCleanName = $matches['1'];
        $movieCleanYear = $matches['2'];
        $results = $movie->fetchTmdbInfoByName($movieCleanName, $movieCleanYear);
    }
    elseif(!$fetchWithoutYear)
    {
        echo "\nMovie does not have a year in the release search name. Skipping...\n";
        continue;
    }
    elseif($fetchWithoutYear && $movieCleanNameYear != false)
    {
        echo "\n\033[00;33mAttempting to match without a year.  Search title: ".$movieCleanNameYear."\n\033[00;37m";
        $movieCleanYear = false;
        $results = $movie->fetchTmdbInfoByName($movieCleanNameYear);
    }


    $matchfound = false;

    if(count($results['results'])>0)
    {
        foreach ($results['results'] as $possibleMatch)
        {
            $ourName = strtolower($movieCleanName);
            $tmdbName = strtolower($possibleMatch['title']);
            similar_text($ourName, $tmdbName, $percentSimilar);
            // echo "TMDb Title 0:     ".$results['results']['0']['title']." (".$results['results']['0']['release_date'].") - Match: ".number_format($percentSimilar, 2)."%.\n";
            if(isset($possibleMatch['release_date']) &&  preg_match('/((20|19)\d\d)/',$possibleMatch['release_date'], $matches))
                $tmdbYear = $matches['1'];
            if($movieCleanYear !== false && isset($tmdbYear))
                $matchedYear = ($tmdbYear >= $movieCleanYear -1 && $tmdbYear < $movieCleanYear + 2) ? true : false;
            else
                $matchedYear = true;
            if ($percentSimilar>80 && $matchedYear)
            {
                echo "\033[01;32mMatch found:   ".$possibleMatch['title']." (".$tmdbYear.") Match: 0  ID: ".$possibleMatch['id']."\n\033[00;37m";
                $matchfound = $possibleMatch['id'];
                break;
            }
        }
    }
    if($matchfound>0)
    {

        // $yesOrNo = $consoletools->getUserInput("\nDo you want to update this release with TMDb ID ".$matchfound." (Y or N)? [Y]: ");
        $yesOrNo = 'Y'; // Temporary thing
        if($yesOrNo == 'Y' || $yesOrNo == 'y' || $yesOrNo == '')
        {
            $tmdbProps = $movie->fetchTmdbProperties($matchfound, true);
            // print_r($tmdbProps);
            // $consoletools->getUserInput("Press enter to continue with update.");
            $movieID = $movie->updateMovieInfo($tmdbProps['imdb_id'], $matchfound, $tmdbProps);
            //echo "\nUpdating movie with IMDb ID: ".$tmdbProps['imdb_id']."\n";
            $db->query("UPDATE releases SET imdbID=".$tmdbProps['imdb_id']." WHERE ID=".$movierow['ID']);
            $matchedMovies ++;
        }
    }
    else
    {
        echo "\nNo matches found in IMDB.\n\n";
        file_put_contents(WWW_DIR."/lib/logging/tmdb-nomatch.log",$movierow['ID'].",".$db->escapeString($refinedSearchName)."\n", FILE_APPEND);
    }
    // usleep(750);
}
exit ("\nAll done...\nMovies matched: ".$matchedMovies."/".$processed."\n"."Renamed movies: ".$renamedMovies."\nChanged Category: ".$movedCategory."\n");

*/
