<?php
/**
 * Project: nZEDbetter
 * User: Randy
 * Date: 1/9/14
 * Time: 4:16 PM
 * File: mbArtist.php
 * 
 */

class mbArtist extends mb_base {



    private $_name = '';
    private $_type = '';
    private $_description = '';
    private $_tags = array();
    private $_country = '';
    private $_rating = 0.0;
    private $_beginDate = '';
    private $_endDate = '';


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
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->_description = $description;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->_description;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->_name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * @param float $rating
     */
    public function setRating($rating)
    {
        if(is_float($rating))
            $this->_rating = $rating;
    }

    /**
     * @return float
     */
    public function getRating()
    {
        return $this->_rating;
    }

    /**
     * @param string $tag
     */
    public function addTag($tag)
    {
        if(!in_array($tag, $this->_tags) && count($this->_tags) < 6)
            $this->_tags[] = $tag;
    }

    /**
     * @return array
     */
    public function getTags()
    {
        return $this->_tags;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        if($type == 'Person' || $type == 'Group')
            $this->_type = $type;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * @param string $beginDate
     */
    public function setBeginDate($beginDate)
    {
        $this->_beginDate = $beginDate;
    }

    /**
     * @return string
     */
    public function getBeginDate()
    {
        return $this->_beginDate;
    }

    /**
     * @param string $endDate
     */
    public function setEndDate($endDate)
    {
        $this->_endDate = $endDate;
    }

    /**
     * @return string
     */
    public function getEndDate()
    {
        return $this->_endDate;
    }


} 