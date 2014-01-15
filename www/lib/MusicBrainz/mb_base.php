<?php
/**
 * Project: nZEDbetter
 * User: Randy
 * Date: 1/9/14
 * Time: 2:41 PM
 * File: mb_base.php
 * 
 */

class mb_base {

    private $_mbID = '';
    private $_matchString = '';
    private $_percentMatch = 0;
    private $_tags = array();

    function __construct($_mbID = null)
    {
        if(!is_null($_mbID) && preg_match('/[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}/', $_mbID) === 1)
            $this->_mbID = $_mbID;
        else
            $this->_mbID = '';
    }

    /**
     * @param string $mbID
     */
    public function setMbID($mbID)
    {
        if(preg_match('/[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}/', $mbID) === 1)
            $this->_mbID = $mbID;
    }

    /**
     * @return string
     */
    public function getMbID()
    {
        return $this->_mbID;
    }

    public function addTag($tag)
    {
        // Only include tags that are two words or less to help prevent excessive number of tags
        $words = explode(" ", $tag);
        // Strip some punctuation out to prevent unnecessary redundancies
        $tag = str_replace(array('/', '-', "\\"), ' ', $tag);
        // Skip tags that are years (i.e. 1960s or 60s or 60's or 60 s)
        $yearTag = preg_match('/^(19|20)?\d\d[\' ]?s?$/i', $tag);
        if (!in_array($tag, $this->_tags) && count($this->_tags) < 6 && count($words) < 3 && $yearTag === 0)
            $this->_tags[] = $tag;
        if (MusicBrainz::DEBUG_MODE >= MusicBrainz::DEBUG_MIN)
            echo "[mb_base::addTag] Tag result: " . $tag . "\n";
    }

    /**
     * @return array
     */
    public function getTags()
    {
        return $this->_tags;
    }

    /**
     * @param string $matchString
     */
    public function setMatchString($matchString)
    {
        $this->_matchString = $matchString;
    }

    /**
     * @return string
     */
    public function getMatchString()
    {
        return $this->_matchString;
    }

    /**
     * @param int $percentMatch
     */
    public function setPercentMatch($percentMatch)
    {
        $this->_percentMatch = $percentMatch;
    }

    /**
     * @return int
     */
    public function getPercentMatch()
    {
        return $this->_percentMatch;
    }



} 