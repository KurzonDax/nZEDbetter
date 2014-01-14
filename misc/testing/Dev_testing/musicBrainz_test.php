<?php
/**
 * Project: nZEDbetter
 * User: Randy
 * Date: 9/7/13
 * Time: 11:26 PM
 * File: musicBrainz_test.php
 *
 */
require(dirname(__FILE__) . "/config.php");
require_once(WWW_DIR . "/lib/framework/db.php");
require_once(WWW_DIR . "/lib/consoletools.php");
require_once(WWW_DIR . "/lib/namecleaning.php");
require_once(WWW_DIR . "/lib/MusicBrainz.php");
require_once(WWW_DIR . "/lib/music.php");
require_once(WWW_DIR . "lib/category.php");
/**
 *
 */
define('DEBUG_ECHO', false);

$consoleTools = new ConsoleTools();
$db = new DB();
$category = new Category();
$matchedReleases = 0;
$singleReleases = 0;
echo "\nWelcome to the MusicBrainz test script.\n";

$catToProcess = 3000;
$offset = '';
$relID = $consoleTools->getUserInput("Enter a release ID, or press enter to search category 3000: ");
if(!is_numeric($relID))
    $offset = $consoleTools->getUserInput("\nPlease enter the offset to begin at: ");
if($offset == '' || !is_numeric($offset))
    $offset = 0;
if(!is_numeric($relID))
    $sql = "SELECT ID, name, searchname, groupID, categoryID, musicinfoID FROM releases WHERE categoryID BETWEEN ".$catToProcess." AND ".($catToProcess+999)." LIMIT ".$offset.",100";
else
    $sql = "SELECT ID, name, searchname, groupID, categoryID, musicinfoID FROM releases WHERE ID=".$relID;

$musicReleases = $db->queryDirect($sql);
$totalReleases = $db->getNumRows($musicReleases);
echo "\nWe found ".number_format($totalReleases)." music releases to process.\n";
$nameCleaning = new nameCleaning();
while($musicRow = $db->fetchAssoc($musicReleases))
{
    if(preg_match('/bootleg/i', $musicRow['name']) === 1)
    {
        if(DEBUG_ECHO)
        {
            echo "Skipping bootleg release: " . $musicRow['name'] . "\n";
            $consoleTools->getUserInput('Press enter to continue...');
        }
        continue;
    }
    $cleanSearchName = $nameCleaning->musicCleaner($musicRow['searchname']);
    $query = cleanQuery($cleanSearchName);
    $artist = false;
    $recording = false;
    if(preg_match('/\(?(19|20)\d\d\)?(?!.+(19|20)\d\d)(?!kbps|x)/', $musicRow['searchname'], $year) === 0)
        preg_match('/\(?(19|20)\d\d\)?(?!.+(19|20)\d\d)(?!kbps|x)/', $musicRow['name'], $year);

    echo "\nRelease ID:     " . $musicRow['ID'] . "\n";
    echo   "Release name:   " . $musicRow['name'] . "\n\n";
    echo   "Search Name:    " . $musicRow['searchname'] . "\n";
    echo   "Release Year:   " . (isset($year[0]) ? $year[0] : "Not Found") . "\n";
    echo   "Query String:   " . $query . "\n";

    $singleTitle = isSingle($musicRow['name']);

    $artistSearchArray[] = $musicRow['searchname'];
    $artistSearchArray[] = normalizeString($musicRow['searchname']);
    $artistSearchArray[] = normalizeString($musicRow['searchname'], true);
    $artistSearchArray[] = $musicRow['name'];
    $artistSearchArray[] = normalizeString($musicRow['name']);
    $artistSearchArray[] = normalizeString($musicRow['name'], true);

    if(!is_array($singleTitle))
    {
        $artistResult = getArtist($query, $artistSearchArray);
        if($artistResult)
        {

            $query = trim(preg_replace('/\b' . $artistResult['matchString'] . '\b/i', '', $query));
            $releaseSearchArr = array();

            $releaseSearchArr = __buildReleaseSearchArray($musicRow['searchname'], $releaseSearchArr);
            $releaseSearchArr = __buildReleaseSearchArray($musicRow['name'], $releaseSearchArr);

            $result = getReleaseName($query, $artistResult, $releaseSearchArr, (isset($year[0]) ? $year[0] : null));
            if($result)
            {
                echo "\n\nRelease name match: ".$result['title']."\nMB ID: ".$result['mbID']."\n";
                echo "Artist: ".$result['artist']."\n";
                echo "Artist ID: ".$result['artistID']."\n";
                $matchedReleases ++;
            }
            else
                echo "\n\nNo release name matches found\n";
        }
        else
            echo "\n\nArtist could not be determined for release\n";

    }
    else
    {
        $singleReleases++;
        $prefix = isset($singleTitle['disc']) ? (string)$singleTitle['disc'] . (string)$singleTitle['track'] : $singleTitle['track'];
        $query = preg_replace('/^' . $prefix . '/', '', $query);

        $artistResult = getArtist((isset($singleTitle['artist']) ? $singleTitle['artist'] : $query), $artistSearchArray);

        echo "Song " . (isset($singleTitle['disc']) ? "Disc: " . $singleTitle['disc'] . " " : '') . "Track: " . (isset($singleTitle['track']) ? $singleTitle['track'] : "Not Found") . "\n";
        echo "Song Title:   " . $singleTitle['title'] . "\n";
        echo "Song Release: " . (isset($singleTitle['release']) ? $singleTitle['release'] : "Not Found") . "\n";
        echo "Song Artist:  " . (isset($singleTitle['artist']) ? $singleTitle['artist'] : 'Not Found') . "\n";

        if($artistResult)
        {

            echo "\nArtist Name:    ".$artistResult['name']."\n";
            echo   "Artist ID:      ".$artistResult['id']."\n";
            echo   "Percent Match:  ".$artistResult['percentMatch']."\n";
            if(isset($year[0]))
                $singleTitle['year'] = $year[0];
            $singleTitle['releaseID'] = $musicRow['ID'];
            $recording = getRecording($singleTitle, $artistResult, $musicRow['name']);
            if($recording)
            {
                echo "\nRecording Title:   " . $recording['title'] . "\n";
                echo   "Recording mbID:    " . $recording['mbID'] . "\n";
                echo   "Recording Release: " . ($recording['release'] !== false ? $recording['release']['title'] : "Not Found") . "\n";
                echo   "Recording Rel ID:  " . ($recording['release'] !== false ? $recording['release']['mbID'] : "Not Found") . "\n";
            }
            else
            {
                echo "\nUnable to find a match for this single.\n";
            }
        }
        else
            echo "\n\nUnable to match an artist for this single.\n";


    }


    // if(DEBUG_ECHO)
        $consoleTools->getUserInput("\nPress enter to continue: ");
}
echo "\nSingles Found: ".$singleReleases;
echo "\nTotal Releases Matched: ".$matchedReleases."\n";
echo "Match Percentage: ".($totalReleases > 0 ? (($matchedReleases/($totalReleases-$singleReleases)*100)) : '0%')."\n";
exit ("\nThanks for playing...\n");


/**
 * @param string    $text           text to use as base
 * @param array     $searchArray    Array to append results to
 *                                  
 * @return array
 *
 * This function builds an array of strings based on rules defined within the
 * function.  The array is then used to compare release search results against.
 */
function __buildReleaseSearchArray($text, $searchArray)
{
    $searchArray[] = $text;
    $searchArray[] = normalizeString($text);
    $searchArray[] = normalizeString($text, true);

    // Remove the word "volume" because many entries in MusicBrainz don't include it
    // i.e. instead of Great Music Volume 1, MB will have Great Music 1
    if (preg_match('/\bVolume\b/i', $text))
        $searchArray[] = preg_replace('/\bVolume\b/i', ' ', $text);
    // Replace ordinal numbers with roman numerals
    preg_match('/\bVolume[ \-_\.](\d)\b/i', $text, $matches);
    switch ($matches[1])
    {
        case '1':
            $searchArray[] = preg_replace('\bVolume[ \-_\.]1\b', ' Volume I ', $text);
            $searchArray[] = preg_replace('\bVolume[ \-_\.]1\b', ' I ', $text);
            break;
        case '2':
            $searchArray[] = preg_replace('\bVolume[ \-_\.]2\b', ' Volume II ', $text);
            $searchArray[] = preg_replace('\bVolume[ \-_\.]2\b', ' II ', $text);
            break;
        case '3':
            $searchArray[] = preg_replace('\bVolume[ \-_\.]3\b', ' Volume III ', $text);
            $searchArray[] = preg_replace('\bVolume[ \-_\.]3\b', ' III ', $text);
            break;
        case '4':
            $searchArray[] = preg_replace('\bVolume[ \-_\.]4\b', ' Volume IV ', $text);
            $searchArray[] = preg_replace('\bVolume[ \-_\.]4\b', ' IV ', $text);
            break;
        case '5':
            $searchArray[] = preg_replace('\bVolume[ \-_\.]5\b', ' Volume V ', $text);
            $searchArray[] = preg_replace('\bVolume[ \-_\.]5\b', ' V ', $text);
            break;
    }

    // Get rid of extra spaces in all values
    foreach ($searchArray as $key => $value)
    {
        $searchArray[$key] = preg_replace('/\s{2,}/', ' ', $value);
    }

    return $searchArray;
}

/**
 * @param string        $query          Search string to be sent to MusicBrainz
 * @param string|array  $searchArray    String or array of strings that results should be matched against
 *
 * @return array|bool
 */
function getArtist($query, $searchArray)
{
    $musicBrainz = new MusicBrainz();

    if(!is_array($searchArray))
    {
        $temp = $searchArray;
        unset($searchArray);
        $searchArray = array();
        $searchArray[] = $temp;
        $searchArray[] = normalizeString($temp);
        $searchArray[] = normalizeString($temp, true);
    }

    $return = false;
    $results = $musicBrainz->searchArtist($query, '', 50);

    $wordCount = count(explode(' ', $query));
    if($query == 'VA')
    {
        $return['name'] = 'Various Artists';
        $return['id'] = '89ad4ac3-39f7-470e-963a-56509c546377';
        $return['matchString'] = 'VA';
        $return['percentMatch'] = '100';

        return $return;
    }
    //if($results['artist-list']['@attributes']['count'] == '0')
    $resultsAttr = isset($results->{'artist-list'}) ? $results->{'artist-list'}->attributes() : array();
    if(isset($resultsAttr['count']) && $resultsAttr['count'] == '0')
    {
        if(DEBUG_ECHO)
            echo "Artist name search returned no results\n";
        return $return;
    }
    elseif(!isset($resultsAttr['count']))
    {
        print_r($results);
        return $return;
    }
    elseif(DEBUG_ECHO)
            echo "Artists Found: ". $resultsAttr['count']."\n";

    $percentMatch = -1000;

    $i = 0;
    foreach($results->{'artist-list'}->artist as $artist)
    {

        $artistCheck = checkArtistName($artist, $searchArray, false, (((30-$i) / 30) * 10));
        if($artistCheck && $artistCheck['percentMatch'] > $percentMatch)
        {
            // The following helps to prevent single-word artists from matching an artist
            // with a similar full name (i.e Pink should not match Pink Floyd)
            // Obviously only works if the query string is two words or less
            if($wordCount < 3 && count(explode(' ' , $artistCheck['name'])) != $wordCount)
            {
                if(DEBUG_ECHO)
                    echo "Matching artist name too short: " . $artistCheck['name'] . "\n";
                continue;
            }
            $return = $artistCheck;
            $percentMatch = $artistCheck['percentMatch'];
        }
        $i++;
    }

    return $return;
}

/**
 * @param string            $query          searchname after musicCleaner and cleanQuery
 * @param array|bool        $artist         Array with 'name' and 'mbID' of artist or false
 * @param array             $searchArray    array of strings to compare results against
 * @param integer|null      $year           Year of release
 *
 * @return array|bool
 *
 * NOTE: If an artist is provided, better results will be obtained if the artist
 * name is removed from the $query string
 */
function getReleaseName($query, $artist, $searchArray, $year=null)
{
    // enforce artist requirement
    // check all occurrences of $searchArray, fix $searchNameArr to use $searchArray

    $query = normalizeString($query);
    $musicBrainz = new MusicBrainz();
    $return = false;
    $percentMatch = 0;
    $artist = $artist=='' ? false : $artist;
    $artistArr = false;

    if($artist === false)
    {
        $results = $musicBrainz->searchRelease($query, 'release', '', '', 30);
    }
    else
    {
        $artistArr = $artist;
        $results = $musicBrainz->searchRelease($query, 'release', normalizeString($artistArr['name']), 'artistname');
    }
    if(!isset($results->{'release-list'}->attributes()->count))
        print_r($results);
    if($results->{'release-list'}->attributes()->count == '0')
    {
        if(DEBUG_ECHO)
            echo "Release name search returned no results\n";
        return $return;
    }
    else
        if(DEBUG_ECHO)
            echo "Releases Found: ".$results->{'release-list'}->attributes()->count."\n";
    // print_r($results);
    if($results->{'release-list'}->attributes()->count == '1')
    {
        $matchFound = false;
        foreach($searchArray as $searchName)
        {
            if(stripos($searchName, normalizeString($results->{'release-list'}->release->title)) === false &&
                stripos(normalizeString($searchName, true), normalizeString($results->{'release-list'}->release->title, true)) === false)
                continue;
            else
            {
                $matchFound = true;
                break;
            }
        }
        if(!$matchFound)
        {
            if(DEBUG_ECHO)
                echo "Non-matching release: ".$results->{'release-list'}->release->title."\n";
            return false;
        }
        else
        {
            if(!is_array($return)){ $return = array();}
            $return['title'] = $results->{'release-list'}->release->title;
            $return['mbID'] = $results->{'release-list'}->release->attributes()->id;
        }
    }
    else // More than 1 release was found
    {
        foreach($results->{'release-list'}->release as $release)
        {
            $matchFound = false;
            $matchedSearchName = '';
            foreach($searchArray as $searchName)
            {
                if(stripos($searchName, normalizeString($release->title)) === false &&
                    stripos(normalizeString($searchName, true), normalizeString($release->title, true)) === false)
                    continue;
                else
                {
                    $matchedSearchName = $searchName;
                    $matchFound = true;
                    break;
                }
            }
            if(!$matchFound)
            {
                if(DEBUG_ECHO)
                    echo "Non-matching release: ".$release->title."\n";
                continue;
            }
            else
            {
                similar_text(normalizeString($release->title), $matchedSearchName, $tempMatch);
                if(DEBUG_ECHO)
                    echo "Checking release: ".$release->title."\n";
                if(!$artist && isset($release->{'artist-credit'}->{'name-credit'}))
                {
                    // print_r($release['artist-credit']['name-credit']);
                    $i = 0;
                    foreach($release->{'artist-credit'}->{'name-credit'} as $relArtist)
                    {
                        if(isset($relArtist->name))
                            $artistArr = checkArtistName($relArtist, $searchArray, false, (((30 - $i) / 30) * 10));
                        else
                            $artistArr = checkArtistName($relArtist->artist, $searchArray, false, (((30 - $i) / 30) * 10));
                        if($artistArr && stripos($query, normalizeString($artistArr['name'])) !== false)
                        {
                            $tempMatch += 25;
                            break;
                        }
                        else
                            $artistArr = false;
                        $i ++;
                    }
                    if(!$artistArr)
                    {
                        if(DEBUG_ECHO)
                            echo "No matching artist was found in the release.\n";
                        continue;
                    }
                    elseif($artistArr['name'] == 'Various Artists')
                        $tempMatch -= 15;
                }
                if(normalizeString($release->title, true) == normalizeString($artistArr['name'], true) && substr_count($query, normalizeString($artistArr['name'], true)) == 1)
                {
                    if(DEBUG_ECHO)
                        echo "Artist name and release title are the same, but not looking for self-titled release\n";
                    continue;
                }
                elseif(stripos(trim(preg_replace('/'.normalizeString($artistArr['name'], true).'/', '', normalizeString($matchedSearchName, true), 1)),
                        trim(normalizeString($release->title, true))) === false)
                {
                    if(DEBUG_ECHO)
                        echo "Title no longer matched after extracting artist's name.\n";
                    continue;
                }
                if(isset($release->date) && !is_null($year) && preg_match('/'.$year.'/',$release->date))
                    $tempMatch += 25;
                elseif(isset($release->date) && !is_null($year))
                {
                    preg_match('/(19|20)\d\d', $release->date, $relYear);
                    if(isset($relYear[0]) && ($relYear[0] == ($year - 1) || $relYear[0] == ($year+1)))
                        $tempMatch += 20;
                }
                elseif (isset($release->{'release-event-list'}->{'release-event'}->date) && !is_null($year) && $release->{'release-event-list'}->{'release-event'}->date == $year)
                    $tempMatch += 20;
                elseif(isset($release->{'release-event-list'}->{'release-event'}->date) && !is_null($year))
                {
                    preg_match('/(19|20)\d\d', $release->{'release-event-list'}->{'release-event'}->date, $relYear);
                    if($relYear[0] == ($year - 1) || $relYear[0] == ($year+1))
                        $tempMatch += 15;
                }
                if(isset($release->{'medium-list'}->medium->format) && $release->{'medium-list'}->medium->format == 'CD')
                    $tempMatch += 15;
                if(DEBUG_ECHO)
                    echo "Matching release: ".$release->title." tempMatch: ".$tempMatch."\n";
                if($tempMatch > $percentMatch)
                {
                    if(!is_array($return)){ $return = array();}
                    $return['title'] = $release->title;
                    $return['mbID'] = $release->attributes()->id;
                    if(!$artist)
                    {
                        $return['artist'] = $artistArr['name'];
                        $return['artistID'] = $artistArr['id'];
                    }
                    $percentMatch = $tempMatch;
                }
            }
        }
    }

    if(isset($return['mbID']))
        return $return;
    else
        return false;
}

/**
 * @param array     $query                  query array containing artist (required), title (required), release, track, year
 * @param bool      $requireReleaseMatch    Whether or not to only match the title if the release matches as well
 *
 * @return bool|array
 */
function getRecording($query, $requireReleaseMatch = false)
{
    /*      searchRecording Possible Fields
     *      arid 			artist id
            artist 			artist name is name(s) as it appears on the recording
            artistname 		an artist on the recording, each artist added as a separate field
            creditname 		name credit on the recording, each artist added as a separate field

     */

    $musicBrainz = new MusicBrainz();
    $return = false;
    $results = array();

    // Experimental - remove text inside parenthesis.  Usually contains a second artist, i.e. (featuring John Doe), but
    // seems to cause a lot of non-matches, or mismatches
    $query = preg_replace('/\([\w\s\.\-]+\)/', '', $query);

    if(is_array($query) && isset($query['title']))
    {
            $results = $musicBrainz->searchRecording($query['title'], 'recording', $query['artist'], 'artistname');
    }

    if (!isset($results->{'recording-list'}->attributes()->count))
        print_r($results);
    if ($results->{'recording-list'}->attributes()->count == '0')
    {
        if (DEBUG_ECHO)
            echo "Recording search returned no results\n";

        return $return;
    } else
        if (DEBUG_ECHO)
            echo "Recordings Found: " . $results->{'recording-list'}->attributes()->count . "\n";


    $normalizedTitleArr = array();
    $normalizedTitleArr[] = isset($query['title']) ? normalizeString($query['title']) : normalizeString($query);
    $normalizedTitleArr[] = isset($query['title']) ? normalizeString($query['title'], true) : normalizeString($query, true);

        $i = 0;     // Recording result counter, used for weighting results
        $percentMatch = -1000;
        foreach($results->{'recording-list'}->recording as $recording)
        {
            $matchFound = false;
            $releaseArr = false;
            foreach ($normalizedTitleArr as $normalizedTitle)
            {
                if (DEBUG_ECHO)
                {
                    echo "Checking Title: " . $normalizedTitle . "\n";
                    echo "Against Title:  " . normalizeString($recording->title) . "\n";
                }
                if (stripos($normalizedTitle, normalizeString($recording->title)) === false &&
                    stripos($normalizedTitle, normalizeString($recording->title, true)) === false &&
                    stripos(normalizeString($recording->title), $normalizedTitle) === false &&
                    stripos(normalizeString($recording->title, true), $normalizedTitle) === false &&
                    $normalizedTitle != normalizeString($recording->title))
                    continue;
                else
                {
                    $matchFound = true;
                    break;
                }
            }
            if($matchFound)
            {
                // Check for a matching release for the recording
                $releaseMatchFound = false;
                if(isset($query['release']) && isset($recording->{'release-list'}))
                {
                    if($releaseArr = __getRecordingRelease($query, $recording->{'release-list'}))   // release loop
                        $releaseMatchFound = true;
                }
                else // query['release'] is not set, or there was not a release list in the results
                {
                    $releaseMatchFound = true; //Simplifies coding to fake a release match
                    $releaseArr = false; // But the release array won't contain anything
                }
                if(!$releaseMatchFound && $requireReleaseMatch)
                {
                    if(DEBUG_ECHO)
                        echo "No matching release for matched title.\n";
                    continue;
                }
                else
                {
                    similar_text((isset($query['title']) ? $query['title'] : $query), $recording->title, $tempPercentMatch);
                    $tempPercentMatch += (((30 - $i) / 30) * 10); // matches weighted based on position in results list
                    $tempPercentMatch += ($releaseMatchFound && isset($recording->{'release-list'}) ? 15 : 0);  //Weight recordings for which the release matched
                    if($tempPercentMatch > $percentMatch)
                    {
                        if (!is_array($return))
                            $return = array();
                        $return['title'] = $recording->title;
                        $return['mbID'] = $recording->attributes()->id;
                        $return['percentMatch'] = $percentMatch = $tempPercentMatch;
                        $return['release'] = $releaseArr;
                    }
                } // Release match is true
            } // Title match found
            else
            {
                if(DEBUG_ECHO)
                    echo "Non-matching recording title: " . $recording->title . "\n";
            }
            $i ++; // Increment the recording result counter
        }   // Recording result loop
    //}   // More than one result

    ob_start();
    print_r($results);
    $resultsString = ob_get_clean();
    file_put_contents(WWW_DIR . 'lib/logging/vardump/'.$query['releaseID'].'-'.$query['artist'].'-'.$query['title'].'.log', $resultsString);


    return $return;
}

/**
 * @param $query
 * @param $releaseList
 *
 * @return array
 */
function __getRecordingRelease($query, $releaseList)
{
    $releaseMatchFound = false;
    $x = 0;
    $releasePercentMatch = $tempReleasePercentMatch = -1000;
    $releaseArr = false;

    if (isset($query['release']))
    {
        $normalizedReleaseArr = array();
        $normalizedReleaseArr[] = normalizeString($query['release']);
        $normalizedReleaseArr[] = normalizeString($query['release'], true);

    } else
        $normalizedReleaseArr = null;

    foreach ($releaseList->release as $release)
    {
        echo "Check release:    " . $release->title . "\n";
        foreach ($normalizedReleaseArr as $normalizedRelease)
        {
            if (stripos($normalizedRelease, normalizeString($release->title)) === false &&
                stripos($normalizedRelease, normalizeString($release->title, true)) === false &&
                stripos(normalizeString($release->title), $normalizedRelease) === false &&
                stripos(normalizeString($release->title, true), $normalizedRelease) === false &&
                $normalizedRelease != normalizeString($release->title)
            )
                continue;
            else
            {
                $releaseMatchFound = true;
                break;
            }
        }

        if ($releaseMatchFound && isset($query['year']) && (isset($release->date) || isset($release->{'release-event-list'}->{'release-event'}->date)))
        {
            echo "Checking year of release: " . $query['year'] . "\n";
            preg_match('/(19\d\d|20\d\d)/', (isset($release->date) ? $release->date : $release->{'release-event-list'}->{'release-event'}->date), $releaseYear);
            if (isset($releaseYear[0]))
            {
                if ($query['year'] >= $releaseYear[0] - 1 && $query['year'] <= $releaseYear[0] + 1)
                    $releaseMatchFound = true;
                else
                    $releaseMatchFound = false; // Reject match if the year isn't within + or - 1 year
            }
        }

        if ($releaseMatchFound)
        {
            echo "Release match found: " . $release->title . "\n";
            similar_text($query['title'], $release->title, $tempReleasePercentMatch);
            $tempReleasePercentMatch += (((30 - $x) / 30) * 10); // matches weighted based on position in results list

            if ($tempReleasePercentMatch > $releasePercentMatch)
            {
                $releaseArr['title'] = $release->title;
                $releaseArr['mbID'] = $release->attributes()->id;
                $releaseArr['percentMatch'] = $releasePercentMatch = $tempReleasePercentMatch;
            }
        }
        // Increment the release result counter
        $x++;
    }

    return $releaseArr;
}

/**
 * @param string    $text       query text to be cleaned
 * @param bool      $debug      default: false, don't print debug output
 * @return mixed
 */
function cleanQuery($text, $debug=false)
{
    // Remove year
    if($debug)
        echo "\nStrip Search Name - " . $text . "\n";
    $text = preg_replace('/\((19|20)\d\d\)|(?<!top|part|vol|volume)[ \-_]\d{1,3}[ \-_]|\d{3,4} ?kbps| cd ?\d{1,2} /i', ' ', $text);
    if($debug)
        echo "1 - " . $text . "\n";
    // Remove extraneous format identifiers
    $text = str_replace(array('MP3','FLAC','WMA','WEB', "cd's", ' cd ',' FM '), ' ', $text);
    if ($debug)
        echo "2 - " . $text . "\n";
    $text = str_ireplace(' vol ', ' Volume ', $text);
    if ($debug)
        echo "3 - " . $text . "\n";
    // Remove extra punctuation and non alphanumeric
    $text = str_replace(array(".", "_", '-', "|", "<", ">", '"', "=", "~", '[', "]", "(", ")", "{", "}", "*", ";", ":", ",", "~", "/", "+", "!"), " ", $text);
    if ($debug)
        echo "4 - " . $text . "\n";
    $text = preg_replace('/\s{2,}/',' ', $text);
    if ($debug)
        echo "5 - " . $text . "\n";
    return $text;
}

/**
 * @param string    $text
 * @param bool      $includeArticles
 *
 * @return mixed|string
 */
function normalizeString($text, $includeArticles=false)
{
    $text = strtolower($text);
    if($includeArticles)
        $text = preg_replace('/\b(a|an|the)\b/i', ' ', $text);
    $text = str_replace(array(".", "_", '-', "|", "<", ">", '"', "=", "~", '[', "]", "(", ")", "{", "}", "*", ";", ":", ",", "~", "/", "+", "'s "), " ", $text);
    $text = str_ireplace(' vol ', ' Volume ', $text);
    $text = str_ireplace('&', 'and', $text);
    $text = preg_replace('/\s{2,}/',' ', $text);
    $text = trim($text);

    return $text;
}

/**
 * @param array     $relArtist          array containing artist results from MB
 * @param array     $query              Array containing values to compare against
 * @param bool      $skipVariousCheck   Defaults to false, skip check for Various Artists
 * @param int|float $weight             Precalculated weight to add to percentmatch
 *
 * @return array|bool
 */
function checkArtistName($relArtist, $query, $skipVariousCheck=false, $weight=0)
{
    $queryText = '';
    if(DEBUG_ECHO)
        echo "Checking artist: " . $relArtist->name . "\n";
    $percentMatch = 0;
    $artistArr = array();
    $artistFound = false;
    if($relArtist->name === '[unknown]')
        return false;
    elseif($relArtist->name == 'Various Artists' && !$skipVariousCheck)
    {
        $artistArr['name'] = 'Various Artists';
        $artistArr['id'] = '89ad4ac3-39f7-470e-963a-56509c546377';
        $artistFound = 'Various Artists';
        $queryText = $query[0];
    }

    foreach($query as $stringToMatch)
    {
        $queryText = $stringToMatch;
        if (preg_match('/\b' . normalizeString($relArtist->name) . '\b/i', $stringToMatch) === 0 &&
            preg_match('/\b' . normalizeString($relArtist->name, true) . '\b/i', $stringToMatch) === 0)
        {
            if (preg_match('/\b' . trim(str_ireplace('Group', '', normalizeString($relArtist->name, true))) . '\b/i', $stringToMatch) === 1)
            {
                $artistFound = trim(str_ireplace('Group', '', normalizeString($relArtist->name, true)));
                break;
            }
            elseif (isset($relArtist->{'sort-name'}) && preg_match('/\b' . normalizeString($relArtist->{'sort-name'}) . '\b/i', $stringToMatch) === 1)
            {
                $artistFound = normalizeString($relArtist->{'sort-name'});
                break;
            }
            else
            {
                if (DEBUG_ECHO)
                    echo "Artist name not matched: " . $relArtist->name . " (weight = $weight)\n";
                if (isset($relArtist->{'alias-list'}))
                {
                    if (DEBUG_ECHO)
                        echo "Checking aliases...\n";
                    foreach ($relArtist->{'alias-list'}->alias as $alias)
                    {
                        if (is_array($alias))
                        {
                            if (DEBUG_ECHO)
                                echo "\nAlias is an array\n";
                            foreach ($alias as $aliasName)
                            {
                                if (isset($aliasName['locale']) && $aliasName->attributes()->locale == 'ja')
                                    continue;
                                if (preg_match('/\b' . normalizeString($aliasName) . '\b/i', $stringToMatch) === 0)
                                {
                                    if (DEBUG_ECHO)
                                        echo "Alias did not match: " . $aliasName . " (weight = $weight)\n";
                                    continue;
                                }
                                else
                                {
                                    // if(DEBUG_ECHO)
                                    $artistFound = normalizeString($aliasName);
                                    break;
                                }
                            }
                            if ($artistFound)
                                break;
                        }
                        else
                        {
                            if (isset($alias['locale']) && $alias->attributes()->locale == 'ja')
                                continue;
                            if (preg_match('/\b' . normalizeString($alias) . '\b/i', $stringToMatch) === 0)
                            {
                                if (DEBUG_ECHO)
                                    echo "Alias did not match: " . $alias . " (weight = $weight)\n";
                                continue;
                            }
                            else
                            {
                                if (DEBUG_ECHO)
                                    echo "Alias matched: " . $alias . " (weight = $weight)\n";
                                $artistFound = normalizeString($alias);
                                break;
                            }
                        }
                        if($artistFound)
                            break;
                    }
                }
                if ($artistFound)
                    break;
            }
            if ($artistFound)
                break;
        }
        else
        {
            $artistFound = normalizeString($relArtist->name);
            break;
        }
    }

    if($artistFound)
    {

        $artistArr['name'] = $relArtist->name;
        $artistArr['id'] = $relArtist->attributes()->id;
        $artistArr['matchString'] = $artistFound;
        similar_text($queryText, normalizeString($relArtist->name), $percentMatch);
        $artistArr['percentMatch'] = $percentMatch + $weight;
        if (DEBUG_ECHO)
            echo "Artist name matched: " . $artistArr['name'] . " (percentMatch = ".$artistArr['percentMatch']. ")\n";
    }

    if($artistFound && $artistArr['percentMatch'] > 15)
        return $artistArr;
    elseif($artistFound && $artistArr['percentMatch'] > 0 && $artistArr['percentMatch'] <= 15 && DEBUG_ECHO)
        echo "Artist percent match not acceptable: " . $artistArr['percentMatch'] . "\n";

    return false;
}

/**
 * @param $releaseName
 *
 * @return mixed
 */
function isSingle($releaseName)
{
    if(empty($releaseName) || $releaseName == null)
        return false;

    // Remove years and file/part counts
    $releaseName = trim(preg_replace('/\(?\[?19\d\d\]?\)?|\(?\[?20\d\d\]?\)?|\(?\[?\d\d?\/\d\d?\]?\)?/', '', $releaseName));
    // Perform some very basic cleaning on the release name before matching
    $releaseName = trim(preg_replace('/by req:? |attn:? [\w\s_]+| 320 | EAC/i', '', $releaseName));
    // Normalize spacing
    $releaseName = trim(preg_replace('/\s{2,}/', ' ', $releaseName));

    echo "Cleaned Single Release Name: " . $releaseName . "\n";

    // If it's a blatantly obvious 'various artist' release, use the following pattern
    if(substr($releaseName, 0, 2) == 'VA')
        preg_match('/VA ?- ?(?P<release>[\w\s\' ]+?)- ?(19\d\d|20\d\d)? ?-?(?![\(\[ ](19\d\d|20\d\d))(?P<track> ?(?!\(|\[|19\d\d|20\d\d)[0-2][0-9]\d?\d?) ?- ?(?P<artist>[\w\s\'\.]+?) ?- ?(?P<title>[\(\)\w _\']+)\.(?:mp3|wav|ogg|wma|mpa|aac|m4a|flac)/', $releaseName, $matches);
    else
    {
        // The 'track' group will not match tracks numbered above 19 to prevent matching a year
        // Probably won't be much of an issue because track numbers that high are rare.
        // The alternative is the regex would be much more strict in what would be identified as a track number.
        preg_match('/(?:^|["\- ])(?P<artist>[\w\s\'_]+)[ \-]*?[ \-]*?(?P<release>[\w\s\'_\(\)\-\d]+)[ \-"]*?(?P<track> ?(?!\(|\[|19\d\d|20\d\d)[0-2][0-9]\d?\d?).+?(?!-)(?P<title>[\(\)\w _\']+)\.(?:mp3|wav|ogg|wma|mpa|aac|m4a|flac)/i', $releaseName, $matches);
    }
    if(!isset($matches[0]) || (!isset($matches['artist']) && !isset($matches['release']) && !isset($matches['track']) && !isset($matches['title'])))
        preg_match('/(?P<track>["\- ](?!\(|\[|19\d\d|20\d\d)[0-2][0-9]\d?\d?)(?<!\(|\[|19\d\d|20\d\d)(?P<artist>( |-).+-)* ?-? ?(?P<title>[\(\)\w \-_\']+)\.(?:mp3|wav|ogg|wma|mpa|aac|m4a|flac)/i', $releaseName, $matches);

    if (!isset($matches[0]))
        preg_match('/("|-) ?"?(?P<artist> ?.+-)* ?-? ?(?P<title>[\(\)\w \-_\']+)\.(?:mp3|wav|ogg|wma|mpa|aac|m4a|flac)/i', $releaseName, $matches);

    if(!isset($matches[0]))
        return false;

    if(isset($matches['artist']))
    {
        $matches['artist'] = trim(str_ireplace(array('-', '_', '"'), ' ', $matches['artist']));
        $matches['artist'] = preg_replace('/\s{2,}/', ' ', $matches['artist']);
        if(preg_match('/^\d+$/', $matches['artist']))
            return false;
    }

    if(isset($matches['release']))
    {
        $matches['release'] = trim(str_ireplace(array('-', '_', '"'), ' ', $matches['release']));
        $matches['release'] = trim(preg_replace('/- ?\([\w\d\s]+\) ?-/', '', $matches['release']));
        $matches['release'] = trim(preg_replace('/\s{2,}/', ' ', $matches['release']));
    }

    if(isset($matches['title']))
    {
        $matches['title'] = trim(str_ireplace(array('-', '_', '"'), ' ', $matches['title']));
        $matches['title'] = trim(preg_replace('/\s{2,}/', ' ', $matches['title']));
    }
    if(isset($matches['track']))
        $matches['track'] = trim(str_ireplace(array('"', ' ', '-'), '', $matches['track']));

    if(isset($matches['track']) && strlen($matches['track']) > 2)
    {
        $matches['disc'] = strlen($matches['track']) > 3 ? substr($matches['track'], 0, 2) : substr($matches['track'], 0, 1);
        $matches['track'] = strlen($matches['track']) > 3 ? substr($matches['track'], 2, 2) : substr($matches['track'], 1, 2);
    }


    return (isset($matches['artist']) && isset($matches['title'])) ? $matches : false;
}