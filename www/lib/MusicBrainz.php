<?php
/**
 * Project: nZEDb
 * User: Randy
 * Date: 9/7/13
 * Time: 2:00 PM
 * File: MusicBrainz.php
 * Class for retrieving music info from a MusicBrainz replication server.  To configure your own
 * replication server, see http://nzedbetter.org/index.php?title=MusicBrainz
 */
require_once(WWW_DIR."lib/site.php");
require_once(WWW_DIR."lib/framework/db.php");

class MusicBrainz {

    const POST = 'post';
    const GET = 'get';
    const HEAD = 'head';
    const API_VERSION = '2';
    const API_SCHEME = "http://";
    const DEBUG_MODE = true;

    function MusicBrainz()
    {
        $s = new Sites();
        $site = $s->get();
        $this->MBserver = (!empty($site->musicBrainzServer)) ? $site->musicBrainzServer : "musicbrainz.org";

    }

    private function _makeSearchCall($searchFunction, $field = '' , $query = '', $limit=10)
    {


        $url = MusicBrainz::API_SCHEME.$this->MBserver.'/ws/'.MusicBrainz::API_VERSION.'/'.$searchFunction.'?query='.($field=='' ? '' : $field.'%3A').rawurlencode($query)."&limit=".$limit;

        if(MusicBrainz::DEBUG_MODE)
            echo "\nURL: ".$url."\n";

        if (extension_loaded('curl'))
        {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,$url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

            $xml_response = curl_exec($ch);
            if ($xml_response === false)
            {
                curl_close($ch);
                return false;
            }
            else
            {
                /* parse XML */
                $parsed_xml = @simplexml_load_string($xml_response);
                curl_close($ch);
                // return ($parsed_xml === false) ? false :  json_decode(json_encode($parsed_xml), 1);
                return $parsed_xml;
            }
        }
        else
        {
            throw new MBException('CURL-extension not loaded');
        }

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

        $url = MusicBrainz::API_SCHEME.$this->MBserver.'/ws/'.MusicBrainz::API_VERSION.'/'.$entity.'/'.$mbid;

        if (extension_loaded('curl'))
        {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,$url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

            $xml_response = curl_exec($ch);
            if ($xml_response === False)
            {
                curl_close($ch);
                return False;
            }
            else
            {
                /* parse XML */
                $parsed_xml = @simplexml_load_string($xml_response);
                curl_close($ch);
                return ($parsed_xml === False) ? False : $parsed_xml;
            }
        }
        else
        {
            throw new MBException('CURL-extension not loaded');
        }

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
            return $this->_makeSearchCall('artist', $field, $query, $limit);

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
            return $this->_makeSearchCall('cdstub', $field, $query, $limit);

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
            return $this->_makeSearchCall('label', $field, $query, $limit);

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
            return $this->_makeSearchCall('recording', $field1, $query, $limit);
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
            return $this->_makeSearchCall('release-group', $field, $query, $limit);
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
            return $this->_makeSearchCall('release', $field1, $query, $limit);
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
            return $this->_makeSearchCall('work', $field, $query, $limit);

    }


}

class MBException extends Exception{}
