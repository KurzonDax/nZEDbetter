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

    function __construct($_mbID)
    {
        $this->_mbID = $_mbID;
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



} 