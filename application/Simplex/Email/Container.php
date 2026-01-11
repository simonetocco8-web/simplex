<?php
/**
 * Created by Marcello Stani.
 * Date: 01/07/13
 * Time: 13.06
 */

class Simplex_Email_Container
{
    // The stuff
    private $_data = array();

    // Get something from the stuff
    public function __get($key) {
        return $this->_data[$key];
    }

    // Add something to the stuff
    public function __set($key, $value) {
        $this->_data[$key] = $value;
    }

}