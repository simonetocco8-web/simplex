<?php
/**
* Helper to build a nice looking button (button or link)
*/
class Zend_View_Helper_Calendar extends Zend_View_Helper_Abstract
{ 
	// TODO: perchè Zend e no Maco?
	
	
    protected static $_loaded = false;
	
    /**
    * Loads the textboxlist dependencies
    * 
    * @return string
    */
    public function calendar()
    { 
    	if(!self::$_loaded)
    	{
    		$this->view->headLink()->appendStylesheet($this->view->baseUrl('/css/fullCalendar/fullcalendar.css', true));
    		$this->view->headLink()->appendStylesheet($this->view->baseUrl('/css/tipsy/style.css', true));
//    		$this->view->headLink()->appendStylesheet($this->view->baseUrl('/css/fullCalendar/fullCalendar.print.css', true));

            $this->view->headLink()->appendStylesheet($this->view->baseUrl('/css/jquery/smoothness/jquery-ui-1.8.7.custom.css', true));

            //$this->view->headScript()->prependFile($this->view->baseUrl('/js/jquery-ui-1.8.7.custom.min.js'));
            $this->view->headScript()->prependFile('https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js');

			//$this->view->headScript()->appendFile($this->view->baseUrl('/js/fullCalendar/fullcalendar.min.js'));
			$this->view->headScript()->appendFile($this->view->baseUrl('/js/common/jquery.tipsy.js'));
			$this->view->headScript()->appendFile($this->view->baseUrl('/js/fullCalendar/fullcalendar.js'));

			self::$_loaded = true;
    	}
    	return '';
    } 
} 