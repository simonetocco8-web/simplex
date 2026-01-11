<?php
/**
* Helper to build a nice upload manager
*/
class Zend_View_Helper_Upload extends Zend_View_Helper_Abstract 
{ 
    // TODO: perchè Zend e no Maco?
    
    protected static $_loaded = false;
    
    /**
    * Loads the textboxlist dependencies
    * 
    * @return string
    */
    public function upload()
    { 
        if(!self::$_loaded)
        {
            $this->view->headLink()->appendStylesheet($this->view->baseUrl('/js/uploadify/uploadify.css', true));
            $this->view->headLink()->appendStylesheet($this->view->baseUrl('/css/jquery/ui-lightness/jquery-ui-1.8.7.custom.css', true));

            $this->view->headScript()->appendFile($this->view->baseUrl('/js/jquery-ui-1.8.7.custom.min.js'));
            $this->view->headScript()->appendFile($this->view->baseUrl('/js/uploadify/swfobject.js'));
            $this->view->headScript()->appendFile($this->view->baseUrl('/js/uploadify/jquery.uploadify.v2.1.4.min.js'));
            
            self::$_loaded = true;
        }
        return '';
    } 
} 