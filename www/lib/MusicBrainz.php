<?php
/**
 * Project: nZEDbetter
 * User: Randy
 * Date: 9/7/13
 * Time: 2:00 PM
 * File: MusicBrainz.php
 *
 * Class for retrieving music info from a MusicBrainz replication server.  To configure your own
 * replication server, see http://nzedbetter.org/index.php?title=MusicBrainz
 *
 * It is STRONGLY recommended that you configure your own replication server if
 * you plan to index music binaries and utilize the MusicBrainz integration.
 *
 * NOTE: All http requests to musicbrainz are in compliance with the MusicBrainz
 * terms of service, provided that the code below has not been altered from the
 * author's original work.  For the current release version of nZEDbetter, please visit
 * https://github.com/KurzonDax/nZEDbetter
 *
 */

require_once(WWW_DIR . "lib/site.php");
require_once(WWW_DIR . "lib/framework/db.php");
require_once(WWW_DIR . "lib/releaseimage.php");
require_once(WWW_DIR . "lib/amazon.php");
require_once(WWW_DIR . "lib/MusicBrainz/mb_base.php");
require_once(WWW_DIR . "lib/MusicBrainz/mbArtist.php");
require_once(WWW_DIR . "lib/MusicBrainz/mbRelease.php");
require_once(WWW_DIR . "lib/MusicBrainz/mbTrack.php");
require_once(WWW_DIR . "lib/namecleaning.php");

/**
 * Class MusicBrainz
 */
class MusicBrainz {

    const POST = 'post';
    const GET = 'get';
    const HEAD = 'head';
    const API_VERSION = '2';
    const API_SCHEME = "http://";
    const SKIP_COVER_CREATION = false;
    const COVER_ART_BASE_URL = "http://coverartarchive.org/release/";
    const WRITE_LOG_FILES = true;

    const DEBUG_NONE = 0;
    const DEBUG_MIN = 1;
    const DEBUG_MED = 3;
    const DEBUG_MAX = 5;

    const DEBUG_MODE = MusicBrainz::DEBUG_NONE;
    /**
     * @var string
     */
    private $_MBserver = '';
    /**
     * @var bool
     */
    private $_throttleRequests = false;
    /**
     * @var string
     */
    private $_applicationName = 'nZEDbetter';
    /**
     * @var string
     */
    private $_applicationVersion = '';
    /**
     * @var null
     */
    private $_email = null;
    /**
     * @var string
     */
    private $_imageSavePath = '';
    /**
     * @var bool
     */
    private $_isAmazonValid = false;
    /**
     * @var string
     */
    private $_amazonPublicKey = '';
    /**
     * @var string
     */
    private $_amazonPrivateKey = '';
    /**
     * @var string
     */
    private $_amazonTag = '';
    /**
     * @var string
     */
    private $_baseLogPath = '';
    /**
     * @var int
     */
    private $_threads = 0;

    /**
     * @throws MBException      exception thrown if search server URL contains musicbrainz.org
     *                          and no valid email address has been configured in site settings.
     *
     * NOTE: All requests to musicbrainz are in compliance with the MusicBrainz terms
     * of service, provided that the code below has not been altered from the author's
     * original work.  For the current release version of nZEDbetter, please visit
     * https://github.com/KurzonDax/nZEDbetter
     */
    public function __construct()
    {
        if (MusicBrainz::DEBUG_MODE >= MusicBrainz::DEBUG_MIN)
            echo "Starting " . __METHOD__ . "\n";

        $s = new Sites();
        $site = $s->get();
        $this->_MBserver = (!empty($site->musicBrainzServer)) ? $site->musicBrainzServer : "musicbrainz.org";
        $this->_email = !empty($site->email) ? $site->email : null;
        $this->_applicationVersion = $site->NZEDBETTER_VERSION;
        $this->_imageSavePath = WWW_DIR . "covers/music/";
        $this->_amazonPrivateKey = !empty($site->amazonprivkey) ? $site->amazonprivkey : '';
        $this->_amazonPublicKey = !empty($site->amazonpubkey) ? $site->amazonpubkey : '';
        $this->_amazonTag = !empty($site->amazonassociatetag) ? $site->amazonassociatetag : '';
        $this->_threads = !empty($site->postthreadsamazon) ? $site->postthreadsamazon : 1;

        if($this->_amazonPrivateKey != '' && $this->_amazonPublicKey != '' && $this->_amazonTag != '')
            $this->_isAmazonValid = true;

        if(stripos($this->_MBserver, 'musicbrainz.org') === false)
        {
            $this->_throttleRequests = false;
        }
        else
        {
            $this->_throttleRequests = true;
            if(is_null($this->_email) || empty($this->_email) ||
            preg_match('/[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+(?:[A-Z]{2}|com|org|net|gov|mil|biz|info|mobi|name|aero|jobs|museum)\b/i', $this->_email) === 0)
            {
                echo "\n\033[01;31mALERT!!! You have not set a valid email address in Admin->Site Settings.\n";
                echo "The MusicBrainz integration will not function until this is corrected.\n\n";
                throw new MBException('Invalid email address. Halting MusicBrainz Integration.');
            }
        }

        if(MusicBrainz::WRITE_LOG_FILES)
        {
            $this->_baseLogPath = WWW_DIR . "lib/logging/musicBrainz/";
            if(!is_dir($this->_baseLogPath))
                mkdir($this->_baseLogPath, 0777, true);
        }

    }

    /**
     * @param string $searchFunction
     * @param string $field
     * @param string $query
     * @param int    $limit
     *
     * @return bool|SimpleXMLElement
     */
    private function __makeSearchCall($searchFunction, $field = '' , $query = '', $limit=10)
    {

        if (MusicBrainz::DEBUG_MODE >= MusicBrainz::DEBUG_MIN)
            echo "Starting " . __METHOD__ . "\nField: " . $field . "\nQuery: " . $query . "\n";
        $url = MusicBrainz::API_SCHEME . $this->_MBserver . '/ws/' . MusicBrainz::API_VERSION . '/' . $searchFunction . '/?query=' . ($field=='' ? '' : $field . ':') . urlencode($query) . "&limit=" . $limit;

        if(MusicBrainz::WRITE_LOG_FILES)
            file_put_contents($this->_baseLogPath . "urls.log", $url . "\n", FILE_APPEND);

        return $this->__getResponse($url);

    }

    /**
     * @param string    $entity   string literal: artist, label, recording, release, release-group, work, area, url
     * @param string    $mbid
     *
     * @return bool|SimpleXMLElement
     */
    public function musicBrainzLookup($entity, $mbid)
    {
        if (MusicBrainz::DEBUG_MODE >= MusicBrainz::DEBUG_MIN)
            echo "Starting " . __METHOD__ . "\n";
        // $entity must be one of the following:
        // artist, label, recording, release, release-group, work, area, url
        if(is_null($entity) || empty($entity) || is_null($mbid) || empty($mbid))
            return false;

        $validEntities = array('artist', 'label', 'recording', 'release', 'release-group', 'work', 'area', 'url');

        if(!in_array($entity, $validEntities))
            return false;

        switch ($entity)
        {
            case 'artist':
                $incParams = '?inc=ratings+tags';
                break;
            case 'release':
                $incParams = '?inc=ratings+tags+release-groups+mediums+release-rels';
                break;
            case 'recording':
                $incParams = '?inc=ratings+tags+artists+releases';
                break;
            default:
                $incParams = '';
        }

        $url = MusicBrainz::API_SCHEME . $this->_MBserver . '/ws/' . MusicBrainz::API_VERSION . '/' . $entity . '/' . $mbid . $incParams;

        return $this->__getResponse($url);

    }

    /**
     * @param string $query
     * @param string $field     defaults to blank
     * @param int    $limit     defaults to 10, max number of results to return
     *
     * @return bool|SimpleXMLElement
     */
    private function __searchArtist($query, $field='', $limit=10)
    {
        /*  FIELD       DESCRIPTION
         *  area		artist area
            beginarea	artist begin area
            endarea		artist end area
            arid		MBID of the artist
            artist		name of the artist
            artistaccent	 name of the artist with any accent characters retained
            alias		the aliases/misspellings for the artist
            begin		artist birth date/band founding date
            comment		artist comment to differentiate similar artists
            country		the two letter country code for the artist country or 'unknown'
            end			artist death date/band dissolution date
            ended		true if know ended even if do not know end date
            gender		gender of the artist (“male”, “female”, “other”)
            ipi			IPI code for the artist
            sortname	artist sortname
            tag			a tag applied to the artist
            type		artist type (“person”, “group”, "other" or “unknown”)
         *
         */
        if (MusicBrainz::DEBUG_MODE >= MusicBrainz::DEBUG_MIN)
            echo "Starting " . __METHOD__ . "  Query: " . $query . "\n";
        if(empty($query) || is_null($query))
            return false;
        else
            return $this->__makeSearchCall('artist', $field, $query, $limit);

    }

    /**
     * @param string $query
     * @param string $field     defaults to 'title'
     * @param int    $limit     defaults to 10, max number of results to return
     *
     * @return bool|SimpleXMLElement
     */
    private function __searchCDstubs($query, $field='title',$limit=10)
    {

        /*
         *  FIELD       DESCRIPTION
         *  artist		artist name
            title		release name
            barcode		release barcode
            comment		general comments about the release
            tracks		number of tracks on the CD stub
            discid		disc ID of the CD
         *
         */
        if(empty($query) || is_null($query))
            return false;
        else
            return $this->__makeSearchCall('cdstub', $field, $query, $limit);

    }

    /**
     * @param string $query
     * @param string $field     defaults to blank
     * @param int    $limit     defaults to 10, max number of results to return
     *
     * @return bool|SimpleXMLElement
     */
    private function __searchLabel($query, $field='',$limit=10)
    {

        /*
         *
         *  FIELD		DESCRIPTION
            alias		the aliases/misspellings for this label
            area		label area
            begin		label founding date
            code		label code (only the figures part, i.e. without "LC")
            comment		label comment to differentiate similar labels
            country		The two letter country code of the label country
            end			label dissolution date
            ended		true if know ended even if do not know end date
            ipi			ipi
            label		label name
            labelaccent	name of the label with any accent characters retained
            laid		MBID of the label
            sortname	label sortname
            type		label type
            tag			folksonomy tag
         *
         */

        if(empty($query) || is_null($query))
            return false;
        else
            return $this->__makeSearchCall('label', $field, $query, $limit);

    }

    /**
     * @param string $query1
     * @param string $field1    defaults to 'recording', first field to search in query
     * @param string $query2
     * @param string $field2    defaults to 'artist', second field to search to narrow results
     * @param string $query3
     * @param string $field3    defaults to 'release' thrid field to search to narrow results
     * @param int    $limit     defaults to 30, max number of results to return
     *
     * @return bool|SimpleXMLElement
     */
    private function __searchRecording($query1, $field1='recording', $query2='', $field2='artist', $query3='', $field3='release',  $limit=30)
    {

        /*
         *
         *  Field			Description
            arid 			artist id
            artist 			artist name is name(s) as it appears on the recording
            artistname 		an artist on the recording, each artist added as a separate field
            creditname 		name credit on the recording, each artist added as a separate field
            comment 		recording disambiguation comment
            country 		recording release country
            date 			recording release date
            dur 			duration of track in milliseconds
            format 			recording release format
            isrc			ISRC of recording
            number 			free text track number
            position 		the medium that the recording should be found on, first medium is position 1
            primarytype 	primary type of the release group (album, single, ep, other)
            puid			PUID of recording
            qdur 			quantized duration (duration / 2000)
            recording 		name of recording or a track associated with the recording
            recordingaccent name of the recording with any accent characters retained
            reid 			release id
            release 		release name
            rgid 			release group id
            rid 			recording id
            secondarytype 	secondary type of the release group (audiobook, compilation, interview, live, remix soundtrack, spokenword)
            status			Release status (official, promotion, Bootleg, Pseudo-Release)
            tid 			track id
            tnum 			track number on medium
            tracks 			number of tracks in the medium on release
            tracksrelease 	number of tracks on release as a whole
            tag 			folksonomy tag
            type 			type of the release group, old type mapping for when we did not have separate primary and secondary types
         *
         */
        if (MusicBrainz::DEBUG_MODE >= MusicBrainz::DEBUG_MIN)
            echo "Starting " . __METHOD__ . "\nQuery1: " . $query1 . "\nQuery2: " . $query2 . "\n";

        if(empty($query1) || is_null($query1))
            return false;
        else
        {
            // $query = $query1.(($query2 !== '') ? ' AND '.$field2.':'.$query2 : '');
            $query = '"' . $query1 . '"';
            $query .= ($query2 !== '' ? ' AND ' . $field2 . ':"' . $query2 . '"' : '');
            $query .= ($query3 !== '' ? ' AND ' . $field3 . ':"' . $query3 . '"' : '');
            return $this->__makeSearchCall('recording', $field1, $query, $limit);
        }
    }

    /**
     * @param string $query
     * @param string $field     defaults to 'releasegroup'
     * @param int    $limit     defaults to 10, max number of results to return
     *
     * @return bool|SimpleXMLElement
     */
    private function __searchReleaseGroup($query, $field='releasegroup',$limit=10)
    {
        /*
         *
         *  Field 				Description
            arid 				MBID of the release group’s artist
            artist 				release group artist as it appears on the cover (Artist Credit)
            artistname 			“real name” of any artist that is included in the release group’s artist credit
            comment 			release group comment to differentiate similar release groups
            creditname 			name of any artist in multi-artist credits, as it appears on the cover.
            primarytype 		primary type of the release group (album, single, ep, other)
            rgid 				MBID of the release group
            releasegroup 		name of the release group
            releasegroupaccent 	name of the releasegroup with any accent characters retained
            releases 			number of releases in this release group
            release 			name of a release that appears in the release group
            reid 				MBID of a release that appears in the release group
            secondarytype 		secondary type of the release group (audiobook, compilation, interview, live, remix soundtrack, spokenword)
            status 				status of a release that appears within the release group
            tag 				a tag that appears on the release group
            type 				type of the release group, old type mapping for when we did not have separate primary and secondary types
         *
         */
        if(empty($query) || is_null($query))
            return false;
        else
            return $this->__makeSearchCall('release-group', $field, $query, $limit);
    }

    /**
     * @param string $query1
     * @param string $field1    defaults to 'release'
     * @param string $query2
     * @param string $field2    defaults to 'artistname', used to narrow results
     * @param int    $limit     defaults to 10, max number of results to return
     *
     * @return bool|SimpleXMLElement
     */
    private function __searchRelease($query1, $field1='release', $query2='', $field2='artistname',$limit=10)
    {
        /*
         *
         *
         *field 			Description
            arid 			artist id
            artist 			complete artist name(s) as it appears on the release
            artistname 		an artist on the release, each artist added as a separate field
            asin 			the Amazon ASIN for this release
            barcode 		The barcode of this release
            catno 			The catalog number for this release, can have multiples when major using an imprint
            comment 		Disambiguation comment
            country 		The two letter country code for the release country
            creditname 		name credit on the release, each artist added as a separate field
            date 			The release date (format: YYYY-MM-DD)
            discids 		total number of cd ids over all mediums for the release
            discidsmedium 	number of cd ids for the release on a medium in the release
            format 			release format
            laid 			The label id for this release, a release can have multiples when major using an imprint
            label 			The name of the label for this release, can have multiples when major using an imprint
            lang 			The language for this release. Use the three character ISO 639 codes to search for a specific language. (e.g. lang:eng)
            mediums 		number of mediums in the release
            primarytype 	primary type of the release group (album, single, ep, other)
            puid 			The release contains recordings with these puids
            reid 			release id
            release 		release name
            releaseaccent 	name of the release with any accent characters retained
            rgid 			release group id
            script 			The 4 character script code (e.g. latn) used for this release
            secondarytype 	secondary type of the release group (audiobook, compilation, interview, live, remix, soundtrack, spokenword)
            status 			release status (e.g official)
            tag 			a tag that appears on the release
            tracks 			total number of tracks over all mediums on the release
            tracksmedium 	number of tracks on a medium in the release
            type 			type of the release group, old type mapping for when we did not have separate primary and secondary types
         *
         *
         */
        if (MusicBrainz::DEBUG_MODE >= MusicBrainz::DEBUG_MIN)
            echo "Starting " . __METHOD__ . "\nQuery1: " . $query1 . "\nQuery2: " . $query2 . "\n";

        if(empty($query1) || is_null($query1))
            return false;
        else
        {
            // $query=$query1.(($query2 != '') ? " AND ".$field2.":".$query2 : '');
            $query = '"' . $query1 . '"';
            $query .= ($query2 !== '' ? ' AND ' . $field2 . ':"' . $query2 . '"' : '');
            return $this->__makeSearchCall('release', $field1, $query, $limit);
        }
    }

    /**
     * @param string    $query
     * @param string    $field      defaults to 'work'
     * @param int       $limit      defaults to 10, max number of results to return
     *
     * @return bool|mixed
     */
    private function __searchWork($query, $field='work',$limit=10)
    {
        /*
         *
         *  Field           Description
            alias 			the aliases/misspellings for this work
            arid 			artist id
            artist 			artist name, an artist in the context of a work is an artist-work relation such as composer or performer
            comment 		disambiguation comment
            iswc 			ISWC of work
            lang 			Lyrics language of work
            tag 			folksonomy tag
            type 			work type
            wid 			work id
            work 			name of work
            workaccent 		name of the work with any accent characters retained
         *
         */

        if(empty($query) || is_null($query))
            return false;
        else
            return $this->__makeSearchCall('work', $field, $query, $limit);

    }

    /**
     * @param array  $musicRow     associative array containing ID, name, searchname
     *
     * @return bool
     */
    public function processMusicRelease($musicRow)
    {
        if (MusicBrainz::DEBUG_MODE >= MusicBrainz::DEBUG_MIN)
            echo "Starting " . __METHOD__ . "\n";

        $nameCleaning = new nameCleaning();
        $db = new DB();

        $failureType = '';
        $n = "\n\033[01;37m";

        if (preg_match('/bootleg/i', $musicRow['name']) === 1)
        {
            echo "\033[01;30mSkipping bootleg release: " . $musicRow['name'] . $n;
            $db->query("UPDATE releases SET musicinfoID=-9 WHERE ID=" . $musicRow['ID']);
            return true;
        }
        elseif (preg_match('/\.jpg|\.png/i' , $musicRow['name']) === 1)
        {
            echo "\033[01;30mSkipping non-music release: " . $musicRow['name'] . $n;
            $db->query("UPDATE releases SET musicinfoID=-10 WHERE ID=" . $musicRow['ID']);

            return true;
        }

        $cleanSearchName = $nameCleaning->musicCleaner($musicRow['searchname']);
        $query = $this->cleanQuery($cleanSearchName);
        $year = false;
        if (preg_match('/\(?(19|20)\d\d\)?(?!.+(19|20)\d\d)(?!kbps|x)/', $musicRow['searchname'], $yearMatch) === 0)
            preg_match('/\(?(19|20)\d\d\)?(?!.+(19|20)\d\d)(?!kbps|x)/', $musicRow['name'], $yearMatch);
        if(isset($yearMatch))
        {
            $year = 0;
            foreach($yearMatch as $yearFound)
                $year = $yearFound > $year ? $yearFound : $year;

            $year = $year == 0 ? false : $year;
        }
        $artistSearchArray[] = $musicRow['name'];
        $artistSearchArray[] = $this->__normalizeString($musicRow['name']);
        $artistSearchArray[] = $this->__normalizeString($musicRow['name'], true);
        $artistSearchArray[] = $musicRow['searchname'];
        $artistSearchArray[] = $this->__normalizeString($musicRow['searchname']);
        $artistSearchArray[] = $this->__normalizeString($musicRow['searchname'], true);

        $isSingle = $this->isTrack($musicRow['name']);

        if (!$isSingle)
        {
            $parsedRelease = $this->releaseParser($musicRow['name']);
            if($parsedRelease !== false)
            {
                echo "Looking up album: \033[01;36m" . $parsedRelease['title'] . "\033[01;37m   Artist: \033[01;36m" . $parsedRelease['artist'] . $n;
                $artistResult = $this->findArtist($parsedRelease['artist'], $artistSearchArray);
                if ($artistResult)
                {
                    $releaseSearchArr = array();
                    $releaseSearchArr = $this->__buildReleaseSearchArray($musicRow['name'], $releaseSearchArr);
                    $releaseSearchArr = $this->__buildReleaseSearchArray($musicRow['searchname'], $releaseSearchArr);


                    $albumResult = $this->findRelease($parsedRelease['title'], $artistResult, $releaseSearchArr, ($year !==false ? $year : null));
                    if ($albumResult)
                    {
                        if($this->updateArtist($artistResult) !== false)
                        {
                            if($this->updateAlbum($albumResult) !== false)
                            {
                                echo "\033[01;32mAdded/Updated Album: " . $albumResult->getTitle() . "  Artist: " . $artistResult->getName() . $n;
                                $db->queryDirect("UPDATE releases SET musicinfoID=99999999, mbAlbumID=" . $db->escapeString($albumResult->getMbID()) .
                                                    ", mbTrackID=NULL WHERE ID=" . $musicRow['ID']);
                                return true;
                            }
                            else
                                echo "\033[01;31mERROR: Encountered an error adding/updating album" . $n;
                        }
                        else
                            echo "\033[01;31mERROR: Encountered an error adding/updating artist" . $n;
                    }
                    else
                    {
                        echo "\033[01;33mUnable to match release: " . $musicRow['ID'] . "   " . $parsedRelease['title'] . $n;
                        $failureType = 'release-release';
                    }
                }
                else
                {
                    echo "\033[01;34mUnable to determine artist: " . $musicRow['ID'] . "   " . $parsedRelease['artist'] . $n;
                    $failureType = 'release-artist';
                }
            }
            else
            {
                echo "\033[01;31mUnable to parse release artist and title: " . $musicRow['name'] . $n;
                $failureType = 'release-regex';
            }
        }
        else
        {
            echo "Looking up track: \033[01;36m" . $isSingle['title'] . "\033[01;37m   Artist: \033[01;36m" . $isSingle['artist']  . "\033[01;37m" .
                (isset($isSingle['release']) ? "   Album: \033[01;36m" . $isSingle['release'] . "\033[01;37m\n" : "\n");
            $prefix = isset($isSingle['disc']) ? (string)$isSingle['disc'] . (string)$isSingle['track'] : $isSingle['track'];
            $query = preg_replace('/^' . $prefix . '/', '', $query);
            $isSingle['releaseID'] = $musicRow['ID'];
            $artistResult = $this->findArtist((isset($isSingle['artist']) ? $isSingle['artist'] : $query), $artistSearchArray);
            if ($artistResult)
            {
                if ($year !== false)
                    $isSingle['year'] = $year;

                $recordingResult = $this->findRecording($isSingle, $artistResult, (isset($isSingle['release']) ? true : false));
                if ($recordingResult)
                {
                    if($this->updateArtist($artistResult) !== false)
                    {
                        if($this->updateTrack($recordingResult['recording']) !== false)
                        {
                            if($recordingResult['release'] !== false)
                            {
                                if($this->updateAlbum($recordingResult['release']))
                                {
                                    echo "\033[01;32mAdded/Updated Album: " . $recordingResult['release']->getTitle() . "  Artist: " . $artistResult->getName() . $n;
                                    echo "\033[01;32mAdded/Update Track: " . $recordingResult['recording']->getTitle() . "  Artist: " . $artistResult->getName() . $n;
                                    $db->queryDirect("UPDATE releases SET musicinfoID=99999999, mbAlbumID=" . $db->escapeString($recordingResult['release']->getMbID) .
                                        ", mbTrackID=" . $db->escapeString($recordingResult['recording']->getMbID()) . " WHERE ID=" . $musicRow['ID']);
                                    return true;
                                }
                            }
                            else
                            {
                                echo "\033[01;32mAdded/Update Track: " . $recordingResult['recording']->getTitle() . "  Artist: " . $artistResult->getName() . $n;
                                $db->queryDirect("UPDATE releases SET musicinfoID=99999999, mbAlbumID=NULL" .
                                    ", mbTrackID=" . $db->escapeString($recordingResult['recording']->getMbID()) . " WHERE ID=" . $musicRow['ID']);
                                return true;
                            }
                        }
                        else
                            echo "\033[01;31mERROR: Encountered an error updating track" .$n;
                    }
                    else
                        echo "\033[01;31mERROR: Encountered an error updating artist" . $n;
                }
                else
                {
                    echo "\033[01;33mUnable to match single: " . $musicRow['ID'] . "   " . $isSingle['title'] . $n;
                    $failureType = 'track-track';
                }
            }
            else
            {
                echo "\033[01;34mUnable to match artist: " . $musicRow['ID'] . "   " . $isSingle['artist'] . $n;
                $failureType = 'track-artist';
            }
        }

        switch ($failureType)
        {
            case 'release-release':
                $failureCode = '-1';
                break;
            case 'release-artist':
                $failureCode = '-2';
                break;
            case 'track-track':
                $failureCode = '-3';
                break;
            case 'track-artist':
                $failureCode = '-4';
                break;
            case 'release-regex':
                $failureCode = '-5';
                break;
            default:
                $failureType = 'process';
                $failureCode = '-6';
        }

        $db->query("UPDATE releases SET musicinfoID=" . $failureCode . " WHERE ID=" . $musicRow['ID']);

        if(MusicBrainz::WRITE_LOG_FILES)
        {
            $log = $musicRow['ID'] . '|' . $musicRow['name'] . '|' . $musicRow['searchname'];
            if($failureCode == '-1' || $failureCode == '-2')
                $log .= '|' . $parsedRelease['title'] . '|' . $parsedRelease['artist'];
            if($failureCode == '-3' || $failureCode == '-4')
            {
                $log .= $isSingle['title'] . '|' . $isSingle['artist'];
                $log .= (isset($isSingle['release']) ? '|' . $isSingle['release'] : '');
            }
            $log .= (isset($year) && $year !== false ? '|' . $year : '');
            $log .= "\n";
            file_put_contents($this->_baseLogPath . $failureType . '-noMatch.csv', $log, FILE_APPEND);
        }

        return false;
    }

    /**
     * @param string       $query       Search string to be sent to MusicBrainz
     * @param string|array $searchArray String or array of strings that results should be matched against
     *
     * @return mbArtist|bool
     */
    public function findArtist($query, $searchArray)
    {
        if (MusicBrainz::DEBUG_MODE >= MusicBrainz::DEBUG_MIN)
            echo "Starting " . __METHOD__ . "  Query: " . $query . "\n";

        $mbArtist = new mbArtist();
        $foundArtist = false;

        if (!is_array($searchArray))
        {
            $temp = $searchArray;
            unset($searchArray);
            $searchArray = array();
            $searchArray[] = $temp;
            $searchArray[] = $this->__normalizeString($temp);
            $searchArray[] = $this->__normalizeString($temp, true);
        }

        $wordCount = count(explode(' ', $query));
        if ($query == 'VA')
        {
            $mbArtist->setName('Various Artists');
            $mbArtist->setMbID('89ad4ac3-39f7-470e-963a-56509c546377');
            $mbArtist->setMatchString('VA');
            $mbArtist->setPercentMatch(100);
            return $mbArtist;
        }

        $results = $this->__searchArtist($query, '', 50);

        if (MusicBrainz::DEBUG_MODE >= MusicBrainz::DEBUG_MAX)
            print_r($results);

        $resultsAttr = isset($results->{'artist-list'}) ? $results->{'artist-list'}->attributes() : array();
        if (isset($resultsAttr['count']) && $resultsAttr['count'] == '0')
        {
            if (MusicBrainz::DEBUG_MODE >= MusicBrainz::DEBUG_MIN)
                echo "Artist name search returned no results\n";
            return false;
        }
        elseif (!isset($resultsAttr['count']))
        {
            if(MusicBrainz::DEBUG_MODE >= MusicBrainz::DEBUG_MAX)
                print_r($results);
            return false;
        }
        elseif (MusicBrainz::DEBUG_MODE)
            echo "Artists Found: " . $resultsAttr['count'] . "\n";

        $percentMatch = -1000;

        $i = 0;
        foreach ($results->{'artist-list'}->artist as $artist)
        {

            $artistCheck = $this->__checkArtistName($artist, $searchArray, false, (((30 - $i) / 30) * 10));
            if ($artistCheck && $artistCheck->getPercentMatch() > $percentMatch)
            {
                // The following helps to prevent single-word artists from matching an artist
                // with a similar full name (i.e Pink should not match Pink Floyd)
                // Obviously only works if the query string is three words or less
                if ($wordCount < 4 && count(explode(' ', $artistCheck->getName())) != $wordCount)
                {
                    if (MusicBrainz::DEBUG_MODE >= MusicBrainz::DEBUG_MED)
                        echo "Matching artist name too short: " . $artistCheck->getName() . "\n";
                    continue;
                }
                $mbArtist = $artistCheck->getMbID();
                $percentMatch = $artistCheck->getPercentMatch();
                $foundArtist = true;
            }
            $i++;
        }
        $mbArtist = $foundArtist === true ? $this->__getArtistDetails($mbArtist) : false;

        return $foundArtist === true ? $mbArtist : false;
    }

    /**
     * @param string       $query       searchname after musicCleaner and cleanQuery
     * @param mbArtist     $artist      mbArtist or false
     * @param array        $searchArray array of strings to compare results against
     * @param integer|null $year        Year of release
     *
     * @return mbRelease|bool
     *
     * NOTE: If an artist is provided, better results will be obtained if the artist
     * name is removed from the $query string
     */
    public function findRelease($query, mbArtist $artist, $searchArray, $year = null)
    {
        // enforce artist requirement
        // check all occurrences of $searchArray, fix $searchNameArr to use $searchArray
        if (MusicBrainz::DEBUG_MODE >= MusicBrainz::DEBUG_MIN)
            echo "Starting " . __METHOD__ . "Query: " . $query . "\n";

        $query = $this->__normalizeString($query);

        $percentMatch = 0;

        $mbArtist = new mbArtist();
        $matchedRelease = array();


        $foundRelease = false;

        if ($artist === false)
        {
            $results = $this->__searchRelease($query, 'release', '', '', 30);
        }
        else
        {
            $results = $this->__searchRelease($query, 'release', $this->__normalizeString($artist->getName()), 'artistname');
        }
        if (!isset($results->{'release-list'}->attributes()->count))
        {
            if (MusicBrainz::DEBUG_MODE >= MusicBrainz::DEBUG_MAX)
                print_r($results);
            return false;
        }
        if ($results->{'release-list'}->attributes()->count == '0')
        {
            if (MusicBrainz::DEBUG_MODE >= MusicBrainz::DEBUG_MIN)
                echo "Release name search returned no results\n";

            return false;
        }
        else
            if (MusicBrainz::DEBUG_MODE)
                echo "Releases Found: " . $results->{'release-list'}->attributes()->count . "\n";

        if ($results->{'release-list'}->attributes()->count == '1')
        {
            $matchFound = false;
            foreach ($searchArray as $searchName)
            {
                if (stripos($searchName, $this->__normalizeString($results->{'release-list'}->release->title)) === false &&
                    stripos($this->__normalizeString($searchName, true), $this->__normalizeString($results->{'release-list'}->release->title, true)) === false)
                    continue;
                else
                {
                    $matchFound = true;
                    break;
                }
            }
            if (!$matchFound)
            {
                if (MusicBrainz::DEBUG_MODE >= MusicBrainz::DEBUG_MED)
                    echo "Non-matching release: " . $results->{'release-list'}->release->title . "\n";

                return false;
            }
            else
            {
                $matchedRelease['id'] = $results->{'release-list'}->release->attributes()->id;
                $matchedRelease['percentMatch'] = 100;
                $matchedRelease['artistID'] = $artist->getMbID();
                $foundRelease = true;
            }
        }
        else // More than 1 release was found
        {
            foreach ($results->{'release-list'}->release as $release)
            {
                $matchFound = false;
                $matchedSearchName = '';
                foreach ($searchArray as $searchName)
                {
                    if (stripos($searchName, $this->__normalizeString($release->title)) === false &&
                        stripos($this->__normalizeString($searchName, true), $this->__normalizeString($release->title, true)) === false
                    )
                        continue;
                    else
                    {
                        $matchedSearchName = $searchName;
                        $matchFound = true;
                        break;
                    }
                }
                if (!$matchFound)
                {
                    if (MusicBrainz::DEBUG_MODE >= MusicBrainz::DEBUG_MED)
                        echo "Non-matching release: " . $release->title . "\n";
                    continue;
                }
                else
                {
                    similar_text($this->__normalizeString($release->title), $matchedSearchName, $tempMatch);
                    if (MusicBrainz::DEBUG_MODE >= MusicBrainz::DEBUG_MED)
                        echo "Checking release: " . $release->title . "\n";
                    if (!$artist && isset($release->{'artist-credit'}->{'name-credit'}))
                    {
                        $i = 0;  // Counter for position in list of artists
                        foreach ($release->{'artist-credit'}->{'name-credit'} as $relArtist)
                        {
                            if (isset($relArtist->name))
                                $mbArtist = $this->__checkArtistName($relArtist, $searchArray, false, (((30 - $i) / 30) * 10));
                            else
                                $mbArtist = $this->__checkArtistName($relArtist->artist, $searchArray, false, (((30 - $i) / 30) * 10));
                            if ($mbArtist && stripos($query, $this->__normalizeString($mbArtist->getName())) !== false)
                            {
                                $tempMatch += 25;
                                break;
                            }
                            else
                                $mbArtist = false;
                            $i++;
                        }
                        if (!$mbArtist)
                        {
                            if (MusicBrainz::DEBUG_MODE >= MusicBrainz::DEBUG_MED)
                                echo "No matching artist was found in the release.\n";
                            continue;
                        }
                        elseif ($mbArtist->getName() == 'Various Artists')
                            $tempMatch -= 15;
                    }
                    elseif($artist)
                        $mbArtist = $artist;
                    if ($this->__normalizeString($release->title, true) == $this->__normalizeString($mbArtist->getName(), true) &&
                        substr_count($query, $this->__normalizeString($mbArtist->getName(), true)) == 1)
                    {
                        if (MusicBrainz::DEBUG_MODE >= MusicBrainz::DEBUG_MED)
                            echo "Artist name and release title are the same, but not looking for self-titled release\n";
                        continue;
                    }
                    elseif (stripos(trim(preg_replace('/' . $this->__normalizeString($mbArtist->getName(), true) . '/', '', $this->__normalizeString($matchedSearchName, true), 1)),
                            trim($this->__normalizeString($release->title, true))) === false)
                    {
                        if (MusicBrainz::DEBUG_MODE >= MusicBrainz::DEBUG_MED)
                            echo "Title no longer matched after extracting artist's name.\n";
                        continue;
                    }
                    if (isset($release->date) && !is_null($year) && preg_match('/' . $year . '/', $release->date))
                        $tempMatch += 25;
                    elseif (isset($release->date) && !is_null($year))
                    {
                        preg_match('/(19|20)\d\d', $release->date, $relYear);
                        if (isset($relYear[0]) && ($relYear[0] == ($year - 1) || $relYear[0] == ($year + 1)))
                            $tempMatch += 20;
                    }
                    elseif (isset($release->{'release-event-list'}->{'release-event'}->date) && !is_null($year) && $release->{'release-event-list'}->{'release-event'}->date == $year)
                        $tempMatch += 25;
                    elseif (isset($release->{'release-event-list'}->{'release-event'}->date) && !is_null($year))
                    {
                        preg_match('/(19|20)\d\d', $release->{'release-event-list'}->{'release-event'}->date, $relYear);
                        if ($relYear[0] == ($year - 1) || $relYear[0] == ($year + 1))
                            $tempMatch += 20;
                    }
                    if (isset($release->{'medium-list'}->medium->format) && $release->{'medium-list'}->medium->format == 'CD')
                        $tempMatch += 15;
                    if (MusicBrainz::DEBUG_MODE)
                        echo "Matching release: " . $release->title . " tempMatch: " . $tempMatch . "\n";
                    if ($tempMatch > $percentMatch)
                    {
                        $matchedRelease['id'] = $release->attributes()->id;
                        $matchedRelease['percentMatch'] = $tempMatch;
                        $matchedRelease['artistID'] = $mbArtist->getMbID();
                        $percentMatch = $tempMatch;
                        $foundRelease = true;
                        unset($mbArtist);

                    }
                }
            }
        }
        $mbRelease = $foundRelease === true ? $this->__getReleaseDetails($matchedRelease['id'], $matchedRelease['artistID'], $matchedRelease['percentMatch']) : false;
        return $foundRelease === true ? $mbRelease : false;
    }

    /**
     * @param array     $query               query array containing title (required), release, track, year
     * @param mbArtist  $artist              required
     * @param bool      $requireReleaseMatch Whether or not to only match the title if the release matches as well
     *                                       Defaults to false
     *
     * @return array|bool                    {recording => mbTrack, release => mbRelease or false if no release matched}
     *                                       or return false on no matches
     */
    public function findRecording($query, mbArtist $artist,  $requireReleaseMatch = false)
    {
        if (MusicBrainz::DEBUG_MODE >= MusicBrainz::DEBUG_MIN)
        {
            echo "Starting " . __METHOD__ . "\n";
            print_r($query);
        }

        $foundRecording = false;
        $return = array();
        $matchedRecording = array();

        if(!isset($artist))
            return false;
        // Experimental - remove text inside parenthesis.  Usually contains a second artist, i.e. (featuring John Doe) that
        // seems to cause a lot of non-matches, or mismatches
        if(isset($query['title']))
            $query['title'] = preg_replace('/\([\w\s\.\-]+\)/', '', $query['title']);

        if(is_array($query) && isset($query['title']) && isset($query['release']))
        {
            $results = $this->__searchRecording($query['title'], 'recording', $artist->getMbID(), "arid",  $query['release'], 'release');
        }
        elseif (is_array($query) && isset($query['title']))
        {
            $results = $this->__searchRecording($query['title'], 'recording', $artist->getName(), 'artistname');
        }
        else
            return false;

        if (!isset($results->{'recording-list'}) || !isset($results->{'recording-list'}->attributes()->count))
        {
            if (MusicBrainz::DEBUG_MODE >= MusicBrainz::DEBUG_MAX)
                print_r($results);
            return false;
        }
        if ($results->{'recording-list'}->attributes()->count == '0')
        {
            if (MusicBrainz::DEBUG_MODE >= MusicBrainz::DEBUG_MED)
                echo "Recording search returned no results\n";

            return false;
        } elseif (MusicBrainz::DEBUG_MODE >= MusicBrainz::DEBUG_MED)
            echo "Recordings Found: " . $results->{'recording-list'}->attributes()->count . "\n";

        $normalizedTitleArr = array();
        $normalizedTitleArr[] = $this->__normalizeString($query['title']);
        $normalizedTitleArr[] = $this->__normalizeString($query['title'], true);

        $i = 0; // Recording result counter, used for weighting results
        $percentMatch = -1000; // Arbitrary starting value for $percentMatch
        foreach ($results->{'recording-list'}->recording as $recording)
        {
            $matchFound = false;

            foreach ($normalizedTitleArr as $normalizedTitle)
            {
                if (MusicBrainz::DEBUG_MODE >= MusicBrainz::DEBUG_MED)
                {
                    echo "Checking Title: " . $normalizedTitle . "\n";
                    echo "Against Title:  " . $this->__normalizeString($recording->title) . "\n";
                }
                if (stripos($normalizedTitle, $this->__normalizeString($recording->title)) === false &&
                    stripos($normalizedTitle, $this->__normalizeString($recording->title, true)) === false &&
                    stripos($this->__normalizeString($recording->title), $normalizedTitle) === false &&
                    stripos($this->__normalizeString($recording->title, true), $normalizedTitle) === false &&
                    $normalizedTitle != $this->__normalizeString($recording->title)
                )
                    continue;
                else
                {
                    $matchFound = true;
                    break;
                }
            }
            if ($matchFound)
            {
                // Check for a matching release for the recording
                $releaseMatchFound = false;
                if (isset($query['release']) && isset($recording->{'release-list'}))
                {
                    if ($mbRelease = $this->__getRecordingRelease($query, $recording->{'release-list'})) // release loop
                        $releaseMatchFound = true;
                }
                else // query['release'] is not set, or there was not a release list in the results
                {
                    $releaseMatchFound = true; //Simplifies coding to fake a release match
                    $mbRelease = false; // But the release object won't contain anything
                }
                if (!$releaseMatchFound && $requireReleaseMatch)
                {
                    if (MusicBrainz::DEBUG_MODE >= MusicBrainz::DEBUG_MED)
                        echo "No matching release for matched title.\n";
                    if (MusicBrainz::WRITE_LOG_FILES)
                    {
                        $log = $query['releaseID'] . '|' . $recording->attributes()->id . '|' . $query['title'] . '|' . $query['release'] . '|' . $artist->getName() . "\n";
                        file_put_contents($this->_baseLogPath . "recordingMatch-releaseNoMatch.csv", $log, FILE_APPEND);
                    }
                    continue;
                }
                else
                {
                    similar_text((isset($query['title']) ? $query['title'] : $query), $recording->title, $tempPercentMatch);
                    $tempPercentMatch += (((30 - $i) / 30) * 10); // matches weighted based on position in results list
                    $tempPercentMatch += ($releaseMatchFound && isset($recording->{'release-list'}) ? 15 : 0); //Weight recordings for which the release matched
                    if ($tempPercentMatch > $percentMatch)
                    {
                        $matchedRecording['id'] = $recording->attributes()->id;
                        $matchedRecording['percentMatch'] = $tempPercentMatch;
                        $matchedRecording['releaseID'] = $mbRelease !== false ? $mbRelease->getMbID() : false;
                        unset($mbRelease);
                        $foundRecording = true;
                    }
                } // Release match is true
            } // Title match found
            else
            {
                if (MusicBrainz::DEBUG_MODE >= MusicBrainz::DEBUG_MED)
                    echo "Non-matching recording title: " . $recording->title . "\n";
            }
            $i++; // Increment the recording result counter
        } // Recording result loop

        if (MusicBrainz::DEBUG_MODE >= MusicBrainz::DEBUG_MAX)
        {
            ob_start();
            print_r($results);
            $resultsString = ob_get_clean();
            file_put_contents(WWW_DIR . 'lib/logging/vardump/' . $query['releaseID'] . '-' . $query['artist'] . '-' . $query['title'] . '.log', $resultsString);
        }
        if($foundRecording)
        {
            $return['recording'] = $this->__getRecordingDetails($matchedRecording['id'], $artist->getMbID(),
                ($matchedRecording['releaseID'] !== false ? $matchedRecording['releaseID'] : null), $matchedRecording['percentMatch']);
            $return['release'] =  $matchedRecording['releaseID'] !== false ? $this->__getReleaseDetails($matchedRecording['releaseID'], $artist->getMbID()) : false;
        }

        return $foundRecording === true ? $return : false;
    }

    /**
     * @param string $text              string to normalize
     * @param bool   $includeArticles   remove English language articles (a, an, the)
     *
     * @return string
     *
     * This function standardizes text strings to facilitate better matches
     */
    private function __normalizeString($text, $includeArticles = false)
    {
        $text = strtolower($text);
        if ($includeArticles)
            $text = preg_replace('/\b(a|an|the)\b/i', ' ', $text);
        $text = str_replace(array(".", "_", '-', "|", "<", ">", '"', "=", "~", '[', "]", "(", ")", "{", "}", "*", ";", ":", ",", "~", "/", "+", "'s "), " ", $text);
        $text = str_ireplace(' vol ', ' Volume ', $text);
        $text = str_ireplace('&', 'and', $text);
        $text = preg_replace('/\s{2,}/', ' ', $text);
        $text = trim($text);

        return $text;
    }

    /**
     * @param string    $text   text to clean
     * @param bool      $debug  true to output debug info, defaults to false
     *
     * @return string
     */
    public function cleanQuery($text, $debug = false)
    {
        // Remove year
        if ($debug)
            echo "\nStrip Search Name - " . $text . "\n";
        $text = preg_replace('/\((19|20)\d\d\)|(?<!top|part|vol|volume)[ \-_]\d{1,3}[ \-_]|\d{3,4} ?kbps| cd ?\d{1,2} /i', ' ', $text);
        if ($debug)
            echo "1 - " . $text . "\n";
        // Remove extraneous format identifiers
        $text = str_replace(array('MP3', 'FLAC', 'WMA', 'WEB', "cd's", ' cd ', ' FM '), ' ', $text);
        if ($debug)
            echo "2 - " . $text . "\n";
        $text = str_ireplace(' vol ', ' Volume ', $text);
        if ($debug)
            echo "3 - " . $text . "\n";
        // Remove extra punctuation and non alphanumeric
        $text = str_replace(array(".", "_", '-', "|", "<", ">", '"', "=", "~", '[', "]", "(", ")", "{", "}", "*", ";", ":", ",", "~", "/", "+", "!"), " ", $text);
        if ($debug)
            echo "4 - " . $text . "\n";
        $text = preg_replace('/\s{2,}/', ' ', $text);
        if ($debug)
            echo "5 - " . $text . "\n";

        return $text;
    }

    /**
     * @param string $text        text to use as base
     * @param array  $searchArray array to append results to
     *
     * @return array
     *
     * This function builds an array of strings based on rules defined within the
     * function.  The array is then used to compare release search results against.
     */
    private function __buildReleaseSearchArray($text, $searchArray)
    {
        if(MusicBrainz::DEBUG_MODE >= MusicBrainz::DEBUG_MIN)
            echo "Starting " . __METHOD__ . "\n";

        $searchArray[] = $text;
        $searchArray[] = $this->__normalizeString($text);
        $searchArray[] = $this->__normalizeString($text, true);

        // Remove the word "volume" because many entries in MusicBrainz don't include it
        // i.e. instead of Great Music Volume 1, MB will have Great Music 1
        if (preg_match('/\bVolume\b/i', $text))
            $searchArray[] = preg_replace('/\bVolume\b/i', ' ', $text);
        // Replace ordinal numbers with roman numerals
        preg_match('/\bVolume[ \-_\.](\d)\b/i', $text, $matches);
        if(isset($matches[1]))
        {
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
        }
        // Get rid of extra spaces in all values
        foreach ($searchArray as $key => $value)
        {
            $searchArray[$key] = preg_replace('/\s{2,}/', ' ', $value);
        }

        return $searchArray;
    }

    /**
     * @param mbArtist $artist
     *
     * @return bool
     */
    public function updateArtist(mbArtist $artist)
    {
        if (MusicBrainz::DEBUG_MODE >= MusicBrainz::DEBUG_MIN)
            echo "Starting " . __METHOD__ . "\n";

        if($artist->getMbID() == '' || is_null($artist->getMbID()))
            return false;

        $this->__updateGenres('artist', $artist->getMbID(), $artist->getTags());
        $db = new DB();
        $searchExisting = $db->queryOneRow("SELECT mbID FROM mbArtists WHERE mbID=" . $db->escapeString($artist->getMbID()));
        if(!$searchExisting)
        {
            $sql = "INSERT INTO mbArtists (mbID, name, type, gender, disambiguation, description, genres, country, rating, beginDate, endDate) VALUES (" .
                     $db->escapeString($artist->getMbID()) . ", " . $db->escapeString($artist->getName()) . ", " . $db->escapeString($artist->getType()) . ", " .
                     $db->escapeString($artist->getGender()) . ", " . $db->escapeString($artist->getDisambiguation()) . ", " .
                     $db->escapeString($artist->getDescription()) . ", " . $db->escapeString(implode(", ", $artist->getTags())) . ", " . $db->escapeString($artist->getCountry()) . ", " .
                     $artist->getRating() . "," . $db->escapeString($artist->getBeginDate()) . "," . $db->escapeString($artist->getEndDate()) . ")";

            if(MusicBrainz::WRITE_LOG_FILES)
                file_put_contents($this->_baseLogPath . 'artist-SQL.log', $sql . "\n----------------------------------------\n", FILE_APPEND);

            return $db->queryInsert($sql);
        }
        else
        {
            $sql = "UPDATE mbArtists SET name=" . $db->escapeString($artist->getName()) . ", type=" . $db->escapeString($artist->getType()) . ", description=" . $db->escapeString($artist->getDescription()) .
                    ", gender=" . $db->escapeString($artist->getGender()) . ", disambiguation=" . $db->escapeString($artist->getDisambiguation()) .
                    ", genres=" . $db->escapeString(implode(", ", $artist->getTags())) . ", country=" . $db->escapeString($artist->getCountry()) . ", rating=" . $artist->getRating() .
                    ", beginDate=" . $db->escapeString($artist->getBeginDate()) . ", endDate=" . $db->escapeString($artist->getEndDate()) . ", updateDate=" . time() .
                    " WHERE mbID=" . $db->escapeString($artist->getMbID());

            if (MusicBrainz::WRITE_LOG_FILES)
                file_put_contents($this->_baseLogPath . 'artist-SQL.log', $sql . "\n----------------------------------------\n", FILE_APPEND);

            return $db->queryDirect($sql);
        }
    }

    /**
     * @param mbRelease $release
     *
     * @return bool
     */
    public function updateAlbum(mbRelease $release)
    {
        if (MusicBrainz::DEBUG_MODE >= MusicBrainz::DEBUG_MIN)
            echo "Starting " . __METHOD__ . "\n";

        if($release->getMbID() == '' || is_null($release->getMbID()))
            return false;

        $this->__updateGenres('album', $release->getMbID(), $release->getTags());
        $db = new DB();

        $this->__getCoverArt($release);

        if (MusicBrainz::DEBUG_MODE >= MusicBrainz::DEBUG_MIN)
            echo "updateAlbum: Returned from __getCoverArt\n";

        $searchExisting = $db->queryOneRow("SELECT mbID FROM mbAlbums WHERE mbID=" . $db->escapeString($release->getMbID()));
        if(!$searchExisting)
        {
            $sql = "INSERT INTO mbAlbums (mbID, artistID, title, year, status, country, releaseDate, releaseGroupID, description, tracks, genres, cover, rating, asin) VALUES " .
                    "(" . $db->escapeString($release->getMbID()) . ", " . $db->escapeString($release->getArtistID()) .
                    ", " . $db->escapeString($release->getTitle()) . ", " . $db->escapeString($release->getYear()) .
                    ", " . $db->escapeString($release->getStatus()) . ", " . $db->escapeString($release->getCountry()) .
                    ", " . $db->escapeString($release->getReleaseDate()) . ", " . $db->escapeString($release->getReleaseGroupID()) .
                    ", " . $db->escapeString($release->getDescription()) . ", " . $release->getTracks() .
                    ", " . $db->escapeString(implode(", ", $release->getTags())) . ", " . $db->escapeString($release->getCover()) .
                    ", " . $release->getRating() . ", " . $db->escapeString($release->getAsin()) . ")";



            if (MusicBrainz::WRITE_LOG_FILES)
                file_put_contents($this->_baseLogPath . 'album-SQL.log', $sql . "\n----------------------------------------\n", FILE_APPEND);

            return $db->queryInsert($sql);
        }
        else
        {
            $sql = "UPDATE mbAlbums SET artistID=" . $db->escapeString($release->getArtistID()) . ", title=" . $db->escapeString($release->getTitle()) .
                    ", year=" . $db->escapeString($release->getYear()) . ", releaseDate=" . $db->escapeString($release->getReleaseDate()) .
                    ", status=" . $db->escapeString($release->getStatus()) . ", country=" . $db->escapeString($release->getCountry()) .
                    ", releaseGroupID=" . $db->escapeString($release->getReleaseGroupID()) . ", description=" . $db->escapeString($release->getDescription()) .
                    ", tracks=" . $release->getTracks() . ", genres=" . $db->escapeString(implode(", ", $release->getTags())) .
                    ", cover=" . $db->escapeString($release->getCover()) . ", rating=" . $release->getRating() .
                    ", asin=" . $db->escapeString($release->getAsin()) . ", updateDate=" . time() . " WHERE mbID=" . $db->escapeString($release->getMbID());

            if (MusicBrainz::WRITE_LOG_FILES)
                file_put_contents($this->_baseLogPath . 'album-SQL.log', $sql . "\n----------------------------------------\n", FILE_APPEND);

            return $db->queryDirect($sql);
        }
    }

    /**
     * @param mbTrack $track
     *
     * @return bool
     */
    public function updateTrack(mbTrack $track)
    {
        if (MusicBrainz::DEBUG_MODE >= MusicBrainz::DEBUG_MIN)
            echo "Starting " . __METHOD__ . "\n";

        if($track->getMbID() == '' || is_null($track->getMbID()))
            return false;

        $db = new DB();
        $searchExisting = $db->queryOneRow("SELECT mbID from mbTracks WHERE mbID=" . $db->escapeString($track->getMbID()));
        if(!$searchExisting)
        {
            $sql = "INSERT INTO mbTracks (mbID, albumID, artistID, year, trackNumber, discNumber, title, length) VALUES " .
                "(" . $db->escapeString($track->getMbID()) . ", " . $db->escapeString($track->getAlbumID()) .
                ", " . $db->escapeString($track->getArtistID()) . ", " . $track->getYear() . ", " . $track->getTrackNumber() .
                ", " . $db->escapeString($track->getDiscNumber()) . ", " . $db->escapeString($track->getTitle()) . ", " . $track->getLength() . ")";

            if (MusicBrainz::WRITE_LOG_FILES)
                file_put_contents($this->_baseLogPath . 'track-SQL.log', $sql . "\n----------------------------------------\n", FILE_APPEND);

            return $db->queryInsert($sql);
        }
        else
        {
            $sql = "UPDATE mbTracks SET albumID=" . $db->escapeString($track->getAlbumID()) . ", artistID=" . $db->escapeString($track->getArtistID()) .
                ", year=" . $track->getYear() . ", trackNumber=" . $track->getTrackNumber() . ", title=" . $db->escapeString($track->getTitle()) .
                ", length=" . $track->getLength() . ", discNumber=" . $track->getDiscNumber() . ", updateDate=" . time() . " WHERE mbID=" . $db->escapeString($track->getMbID());

            if (MusicBrainz::WRITE_LOG_FILES)
                file_put_contents($this->_baseLogPath . 'track-SQL.log', $sql . "\n----------------------------------------\n", FILE_APPEND);

            return $db->queryDirect($sql);
        }
    }

    /* @param string    $type   string literal: 'album' or 'artist'
     * @param string    $mbID   mbID of entity to update
     * @param array     $genres array of tags to update
     *
     * @return bool             true for success, false for failure
     */
    private function __updateGenres($type, $mbID, $genres)
    {
        if (MusicBrainz::DEBUG_MODE >= MusicBrainz::DEBUG_MIN)
        {
            echo "Starting " . __METHOD__ . "\n";
            print_r($genres);
        }

        $validTypes = array('artist', 'album');
        if(in_array($type, $validTypes) && !empty($genres) && !empty($mbID))
        {
            $db = new DB();
            foreach($genres as $genre)
            {
                $genreID = $db->queryOneRow("SELECT ID FROM mbGenres WHERE name=" . $db->escapeString(trim($genre)));
                if(!isset($genreID['ID']))
                {
                    $sql = "INSERT INTO mbGenres (`name`) VALUES (" . $db->escapeString(trim($genre)) . ")";
                    $genreID['ID'] = $db->queryInsert($sql);
                    if (MusicBrainz::DEBUG_MODE >= MusicBrainz::DEBUG_MIN)
                    {
                        echo "__updateGenres: New genre added: " . $genreID['ID'] . "\n";
                        echo "SQL: " . $sql . "\n";
                        echo "SQL Error: " . $db->Error() . "\n";
                    }

                }
                $db->queryInsert("INSERT INTO mb" . ucwords($type) . "IDtoGenreID (" . strtolower($type) . "ID, genreID) VALUES (" .
                    $db->escapeString($mbID) . ", " . $genreID['ID'] . ")");
            }
            return true;
        }

        return false;
    }

    /* @param string    $releaseName
     *
     * @return array|bool               array will contain artist and title at minimum,
     *                                  may also include release, track, and disc
     */
    public function isTrack($releaseName)
    {
        if (MusicBrainz::DEBUG_MODE >= MusicBrainz::DEBUG_MIN)
            echo "Starting " . __METHOD__ . "\n";

        if (empty($releaseName) || $releaseName == null)
            return false;

        //Thunder-News.org has a specific format that won't match correctly against the other regex below
        if(preg_match('/\(www.thunder-news.org\)/i', $releaseName) === 1)
            preg_match('/>(?P<artist>\w[\w\s\'_\&\-]+?)-(?P<release>\w[\w\s\'_\(\)\d\&\.]+)-([A-Z]{2,}|\(|(19|20)\d\d).+< <.+" ?(?P<track>[0-9][0-9]\d?\d?)-[\w_\.]+-(?P<title>\w[\(\)\w_\'\&]+)(-\w+)?\.(?:mp3|wav|ogg|wma|mpa|aac|m4a|flac|ape)/i', $releaseName, $matches);
        else
        {
            // Remove years and file/part counts
            $releaseName = trim(preg_replace('/\[.+\]|\(?\[?19\d\d\]?\)?(-\d)?|\(?\[?20\d\d\]?\)?(-\d)?|\(?\[?\d{1,3}\/\d{1,3}\]?\)?|\[\d{1,2} of \d{1,2}\]/', '', $releaseName));
            // Perform some very basic cleaning on the release name before matching
            $releaseName = trim(preg_replace('/~|by req:? |as req(uested)?|attn:? [\w_]+| 320 |\bEAC\b|\bmy rip\b|[\(\{\[\*]?NMR(\d\d\d)?[\)\}\]\*]?|\d\dbit|\d\dKhz|CD\d|^\(.+\)|\[ThOrP\]|repost|\d{1,2}cd|^req \w+? /i', '', $releaseName));
            // Normalize spacing
            $releaseName = trim(preg_replace('/\s{2,}/', ' ', $releaseName));

            // If it's a blatantly obvious 'various artist' release, use the following pattern
            // Need to handle looking up the actual release for VA tracks better in findRecording
            if (substr($releaseName, 0, 2) == 'VA')
                preg_match('/VA ?- ?(?P<release>[\w\s\' ]+?)- ?(19\d\d|20\d\d)? ?-?(?![\(\[ ](19\d\d|20\d\d))(?P<track> ?(?!\(|\[|19\d\d|20\d\d)[0-2][0-9]\d?\d?) ?- ?(?P<artist>[\w\s\'\.]+?) ?- ?(?P<title>[\(\)\w _\']+)\.(?:mp3|wav|ogg|wma|mpa|aac|m4a|flac)/i', $releaseName, $matches);

            // artist (hyphenated only, i.e. Mary-Chapin Carpenter), release, track, title - This must be run first
            if (!isset($matches[0]) || (!isset($matches['artist']) && !isset($matches['release']) && !isset($matches['track']) && !isset($matches['title'])))
                preg_match('/(?:^| ?)(?P<artist>[\w\s]+-[\w\s]+) - (?P<release>\w[\w\s\'_\(\)\d\&]+)[ \-]+?(?P<track> ?[0-2][0-9]\d?\d?) - (?!-)(?P<title>[\(\)\w _\'\&\.]+)\.(?:mp3|wav|ogg|wma|mpa|aac|m4a|flac|ape)/i', $releaseName, $matches);

                // The 'track' group will not match tracks numbered above 19 to prevent matching a year
                // Probably won't be much of an issue because track numbers that high are rare.
                // The alternative is the regex would be much more strict in what would be identified as a track number.
                // Below matches artist, release, track, title (Type 1)
            if (!isset($matches[0]) || (!isset($matches['artist']) && !isset($matches['release']) && !isset($matches['track']) && !isset($matches['title'])))
                preg_match('/(?:^|["\- ])(?P<artist>\w[\w\s\'_\&]++)(\s{3,}|[ \-"]+)(?P<release>\w[\w\s\'_\(\)\-\d\&\.]+)[ \-"]*?\(?(?P<track> ?[D0-2][0-9]\d?\d?).+?(?!-)(?P<title>[\(\)\w _\'\&\.]+)\.(?i:mp3|wav|ogg|wma|mpa|aac|m4a|flac|ape)/i', $releaseName, $matches);

            // artist, release, track, title (Type 2)
            if (!isset($matches[0]) || (!isset($matches['artist']) && !isset($matches['release']) && !isset($matches['track']) && !isset($matches['title'])) || preg_match('/^(D\d|[\d \-])$/', $matches['artist']) || preg_match('/^(D\d|[\d \-])$/', $matches['release']))
                preg_match('/(?P<artist>[\w\s\&\'_\-]+?)(\d?"|\s{2,}).*?(?P<release>\w[\w\s\&\'_\-]+).+"(?P<track>(\d - )?(D\d)?\d\d) ?-?(?P<title>[\(\)\w _\'\&\.]+)\.(?:mp3|wav|ogg|wma|mpa|aac|m4a|flac|ape)/i', $releaseName, $matches);

            // artist, release, track, title (Type 3)
            if (!isset($matches[0]) || (!isset($matches['artist']) && !isset($matches['release']) && !isset($matches['track']) && !isset($matches['title'])))
                preg_match('/(?P<artist>\w[\w\s\&\'_\-]+?)-\s*(?P<release>\w[\w\s\&\'_\-\.]+) ?- ?(?P<track>\d\d) ?- ?(?P<title>[\w\s]+)\.(?:mp3|wav|ogg|wma|mpa|aac|m4a|flac|ape)/i', $releaseName, $matches);

            // artist, release, track, title (Type 4)
            if (!isset($matches[0]) || (!isset($matches['artist']) && !isset($matches['release']) && !isset($matches['track']) && !isset($matches['title'])))
                preg_match('/"\s*?(?P<release>\w[\w\s\&\'_\-]+)".+"(?P<track>\d\d) - (?P<artist>\w[\w\s\&\'_\-\.]+) ?- ?(?P<title>[\w\s\'\&_`]+)\.(?:mp3|wav|ogg|wma|mpa|aac|m4a|flac|ape)/i', $releaseName, $matches);


            // track, artist, title
            if (!isset($matches[0]) || (!isset($matches['artist']) && !isset($matches['release']) && !isset($matches['track']) && !isset($matches['title'])))
                preg_match('/["\- ](?P<track>(?!\(|\[|19\d\d|20\d\d)[0-2][0-9]\d?\d?)(?<!\(|\[|19\d\d|20\d\d)(?P<artist>( |-).+-) ?-? ?(?P<title>[\(\)\w \-_\']+)\.(?:mp3|wav|ogg|wma|mpa|aac|m4a|flac)/i', $releaseName, $matches);

            // artist, title (must be used last due to being very permissive)
            if (!isset($matches[0]))
                preg_match('/("|-)? ?"?(?P<artist> ?.+-)* ?-? ?(?P<title>[\(\)\w \-_\']+)\.(?:mp3|wav|ogg|wma|mpa|aac|m4a|flac)/i', $releaseName, $matches);
        }
        if (!isset($matches[0]))
            return false;

        if (isset($matches['artist']))
        {
            $matches['artist'] = trim(str_ireplace(array('-', '_', '"'), ' ', $matches['artist']));
            $matches['artist'] = trim(preg_replace('/\s{2,}/', ' ', $matches['artist']));
            if (preg_match('/^\d+$/', $matches['artist']) === 1 || strlen($matches['artist']) < 2 || preg_match('/^\s+&/', $matches['artist']) === 1)
                return false;

            if(trim($matches['artist']) == 'VA')
                $matches['artist'] = 'Various Artists';
        }

        if (isset($matches['release']))
        {
            $matches['release'] = trim(str_ireplace(array('-', '_', '"'), ' ', $matches['release']));
            $matches['release'] = trim(preg_replace('/- ?\([\w\d\s]+\) ?-/', '', $matches['release']));
            $matches['release'] = trim(preg_replace('/\s{2,}/', ' ', $matches['release']));
        }

        if (isset($matches['title']))
        {
            $matches['title'] = trim(str_ireplace(array('-', '_', '"'), ' ', $matches['title']));
            $matches['title'] = trim(preg_replace('/\s{2,}/', ' ', $matches['title']));
        }
        if (isset($matches['track']))
            $matches['track'] = trim(str_ireplace(array('"', ' ', '-'), '', $matches['track']));

        if (isset($matches['track']) && strlen($matches['track']) > 2)
        {
            if(strpos($matches['track'], '-') !== false)
            {
                $matches['disc'] = substr($matches['track'], 0, 2);
                $matches['track'] = substr($matches['track'], 2, 2);
            }
            else
            {
                $matches['disc'] = strlen($matches['track']) > 3 ? substr($matches['track'], 0, 2) : substr($matches['track'], 0, 1);
                $matches['track'] = strlen($matches['track']) > 3 ? substr($matches['track'], 2, 2) : substr($matches['track'], 1, 2);
            }

            $matches['disc'] = str_ireplace('d','', $matches['disc']);
        }

        if (preg_match('/^\d+$/', $matches['artist']) === 1 || strlen($matches['artist']) < 2 || preg_match('/^\s+&/', $matches['artist']) === 1)
            return false;

        if (preg_match('/^\d+$/', $matches['title']) === 1 || strlen($matches['title']) < 2 || preg_match('/^\s+&/', $matches['title']) === 1)
            return false;

        // One last round of cleaning to get rid of trailing hyphens and spaces
        if(isset($matches['artist']))
            $matches = preg_replace('/^[\(\) \-]+|[\(\) \-]+$/', '', $matches);

        return (isset($matches['artist']) && isset($matches['title'])) ? $matches : false;
    }

    /**
     * @param string    $releaseName    release name field from nZEDbetter database
     *
     * @return bool|array               array will contain title and artist fields
     */
    public function releaseParser($releaseName)
    {
        if(is_null($releaseName) || empty($releaseName))
            return false;
        // Don't waste time on tracks that didn't get caught by the isTrack function
        if(preg_match('/\.(?:mp3|wav|ogg|wma|mpa|aac|m4a|flac|ape)/i', $releaseName) === 1)
            return false;

        if (preg_match('/\^trtk\d{5}/', $releaseName) === 1) // trtk releases have a specific format that will not match with the normal regex parsers
            preg_match('/"(?P<artist>[\w\s\&\'_]+)-(?P<title>[\w\s\&\'_\.!,\-]+)([\.\(\+])/', $releaseName, $matches);
        if (preg_match('/\^trtk\d{5}/', $releaseName) === 1 && !isset($matches[0])) // trtk releases without an artist have a specific format that will not match with the normal regex parsers
        {
            preg_match('/"(?P<title>[\w\s\&\'_\.!,\-]+)([\.\(])/', $releaseName, $matches);
            $matches['artist'] = 'Various Artists';
        }
        elseif (preg_match('/\[(a\.b\.flac(EFNet)?|altbinEFNet)\]/', $releaseName) === 1) // a.b.flacEFNet releases have a specific format that will not match with the normal regex parsers
            preg_match('/\[ (?P<artist>[\w\s\&\'_\.]+)-(?P<title>[\w\s\&\'_\.\(\),!\-]+?)((-(\d|[A-Z]{2,}|\())|(_\()).+ \]/', $releaseName, $matches);
        elseif (preg_match('/\[ TOWN \]/', $releaseName) === 1)  // TOWN releases have a specific format that will not match with the normal regex parsers
            preg_match('/"(?P<artist>[\w\s\&\'_]+)-(?P<title>[\w\s\&\'_\.\(\),!\-]+?)-[A-Z]{2,}.+\./', $releaseName, $matches);
        elseif (preg_match('/\(www.thunder-news.org\)/i', $releaseName) === 1) // Thunder-News has a specific format that will not match, or match incorrectly with the normal regex parsers below
            preg_match('/>(?P<artist>\w[\w\s\'_\&\-]+?)-(?P<release>\w[\w\s\'_\(\)\d\&\.]+)-([A-Z]{2,}|\(|(19|20)\d\d).+< <.+\.(?i:par|nfo|sfv|rar|r\d\d?|vol|nzb|m3u|cue|flac)/', $releaseName, $matches);
        elseif (preg_match('/\[ [a-f0-9]{32} \]/', $releaseName) === 1)  // Weird releases that begin with [ 32 char hash ]
            preg_match('/"\d\d-(?P<artist>[\w\s\&\'_\.\-]+?)-(?P<title>[\w\s\&\'_\.\(\),!]+?)-((20|19)\d\d|web|\(|(?i:fri|sat|sun|mon|tue|wed|thu)|[0-2][0-9]).+\./i', $releaseName, $matches);
        elseif (preg_match('/^time life/i', $releaseName) === 1) // Time-Life compilations
        {
            preg_match('/^Time Life ?-? ?(?P<title>[\w\s\&\'_\.\(\),!]+)/i', $releaseName, $matches);
            $matches['artist'] = 'Various Artists';
        }
        else  // General parsers that work with most other release names
        {
            // Remove parts and years
            $releaseName = preg_replace('/(\(.+?\))|(?<!-)\(?\[?19\d\d\]?\)?(-\d(?!\d\d\d))?(?!-)|(?<!-)\(?\[?20\d\d\]?\)?(-\d(?!\d\d\d))?(?!-)|\(?\[?\d{1,3}\/\d{1,3}\]?\)?|\[\d{1,2} of \d{1,2}\]|\[|\]|^(?!VA)[A-Z ]{2,}|.NMR.|.nmr./', '', $releaseName);

            // Release parser 1
            preg_match('/--(?P<artist>[\w\s\&\'_]+?)-(?P<title>[\w\s\&\'_\.\-!,]+)-/i', $releaseName, $matches);

            // Release parser 2
            if((!isset($matches['artist']) || !isset($matches['title'])) || strlen($matches['artist']) < 2 || strlen($matches['title']) < 2)
                preg_match('/"(?P<artist>[\w\s\&\'_]+?)-(?P<title>[\w\s\&\'_\.\(\)!,]+?)(\(|\.(?i:par|nfo|sfv|rar|r\d\d?|vol|nzb|m3u|cue|flac))/i', $releaseName, $matches);

            // Release parser 3
            if ((!isset($matches['artist']) || !isset($matches['title'])) || strlen($matches['artist']) < 2 || strlen($matches['title']) < 2 || preg_match('/^[A \d]+$/', $matches['artist']) === 1 || preg_match('/^[A \d]+$/', $matches['title']) === 1)
                preg_match('/(?P<artist>[\w\s\&\'_]+?)[ \-\"]+?(?P<title>[\w\s\&\'_\.\(\),!]+)\.(?i:par|nfo|sfv|rar|r\d\d?|vol|nzb|m3u|cue|flac)/i', $releaseName, $matches);

            // Release parser 4
            if ((!isset($matches['artist']) || !isset($matches['title'])) || strlen($matches['artist']) < 2 || strlen($matches['title']) < 2 || preg_match('/^[A \d]+$/', $matches['artist']) === 1 || preg_match('/^[A \d]+$/', $matches['title']) === 1)
                preg_match('/^"?-?(?P<artist>[\w\s\&\'_\-]+?) - (?P<title>[\w\s\&\'_\.\(\),!]+?)[\-\(";]/', $releaseName, $matches);

            // Release parser 5
            if ((!isset($matches['artist']) || !isset($matches['title'])) || strlen($matches['artist']) < 2 || strlen($matches['title']) < 2 || preg_match('/^[A \d]+$/', $matches['artist']) === 1 || preg_match('/^[A \d]+$/', $matches['title']) === 1)
                preg_match('/^"?-?(?P<artist>[\w\s\&\'_]+?)-(?P<title>[\w\s\&\'_\.\(\),!]+?)[\-\(";]/', $releaseName, $matches);

            // Release parser 6
            if ((!isset($matches['artist']) || !isset($matches['title'])) || strlen($matches['artist']) < 2 || strlen($matches['title']) < 2 || preg_match('/^[A \d]+$/', $matches['artist']) === 1 || preg_match('/^[A \d]+$/', $matches['title']) === 1)
                preg_match('/^"?(?P<artist>[\w\s\&\'_]+?)-.+"(?P<title>[\w\s\&\'_\.\(\),!]+?)(?i:([\-\("]|\.(par|nfo|sfv|rar|r\d\d?|vol|nzb|m3u|cue|flac)))/', $releaseName, $matches);

        }

        if (!isset($matches['title']) || !isset($matches['artist']))
            return false;
        // Get rid of leading or trailing spaces/hyphens/parenthesis
        $matches = preg_replace('/^[\(\) \-]+|[\(\) \-]+$/', '', $matches);

        $matches['artist'] = trim(str_ireplace(array('-', '_', '"'), ' ', $matches['artist']));
        $matches['title'] = trim(str_ireplace(array('-', '_', '"'), ' ', $matches['title']));

        // Replace multiple spaces with a single space
        $matches= preg_replace('/\s{2,}/', ' ', $matches);

        if (preg_match('/^\d+$/', $matches['artist']) === 1 || strlen($matches['artist']) < 2 || preg_match('/^\s+&/', $matches['artist']) === 1)
            return false;

        if (preg_match('/^\d+$/', $matches['title']) === 1 || strlen($matches['title']) < 2 || preg_match('/^\s+&/', $matches['title']) === 1)
            return false;

        if (trim($matches['artist']) == 'VA' || trim($matches['artist']) == 'Various' || trim($matches['artist']) == 'va' || trim($matches['artist']) == 'various')
            $matches['artist'] = 'Various Artists';

        return $matches;
    }

    /**
     * @param simpleXMLElement     $relArtist        simpleXMLElement containing a single artist result from MB
     * @param array                $query            Array containing values to compare against
     * @param bool                 $skipVariousCheck Defaults to false, skip check for Various Artists
     * @param int|float            $weight           Precalculated weight to add to percentMatch
     *
     * @return mbArtist
     */
    private function __checkArtistName($relArtist, $query, $skipVariousCheck = false, $weight = 0)
    {
        if (MusicBrainz::DEBUG_MODE >= MusicBrainz::DEBUG_MIN)
            echo "Starting " . __METHOD__ . "\n";
        if(MusicBrainz::DEBUG_MODE >= MusicBrainz::DEBUG_MAX)
            print_r($relArtist);

        $queryText = '';
        if (MusicBrainz::DEBUG_MODE >= MusicBrainz::DEBUG_MED)
            echo "Checking artist: " . $relArtist->name . "\n";
        $percentMatch = 0;
        $artistArr = array();
        $artistFound = false;
        if ($relArtist->name === '[unknown]')
            return false;
        elseif ($relArtist->name == 'Various Artists' && !$skipVariousCheck)
        {
            $artistArr['name'] = 'Various Artists';
            $artistArr['id'] = '89ad4ac3-39f7-470e-963a-56509c546377';
            $artistFound = 'Various Artists';
            $queryText = $query[0];
        }

        foreach ($query as $stringToMatch)
        {
            $queryText = $stringToMatch;
            if (preg_match('/\b' . $this->__normalizeString($relArtist->name) . '\b/i', $stringToMatch) === 0 &&
                preg_match('/\b' . $this->__normalizeString($relArtist->name, true) . '\b/i', $stringToMatch) === 0)
            {
                if (preg_match('/\b' . trim(str_ireplace('Group', '', $this->__normalizeString($relArtist->name, true))) . '\b/i', $stringToMatch) === 1)
                {
                    $artistFound = trim(str_ireplace('Group', '', $this->__normalizeString($relArtist->name, true)));
                    break;
                } 
                elseif (isset($relArtist->{'sort-name'}) && preg_match('/\b' . $this->__normalizeString($relArtist->{'sort-name'}) . '\b/i', $stringToMatch) === 1)
                {
                    $artistFound = $this->__normalizeString($relArtist->{'sort-name'});
                    break;
                } 
                else
                {
                    if (MusicBrainz::DEBUG_MODE >= MusicBrainz::DEBUG_MED)
                        echo "Artist name not matched: " . $relArtist->name . " (weight = $weight)\n";
                    if (isset($relArtist->{'alias-list'}))
                    {
                        if (MusicBrainz::DEBUG_MODE >= MusicBrainz::DEBUG_MED)
                            echo "Checking aliases...\n";
                        foreach ($relArtist->{'alias-list'}->alias as $alias)
                        {
                            if (is_array($alias))
                            {
                                if (MusicBrainz::DEBUG_MODE >= MusicBrainz::DEBUG_MED)
                                    echo "\nAlias is an array\n";
                                foreach ($alias as $aliasName)
                                {
                                    if (isset($aliasName['locale']) && $aliasName->attributes()->locale == 'ja')
                                        continue;
                                    if (preg_match('/\b' . $this->__normalizeString($aliasName) . '\b/i', $stringToMatch) === 0)
                                    {
                                        if (MusicBrainz::DEBUG_MODE >= MusicBrainz::DEBUG_MED)
                                            echo "Alias did not match: " . $aliasName . " (weight = $weight)\n";
                                        continue;
                                    } 
                                    else
                                    {
                                        // if(MusicBrainz::DEBUG_MODE)
                                        $artistFound = $this->__normalizeString($aliasName);
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
                                if (preg_match('/\b' . $this->__normalizeString($alias) . '\b/i', $stringToMatch) === 0)
                                {
                                    if (MusicBrainz::DEBUG_MODE >= MusicBrainz::DEBUG_MED)
                                        echo "Alias did not match: " . $alias . " (weight = $weight)\n";
                                    continue;
                                } 
                                else
                                {
                                    if (MusicBrainz::DEBUG_MODE >= MusicBrainz::DEBUG_MED)
                                        echo "Alias matched: " . $alias . " (weight = $weight)\n";
                                    $artistFound = $this->__normalizeString($alias);
                                    break;
                                }
                            }
                            if ($artistFound)
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
                $artistFound = $this->__normalizeString($relArtist->name);
                break;
            }
        }

        if ($artistFound)
        {

            $artist = new mbArtist($relArtist->attributes()->id);
            $artist->setName($relArtist->name);
            $artist->setMatchString($artistFound);
            similar_text($queryText, $this->__normalizeString($relArtist->name), $percentMatch);
            $artist->setPercentMatch($percentMatch + $weight);

            if ($artist->getPercentMatch() > 15)
            {
                if (MusicBrainz::DEBUG_MODE >= MusicBrainz::DEBUG_MED)
                    echo "Artist name matched: " . $artist->getName() . " (percentMatch = " . $artist->getPercentMatch() . ")\n";
                return $artist;
            }
            elseif ($artistFound && $artist->getPercentMatch() > 0 && $artist->getPercentMatch() <= 15)
            {
                if (MusicBrainz::DEBUG_MODE >= MusicBrainz::DEBUG_MED)
                    echo "Artist percent match not acceptable: " . $artist->getPercentMatch() . "\n";
                return false;
            }
        }

        return false;
    }

    /**
     * @param $url
     *
     * @return bool|SimpleXMLElement
     * @throws MBException              exception thrown if php-curl not loaded, or
     *                                  if url contains musicbrainz.org and no valid
     *                                  email address is configured in site settings.
     *
     * NOTE: All requests to musicbrainz are in compliance with the MusicBrainz terms
     * of service, provided that the code below has not been altered from the author's
     * original work.  For the current release version of nZEDbetter, please visit
     * https://github.com/KurzonDax/nZEDbetter
     */
    protected  function __getResponse($url)
    {
        if (MusicBrainz::DEBUG_MODE >= MusicBrainz::DEBUG_MIN)
            echo "Starting " . __METHOD__ . " URL: " . $url . "\n";

        if (extension_loaded('curl'))
        {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_USERAGENT, $this->_applicationName . "/" . $this->_applicationVersion . "  ( " . $this->_email . " )");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

            if ($this->_throttleRequests)
            {
                if (is_null($this->_email) || empty($this->_email) ||
                    preg_match('/[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+(?:[A-Z]{2}|com|org|net|gov|mil|biz|info|mobi|name|aero|jobs|museum)\b/i', $this->_email) === 0)
                {
                    echo "\n\033[01;31mALERT!!! You have not set a valid email address in Admin->Site Settings.\n";
                    echo "The MusicBrainz integration will not function until this is corrected.\n\n";
                    throw new MBException("Invalid email address.  Halting MusicBrainz Processing.");
                }
                else //The following is REQUIRED if using musicbrainz.org for the server, per http://musicbrainz.org/doc/XML_Web_Service/Rate_Limiting
                    sleep($this->_threads * 1);
            }
            // sleep(2);   // Added this in as a step to alleviate an error MusicBrainz seems to generate when it is being flooded
            $xml_response = curl_exec($ch);
            if ($xml_response === false)
            {
                curl_close($ch);

                if(MusicBrainz::DEBUG_MODE > 0)
                    echo "__getResponse: curl request failed.\n";

                return false;
            }
            else
            {
                /* parse XML */
                $parsed_xml = @simplexml_load_string($xml_response);
                curl_close($ch);

                return ($parsed_xml === false) ? false : $parsed_xml;
            }
        }
        else
        {
            throw new MBException('CURL-extension not loaded');
        }
    }

    /**
     * @param mbRelease $release    passed by reference, updated with path to saved cover art
     */
    private function __getCoverArt(mbRelease &$release)
    {
        if (MusicBrainz::DEBUG_MODE >= MusicBrainz::DEBUG_MIN)
            echo "Starting " . __METHOD__ . "\n";
        sleep(2);  //Prevent overloading rate limiters
        $releaseImage = new ReleaseImage();
        if ($release->getCover() == true)
        {
            $imageName = "mb-" . $release->getMbID() . "-cover";
            $imageUrl = MusicBrainz::COVER_ART_BASE_URL . $release->getMbID() . "/front";

            if (MusicBrainz::DEBUG_MODE >= MusicBrainz::DEBUG_MIN)
                echo "__getCoverArt: fetching cover from " . $imageUrl . "\n";

            if(!MusicBrainz::SKIP_COVER_CREATION)
            {
                $imageSave = $releaseImage->saveImage($imageName, $imageUrl, $this->_imageSavePath);
                $release->setCover(($imageSave ? $imageName . ".jpg" : 'NULL'));
            }
            else
                $release->setCover($imageUrl);
        }
        elseif ($release->getAsin() != false && $this->_isAmazonValid)
        {
            // Get from Amazon if $release->asin != false and valid Amazon keys have been provided
            $amazon = new AmazonProductAPI($this->_amazonPublicKey, $this->_amazonPrivateKey, $this->_amazonTag);
            if (MusicBrainz::DEBUG_MODE >= MusicBrainz::DEBUG_MIN)
                echo "__getCoverArt: Attempting Amazon\n";

            try
            {
                $amazonResults = $amazon->getItemByAsin($release->getAsin(), "com", "ItemAttributes,Images");

                if (MusicBrainz::DEBUG_MODE >= MusicBrainz::DEBUG_MIN)
                    echo "__getCoverArt: Amazon results received \n";
                if(MusicBrainz::DEBUG_MODE >= MusicBrainz::DEBUG_MED)
                    print_r($amazonResults);

                if (isset($amazonResults->Items->Item->ImageSets->ImageSet->LargeImage->URL) && !empty($amazonResults->Items->Item->ImageSets->ImageSet->LargeImage->URL))
                {
                    $imageUrl = $amazonResults->Items->Item->ImageSets->ImageSet->LargeImage->URL;
                    $imageName = "mb-" . $release->getMbID() . "-cover";

                    if (MusicBrainz::DEBUG_MODE >= MusicBrainz::DEBUG_MIN)
                        echo "__getCoverArt: Saving cover art from Amazon\nURL: " . $imageUrl . "\nimageName: " . $imageName . "\nSave Path: " . $this->_imageSavePath . "\n";

                    if(!MusicBrainz::SKIP_COVER_CREATION)
                    {
                        $imageSave = $releaseImage->saveImage($imageName, $imageUrl, $this->_imageSavePath, 400, 400);
                        $release->setCover(($imageSave ? $imageName . ".jpg" : 'NULL'));
                    }
                    else
                        $release->setCover($imageUrl);

                }
            }
            catch (Exception $e)
            {
                if (MusicBrainz::DEBUG_MODE >= MusicBrainz::DEBUG_MIN)
                    echo "__getCoverArt: Amazon exception: " . $e . "\n";
                $release->setCover('NULL');
            }
        }
        else
        {
            $release->setCover('NULL');
        }

        if (MusicBrainz::DEBUG_MODE >= MusicBrainz::DEBUG_MIN)
            echo "Finishing __getCoverArt\n";
    }

    /**
     * @param array             $query          Array {release => name of release we're looking for
     *                                                 year => year of release we're looking for or not set}
     * @param simpleXMLElement  $releaseList
     *
     * @return mbRelease|bool
     */
    private function __getRecordingRelease($query, $releaseList)
    {
        if (MusicBrainz::DEBUG_MODE >= MusicBrainz::DEBUG_MIN)
            echo "Starting " . __METHOD__ . "\n";

        $releaseMatchFound = false;
        $x = 0;
        $releasePercentMatch = $tempReleasePercentMatch = -1000;
        $foundRelease = false;
        $mbRelease = new mbRelease();

        if (isset($query['release']))
        {
            $normalizedReleaseArr = array();
            $normalizedReleaseArr[] = $this->__normalizeString($query['release']);
            $normalizedReleaseArr[] = $this->__normalizeString($query['release'], true);

        }
        else
            $normalizedReleaseArr = null;

        foreach ($releaseList->release as $release)
        {
            if (MusicBrainz::DEBUG_MODE >= MusicBrainz::DEBUG_MED)
                echo "Check release:    " . $release->title . "\n";
            foreach ($normalizedReleaseArr as $normalizedRelease)
            {
                if (stripos($normalizedRelease, $this->__normalizeString($release->title)) === false &&
                    stripos($normalizedRelease, $this->__normalizeString($release->title, true)) === false &&
                    stripos($this->__normalizeString($release->title), $normalizedRelease) === false &&
                    stripos($this->__normalizeString($release->title, true), $normalizedRelease) === false &&
                    $normalizedRelease != $this->__normalizeString($release->title))
                    continue;
                else
                {
                    $releaseMatchFound = true;
                    break;
                }
            }

            if ($releaseMatchFound && isset($query['year']) && (isset($release->date) || isset($release->{'release-event-list'}->{'release-event'}->date)))
            {
                if (MusicBrainz::DEBUG_MODE >= MusicBrainz::DEBUG_MED)
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
                if (MusicBrainz::DEBUG_MODE >= MusicBrainz::DEBUG_MED)
                    echo "Release match found: " . $release->title . "\n";
                similar_text($query['title'], $release->title, $tempReleasePercentMatch);
                $tempReleasePercentMatch += (((30 - $x) / 30) * 10); // matches weighted based on position in results list

                if ($tempReleasePercentMatch > $releasePercentMatch)
                {
                    $mbRelease->setMbID($release->attributes()->id);
                    $mbRelease->setTitle($release->title);
                    $mbRelease->setPercentMatch($tempReleasePercentMatch);
                    $releasePercentMatch = $tempReleasePercentMatch;
                    $foundRelease = true;
                }
            }
            // Increment the release result counter
            $x++;
        }

        return $foundRelease === true ? $mbRelease : false;
    }

    /**
     * @param string     $mbID          mbID for release to lookup
     * @param string     $mbArtistID    artist's mbID associated with release
     * @param int|float  $percentMatch
     *
     * @return bool|mbRelease
     */
    private function __getReleaseDetails ($mbID, $mbArtistID, $percentMatch = null)
    {
        if (MusicBrainz::DEBUG_MODE >= MusicBrainz::DEBUG_MIN)
            echo "Starting " . __METHOD__ . "\n";

        $releaseInfo = $this->musicBrainzLookup('release', $mbID);

        if($releaseInfo)
        {
            $mbRelease = new mbRelease($mbID);
            if(!is_null($mbArtistID) && !empty($mbArtistID))
                $mbRelease->setArtistID($mbArtistID);
            elseif(isset($releaseInfo->release->{'artist-credit'}->{'name-credit'}->artist->attributes()->id))
                $mbRelease->setArtistID($releaseInfo->release->{'artist-credit'}->{'name-credit'}->artist->attributes()->id);
            else // Bail out because we can't set a valid artist ID
            {
                unset($mbRelease);
                return false;
            }
            $mbRelease->setTitle($releaseInfo->release->title);
            $mbRelease->setStatus($releaseInfo->release->status);

            if(isset($releaseInfo->release->date))
                $mbRelease->setReleaseDate($releaseInfo->release->date);
            elseif(isset($releaseInfo->release->{'release-event-list'}->{'release-event'}->date))
                $mbRelease->setReleaseDate($releaseInfo->release->{'release-event-list'}->{'release-event'}->date);

            if(isset($releaseInfo->release->{'release-group'}))
            {
                $mbRelease->setReleaseGroupID($releaseInfo->release->{'release-group'}->attributes()->id);
                if(isset($releaseInfo->release->{'release-group'}->{'tag-list'}))
                {
                    foreach($releaseInfo->release->{'release-group'}->{'tag-list'}->tag as $tag)
                    {
                        if(MusicBrainz::DEBUG_MODE >= MusicBrainz::DEBUG_MIN)
                            echo "__getReleaseDetails: Adding tag: " . $tag->name . "\n";
                        $mbRelease->addTag($tag->name);
                    }
                }
                if(isset($releaseInfo->release->{'release-group'}->rating))
                    $mbRelease->setRating((float)$releaseInfo->release->{'release-group'}->rating);
            }
            if(isset($releaseInfo->release->country))
                $mbRelease->setCountry($releaseInfo->release->country);
            if(isset($releaseInfo->release->asin))
                $mbRelease->setAsin($releaseInfo->release->asin);
            if(isset($releaseInfo->release->{'medium-list'}->medium->{'track-list'}->attributes()->count))
                $mbRelease->setTracks((int)$releaseInfo->release->{'medium-list'}->medium->{'track-list'}->attributes()->count);
            if(isset($releaseInfo->release->{'cover-art-archive'}->front))
                $mbRelease->setCover($releaseInfo->release->{'cover-art-archive'}->front == 'true' ? true : false);
            if(!is_null($percentMatch))
                $mbRelease->setPercentMatch($percentMatch);
        }
        else
            $mbRelease = false;

        return $mbRelease;
    }

    /**
     * @param string         $mbID           mbID of recording to lookup
     * @param string         $mbArtistID     artist's mbID associated with recording
     * @param string|null    $mbReleaseID    release's mbID associated with recording
     * @param float|int|null $percentMatch
     *
     * @return bool|mbTrack
     */
    private function __getRecordingDetails($mbID, $mbArtistID, $mbReleaseID=null, $percentMatch = null)
    {
        if (MusicBrainz::DEBUG_MODE >= MusicBrainz::DEBUG_MIN)
            echo "Starting " . __METHOD__ . "\n";

        $recordingInfo = $this->musicBrainzLookup('recording', $mbID);

        if($recordingInfo)
        {
            $mbRecording = new mbTrack($mbID);
            $mbRecording->setTitle($recordingInfo->recording->title);
            if(!is_null($mbArtistID) && !empty($mbArtistID))
                $mbRecording->setArtistID($mbArtistID);
            else // Bail out because no artist ID was supplied
            {
                unset($mbRecording);
                return false;
            }
            if(!is_null($mbReleaseID) && !empty($mbReleaseID))
            {
                $mbRecording->setAlbumID($mbReleaseID);
                if(isset($recordingInfo->recording->{'release-list'}))
                {
                    foreach($recordingInfo->recording->{'release-list'}->release as $release)
                    {
                        if($release->attributes()->id == $mbReleaseID)
                        {
                            if(isset($release->date))
                            {
                                preg_match('/19\d\d|20\d\d/', $release->date, $year);
                                if(isset($year[0]))
                                    $mbRecording->setYear($year[0]);
                            }
                            elseif(isset($release->{'release-event-list'}->{'release-event'}->date))
                            {
                                preg_match('/19\d\d|20\d\d/', $release->date, $year);
                                if (isset($year[0]))
                                    $mbRecording->setYear($year[0]);
                            }
                            if(isset($release->{'medium-list'}))
                            {
                                $mbRecording->setTrackNumber($release->{'medium-list'}->medium->{'track-list'}->track->number);
                                $mbRecording->setDiscNumber($release->{'medium-list'}->medium->position);
                            }
                            break;
                        }
                    }
                }
            }
            if(isset($recordingInfo->recording->length))
                $mbRecording->setLength($recordingInfo->recording->length);
            if(!is_null($percentMatch))
                $mbRecording->setPercentMatch($percentMatch);
        }
        else
            $mbRecording = false;

        return $mbRecording;
    }

    /**
     * @param string  $mbID             mbID of artist to lookup
     * @param null    $percentMatch
     *
     * @return bool|mbArtist
     */
    private function __getArtistDetails($mbID, $percentMatch = null)
    {
        if (MusicBrainz::DEBUG_MODE >= MusicBrainz::DEBUG_MIN)
            echo "Starting " . __METHOD__ . "\n";

        $artistInfo = $this->musicBrainzLookup('artist', $mbID);

        if($artistInfo)
        {
            $mbArtist = new mbArtist($mbID);
            $mbArtist->setType($artistInfo->artist->attributes()->type);
            $mbArtist->setName($artistInfo->artist->name);
            if(isset($artistInfo->artist->disambiguation))
                $mbArtist->setDisambiguation($artistInfo->artist->disambiguation);
            if($mbArtist->getType() == 'Person')
                $mbArtist->setGender($artistInfo->artist->gender);
            if(isset($artistInfo->artist->country))
                $mbArtist->setCountry($artistInfo->artist->country);
            if(isset($artistInfo->artist->{'life-span'}->begin))
                $mbArtist->setBeginDate($artistInfo->artist->{'life-span'}->begin);
            if(isset($artistInfo->artist->{'life-span'}->end))
                $mbArtist->setEndDate($artistInfo->artist->{'life-span'}->end);
            if(isset($artistInfo->artist->{'tag-list'}))
            {
                foreach($artistInfo->artist->{'tag-list'}->tag as $tag)
                {
                    $mbArtist->addTag($tag->name);
                }
            }
            if(isset($artistInfo->artist->rating))
                $mbArtist->setRating($artistInfo->artist->rating);
            if (!is_null($percentMatch))
                $mbArtist->setPercentMatch($percentMatch);
        }
        else
            $mbArtist = false;

        return $mbArtist;
    }
}

/**
 * Class MBException
 */
class MBException extends Exception{}
