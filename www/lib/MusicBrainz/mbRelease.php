<?php
/**
 * Project: nZEDbetter
 * User: Randy
 * Date: 1/9/14
 * Time: 4:30 PM
 * File: mbRelease.php
 * 
 */

class mbRelease extends mb_base{

    private $_title = '';
    private $_artistID = '';
    private $_releaseDate;
    private $_releaseGroupID = '';
    private $_description = null;
    private $_tracks = 0;
    private $_cover = false;
    private $_rating = null;
    private $_asin = false;
    private $_status = '';
    private $_country = '';

    /**
     * @param string $country
     */
    public function setCountry($country)
    {
        $this->_country = $country;
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->_country;
    }


    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->_status = $status;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->_status;
    }

    /**
     * @param string $artistID
     */
    public function setArtistID($artistID)
    {
        if (preg_match('/[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}/', $artistID) === 1)
            $this->_artistID = $artistID;
    }

    /**
     * @return string
     */
    public function getArtistID()
    {
        return $this->_artistID;
    }

    /**
     * @param bool $asin
     */
    public function setAsin($asin)
    {
        $this->_asin = $asin;
    }

    /**
     * @return bool
     */
    public function getAsin()
    {
        return $this->_asin;
    }

    /**
     * @param bool|string $cover
     */
    public function setCover($cover)
    {
        $this->_cover = $cover;
    }

    /**
     * @return bool
     */
    public function getCover()
    {
        return $this->_cover;
    }

    /**
     * @param null|string $description
     */
    public function setDescription($description)
    {
        $this->_description = $description;
    }

    /**
     * @return null|string
     */
    public function getDescription()
    {
        return $this->_description;
    }

    /**
     * @param null|float $rating
     */
    public function setRating($rating)
    {
        if(is_float($rating))
            $this->_rating = $rating;
    }

    /**
     * @return null|float
     */
    public function getRating()
    {
        return $this->_rating;
    }

    /**
     * @param null|string $releaseDate
     */
    public function setReleaseDate($releaseDate)
    {
        $this->_releaseDate = $releaseDate;
    }

    /**
     * @return null|string
     */
    public function getReleaseDate()
    {
        return $this->_releaseDate;
    }

    public function getYear()
    {
        if(!is_null($this->_releaseDate) && !empty($this->_releaseDate))
        {
            preg_match('/(19|20)\d\d/', $this->_releaseDate, $_matchYear);
            return isset($_matchYear[0]) ? $_matchYear[0] : null;
        }
        else
            return null;
    }

    /**
     * @param string $releaseGroupID
     */
    public function setReleaseGroupID($releaseGroupID)
    {
        if (preg_match('/[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}/', $releaseGroupID) === 1)
            $this->_releaseGroupID = $releaseGroupID;
    }

    /**
     * @return string
     */
    public function getReleaseGroupID()
    {
        return $this->_releaseGroupID;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->_title = $title;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->_title;
    }

    /**
     * @param int $tracks
     */
    public function setTracks($tracks)
    {
        if(is_numeric($tracks))
            $this->_tracks = $tracks;
    }

    /**
     * @return int
     */
    public function getTracks()
    {
        return $this->_tracks;
    }

} 