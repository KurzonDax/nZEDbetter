<?php
/**
 * Project: nZEDbetter
 * User: Randy
 * Date: 9/7/13
 * Time: 2:00 PM
 * File: MusicBrainz.php
 * Class for retrieving music info from a MusicBrainz replication server.  To configure your own
 * replication server, see http://nzedbetter.org/index.php?title=MusicBrainz
 */
require_once(WWW_DIR . "lib/site.php");
require_once(WWW_DIR . "lib/framework/db.php");
require_once(WWW_DIR . "lib/releaseimage.php");
require_once(WWW_DIR . "lib/amazon.php");
require_once(WWW_DIR . "lib/MusicBrainz/mb_base.php");
require_once(WWW_DIR . "lib/MusicBrainz/mbArtist.php");
require_once(WWW_DIR . "lib/MusicBrainz/mbRelease.php");
require_once(WWW_DIR . "lib/MusicBrainz/mbTrack.php");

class MusicBrainz {

    const POST = 'post';
    const GET = 'get';
    const HEAD = 'head';
    const API_VERSION = '2';
    const API_SCHEME = "http://";
    const DEBUG_MODE = false;
    const COVER_ART_BASE_URL = "http://coverartarchive.org/release/";

    private $_MBserver = '';
    private $_throttleRequests = false;
    private $_applicationName = 'nZEDbetter';
    private $_applicationVersion = '';
    private $_email = null;
    private $_imageSavePath = '';
    private $_isAmazonValid = false;
    private $_amazonPublicKey = '';
    private $_amazonPrivateKey = '';
    private $_amazonTag = '';

    function construct()
    {
        $s = new Sites();
        $site = $s->get();
        $this->_MBserver = (!empty($site->musicBrainzServer)) ? $site->musicBrainzServer : "musicbrainz.org";
        $this->_email = !empty($site->email) ? $site->email : null;
        $this->_applicationVersion = $site->NZEDBETTER_VERSION;
        $this->_imageSavePath = WWW_DIR . "covers/music/";
        $this->_amazonPrivateKey = !empty($site->amazonprivkey) ? $site->amazonprivkey : '';
        $this->_amazonPublicKey = !empty($site->amazonpubkey) ? $site->amazonpubkey : '';
        $this->_amazonTag = !empty($site->amazonassociatetag) ? $site->amazonassociatetag : '';

        if($this->_amazonPrivateKey != '' && $this->_amazonPublicKey != '' && $this->_amazonTag != '')
            $this->_isAmazonValid = true;

        if(stripos($this->_MBserver, 'musicbrainz.org') === false)
        {
            $this->_throttleRequests = false;
        }
        else
        {
            $this->_throttleRequests = true;
            if(is_null($this->_email) ||
            preg_match('/[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+(?:[A-Z]{2}|com|org|net|gov|mil|biz|info|mobi|name|aero|jobs|museum)\b/i', $this->_email) === 0)
            {
                echo "\n\033[01;31mALERT!!! You have not set a valid email address in Admin->Site Settings.\n";
                echo "The MusicBrainz integration will not function until this is corrected.\n\n";
                throw new MBException('Invalid email address');
            }
        }

    }

    private function __makeSearchCall($searchFunction, $field = '' , $query = '', $limit=10)
    {

        $url = MusicBrainz::API_SCHEME.$this->_MBserver.'/ws/'.MusicBrainz::API_VERSION.'/'.$searchFunction.'?query='.($field=='' ? '' : $field.'%3A').rawurlencode($query)."&limit=".$limit;

        return $this->__getResponse($url);

    }

    public function musicBrainzLookup($entity, $mbid)
    {
        // $entity must be one of the following:
        // artist, label, recording, release, release-group, work, area, url
        if(is_null($entity) || empty($entity) || is_null($mbid) || empty($mbid))
            return false;

        $validEntities = array('artist', 'label', 'recording', 'release', 'release-group', 'work', 'area', 'url');

        if(!in_array($entity, $validEntities))
            return false;

        $url = MusicBrainz::API_SCHEME.$this->_MBserver.'/ws/'.MusicBrainz::API_VERSION.'/'.$entity.'/'.$mbid;

        return $this->__getResponse($url);

    }

    public function searchArtist($query, $field='', $limit=10)
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
        if(empty($query) || is_null($query))
            return false;
        else
            return $this->__makeSearchCall('artist', $field, $query, $limit);

    }

    public function searchCDstubs($query, $field='title',$limit=10)
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

    public function searchLabel($query, $field='',$limit=10)
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

    public function searchRecording($query1, $field1='recording', $query2='', $field2='artist',$limit=30)
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

        if(empty($query1) || is_null($query1))
            return false;
        else
        {
            $query=$query1.(($query2 != '') ? " AND ".$field2.":".$query2 : '');
            return $this->__makeSearchCall('recording', $field1, $query, $limit);
        }
    }

    public function searchReleaseGroup($query, $field='releasegroup',$limit=10)
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

    public function searchRelease($query1, $field1='release', $query2='', $field2='artistname',$limit=10)
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

        if(empty($query1) || is_null($query1))
            return false;
        else
        {
            $query=$query1.(($query2 != '') ? " AND ".$field2.":".$query2 : '');
            return $this->__makeSearchCall('release', $field1, $query, $limit);
        }
    }

    /**
     * @param $query
     * @param string $field
     * @param int $limit
     * @return bool|mixed
     */
    public function searchWork($query, $field='work',$limit=10)
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

    public  function normalizeString($text, $includeArticles = false)
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
     * @param array  $searchArray Array to append results to
     *
     * @return array
     *
     * This function builds an array of strings based on rules defined within the
     * function.  The array is then used to compare release search results against.
     */
    public function buildReleaseSearchArray($text, $searchArray)
    {
        $searchArray[] = $text;
        $searchArray[] = $this->normalizeString($text);
        $searchArray[] = $this->normalizeString($text, true);

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

    public function updateArtist(mbArtist $artist)
    {

        $db = new DB();
        if($artist->getMbID() == '' || is_null($artist->getMbID()))
            return false;

        $searchExisting = $db->queryOneRow("SELECT mbID FROM mbArtists WHERE mbID=" . $db->escapeString($artist->getMbID()));
        if(!$searchExisting)
        {
            $sql = "INSERT INTO mbArtists (mbID, name, type, description, genreID, country, rating, beginDate, endDate) VALUES (" .
                     $db->escapeString($artist->getMbID()) . ", " . $db->escapeString($artist->getName()) . ", " . $db->escapeString($artist->getType()) . ", " .
                     $db->escapeString($artist->getDescription()) . ", " . $db->escapeString(implode(", ", $artist->getTags())) . ", " . $db->escapeString($artist->getCountry()) . ", " .
                     $artist->getRating() . "," . $db->escapeString($artist->getBeginDate()) . "," . $db->escapeString($artist->getEndDate()) . ")";

            return $db->queryInsert($sql);
        }
        else
        {
            $sql = "UPDATE mbArtists SET name=" . $db->escapeString($artist->getName()) . ", type=" . $db->escapeString($artist->getType()) . ", description=" . $db->escapeString($artist->getDescription()) .
                    ", genres=" . $db->escapeString(implode(", ", $artist->getTags())) . ", country=" . $db->escapeString($artist->getCountry()) . ", rating=" . $artist->getRating() .
                    ", beginDate=" . $db->escapeString($artist->getBeginDate()) . ", endDate=" . $db->escapeString($artist->getEndDate()) . ", updateDate=" . time() .
                    " WHERE mbID=" . $db->escapeString($artist->getMbID());

            return $db->queryDirect($sql);
        }
    }

    public function updateAlbum(mbRelease $release)
    {

        if($release->getMbID() == '' || is_null($release->getMbID()))
            return false;

        $db = new DB();
        $searchExisting = $db->queryOneRow("SELECT mbID FROM mbAlbums WHERE mbID=" . $db->escapeString($release->getMbID()));
        if(!$searchExisting)
        {
            $this->__getCoverArt($release);

            $sql = "INSERT INTO mbAlbums (mbID, artistID, title, year, releaseDate, releaseGroupID, description, tracks, genres, cover, rating, asin) VALUES " .
                    "(" . $db->escapeString($release->getMbID()) . ", " . $db->escapeString($release->getArtistID()) .
                    ", " . $db->escapeString($release->getTitle()) . ", " . $db->escapeString($release->getYear()) .
                    ", " . $db->escapeString($release->getReleaseDate()) . ", " . $db->escapeString($release->getReleaseGroupID()) .
                    ", " . $db->escapeString($release->getDescription()) . ", " . $release->getTracks() .
                    ", " . $db->escapeString(implode(", ", $release->getTags())) . ", " . $db->escapeString($release->getCover()) .
                    ", " . $release->getRating() . ", " . ", " . $db->escapeString($release->getAsin());

            return $db->queryInsert($sql);
        }
        else
        {
            $this->__getCoverArt($release);

            $sql = "UPDATE mbAlbums SET artistID=" . $db->escapeString($release->getArtistID()) . ", title=" . $db->escapeString($release->getTitle()) .
                    ", year=" . $db->escapeString($release->getYear()) . ", releaseDate=" . $db->escapeString($release->getReleaseDate()) .
                    ", releaseGroupID=" . $db->escapeString($release->getReleaseGroupID()) . ", description=" . $db->escapeString($release->getDescription()) .
                    ", tracks=" . $release->getTracks() . ", genres=" . $db->escapeString(implode(", ", $release->getTags())) .
                    ", cover=" . $db->escapeString($release->getCover()) . ", rating=" . $release->getRating() .
                    ", asin=" . $db->escapeString($release->getAsin()) . ", updateDate=" . time() . " WHERE mbID=" . $db->escapeString($release->getMbID());

            return $db->queryDirect($sql);
        }

    }

    protected  function __getResponse($url)
    {

        if (extension_loaded('curl'))
        {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_USERAGENT, $this->_applicationName . "/" . $this->_applicationVersion . "( " . $this->_email . " )");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

            if ($this->_throttleRequests)
            {
                if (is_null($this->_email) ||
                    preg_match('/[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+(?:[A-Z]{2}|com|org|net|gov|mil|biz|info|mobi|name|aero|jobs|museum)\b/i', $this->_email) === 0)
                {
                    echo "\n\033[01;31mALERT!!! You have not set a valid email address in Admin->Site Settings.\n";
                    echo "The MusicBrainz integration will not function until this is corrected.\n\n";
                    return false;
                }
                else //The following is REQUIRED if using musicbrainz.com for the server, per http://musicbrainz.org/doc/XML_Web_Service/Rate_Limiting
                    usleep(9000);
            }

            $xml_response = curl_exec($ch);
            if ($xml_response === false)
            {
                curl_close($ch);

                return false;
            } else
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

    private function __getCoverArt(mbRelease &$release)
    {
        $releaseImage = new ReleaseImage();
        if ($release->getCover() == true)
        {
            $imageName = "mb-" . $release->getMbID() . "-cover";
            $imageUrl = MusicBrainz::COVER_ART_BASE_URL . $release->getMbID() . "/front";
            $imageSave = $releaseImage->saveImage($imageName, $imageUrl, $this->_imageSavePath);
            $release->setCover(($imageSave ? $imageName . ".jpg" : 'NULL'));
        }
        elseif ($release->getAsin() != false && $this->_isAmazonValid)
        {
            // Get from Amazon if $release->asin != false and valid Amazon keys have been provided
            $amazon = new AmazonProductAPI($this->_amazonPublicKey, $this->_amazonPrivateKey, $this->_amazonTag);
            try
            {
                $amazonResults = $amazon->getItemByAsin($release->getAsin(), "com", "ItemAttributes,Images");
                if (isset($amazonResults->Items->Item->ImageSets->ImageSet->LargeImage->URL) && !empty($amazonResults->Items->Item->ImageSets->ImageSet->LargeImage->URL))
                {
                    $imageUrl = $amazonResults->Items->Item->ImageSets->ImageSet->LargeImage->URL;
                    $imageName = "mb-" . $release->getMbID() . "-cover";
                    $imageSave = $releaseImage->saveImage($imageName, $imageUrl, $this->_imageSavePath);
                    $release->setCover(($imageSave ? $imageName . ".jpg" : 'NULL'));
                }
            }
            catch (Exception $e)
            {
                $release->setCover('NULL');
            }
        }
        else
        {
            $release->setCover('NULL');
        }
    }
}

class MBException extends Exception{}
