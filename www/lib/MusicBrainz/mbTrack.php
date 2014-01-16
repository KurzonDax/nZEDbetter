<?php
/**
 * Project: nZEDbetter
 * User: Randy
 * Date: 1/9/14
 * Time: 5:01 PM
 * File: mbTrack.php
 * 
 */

class mbTrack extends mb_base{

    private $_albumID = '';
    private $_artistID = '';
    private $_year = null;
    private $_trackNumber = null;
    private $_title = '';
    private $_length = 0;
    private $_discNumber = null;
    /**
     * @param string $albumID
     */
    public function setAlbumID($albumID)
    {
        if (preg_match('/[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}/', $albumID) === 1)
            $this->_albumID = $albumID;
    }

    /**
     * @return string
     */
    public function getAlbumID()
    {
        return $this->_albumID;
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
     * @param int $length
     */
    public function setLength($length)
    {
        if(is_numeric($length))
            $this->_length = $length;
    }

    /**
     * @return int
     */
    public function getLength()
    {
        return $this->_length;
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
     * @param null|int $trackNumber
     */
    public function setTrackNumber($trackNumber)
    {
        if(is_integer($trackNumber))
            $this->_trackNumber = $trackNumber;
    }

    /**
     * @return null|int
     */
    public function getTrackNumber()
    {
        if (is_null($this->_trackNumber))
            return "NULL";
        else
            return $this->_trackNumber;
    }

    /**
     * @param null|int $year
     */
    public function setYear($year)
    {
        if(is_integer($year) && preg_match('/(19|20)\d\d/', $year) === 1)
            $this->_year = $year;
    }

    /**
     * @return null|int
     */
    public function getYear()
    {
        if(is_null($this->_year))
            return "NULL";
        else
            return $this->_year;
    }

    /**
     * @param int $discNumber
     */
    public function setDiscNumber($discNumber)
    {
        $this->_discNumber = $discNumber;
    }

    /**
     * @return int|null
     */
    public function getDiscNumber()
    {
        if (is_null($this->_discNumber))
            return "NULL";
        else
            return $this->_discNumber;
    }




} 