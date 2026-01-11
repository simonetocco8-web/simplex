<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
	protected function _initDoctype()
	{
		$this->bootstrap('view');
		$view = $this->getResource('view');
		$view->doctype('XHTML1_STRICT');

		/*
		 $view->navigation = array();
		 $view->subnavigation = array();
		 $view->headTitle( 'Module One' );                 
		 $view->headLink()->appendStylesheet('/css/clear.css');
		 $view->headLink()->appendStylesheet('/css/main.css');
		 $view->headScript()->appendFile('/js/jquery.js');
		 */
	}
    
    public function _initDb()
    {
        $config = $this->getOptions();
        
        // TODO: controlli vari (isDefaultAdapter)
        //$adapter = new Maco_Db_Adapter_Mysqli($config['resources']['db']['params']);
        $adapter = new Zend_Db_Adapter_Mysqli($config['resources']['db']['params']);
        
		Zend_Db_Table_Abstract::setDefaultAdapter($adapter);
        return $adapter;
    }
    
    protected function _initConfig()
    {
        $config = new Zend_Config($this->getOptions(), true);
        Zend_Registry::set('config', $config);
        return $config;
    }


	protected function _initAutoloader()
	{
		$autoloader = new Zend_Application_Module_Autoloader(
		array(
                'namespace' => '', 
                'basePath' => APPLICATION_PATH, 
		)
		);
		$autoloader->addResourceType('simplex', 'Simplex/', 'Simplex');
		return $autoloader;
	}
	protected function _initReqistry()
	{
		$this->bootstrap('db');
		$dbAdapter = $this->getResource('db');
		Zend_Registry::set('dbAdapter', $dbAdapter);

		if($this->hasPluginResource('log'))
		{
			$res = $this->getPluginResource('log');
			$log = $res->getLog();
			Zend_Registry::set('log', $log);
		}
	}

	public function _initView()
	{
		$view = new Zend_View();
		ZendX_JQuery::enableView($view);
		$viewrenderer = new Zend_Controller_Action_Helper_ViewRenderer();
		$viewrenderer->setView($view);
		Zend_Controller_Action_HelperBroker::addHelper($viewrenderer);
		ZendX_JQuery_View_Helper_JQuery::enableNoConflictMode();
		
		return $view;
	}

    public function _initLogger()
    {
        $logger = new Zend_Log();
        $writer = new Zend_Log_Writer_Firebug();
        $logger->addWriter($writer);
        Zend_Registry::set('logger',$logger);

        return $logger;
    }
    
    public function _initValidateTranslator()
    {
        $this->bootstrap('locale');
        $locale = $this->getResource('locale');
        
        Zend_Registry::set('Zend_Locale', $locale);

        $translationPath = dirname( APPLICATION_PATH ) 
            . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'languages';

        $translator = new Zend_Translate(
            array(
                'adapter' => 'array',
                'content' => $translationPath,
                'locale'  => $locale,
                'scan' => Zend_Translate::LOCALE_DIRECTORY
            )
        );
        
        Zend_Registry::set('Zend_Translate', $translator);
        Zend_Validate_Abstract::setDefaultTranslator($translator);
        Zend_Form::setDefaultTranslator($translator);
    }
    
    public function _initKint()
    {
        include(LIBRARY_PATH . '/Kint/kint.class.php');
    }

    /**
     * Set up the queue
     *
     */
    protected function _initQueue()
    {
        $options = $this->getOptions();

        // Create an adapter for our queue and register it.
        $queueAdapter = new Zend_Queue_Adapter_Db( $options[ 'queue' ] );
        Zend_Registry::getInstance()->queueAdapter = $queueAdapter;

    }
}