<?php
/**
* Helper to build a nice looking button (button or link)
*/
class Zend_View_Helper_TextboxList extends Zend_View_Helper_Abstract 
{ 
	// TODO: perchè Zend e no Maco?
	
	
    protected static $_loaded = false;
	
    /**
    * Loads the textboxlist dependencies
    * 
    * @return string
    */
    public function textboxList()
    { 
    	if(!self::$_loaded)
    	{
    		$this->view->headLink()->appendStylesheet($this->view->baseUrl('/css/TextboxList/TextboxList.css', true));
    		$this->view->headLink()->appendStylesheet($this->view->baseUrl('/css/TextboxList/TextboxList.Autocomplete.css', true));

			$this->view->headScript()->appendFile($this->view->baseUrl('/js/common/TextboxList/GrowingInput.js'));
			$this->view->headScript()->appendFile($this->view->baseUrl('/js/common/TextboxList/TextboxList.js'));
			$this->view->headScript()->appendFile($this->view->baseUrl('/js/common/TextboxList/TextboxList.Autocomplete.js'));
			
			self::$_loaded = true;
    	}
    	return '';
    } 
} 