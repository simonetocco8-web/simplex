<?php

class Simplex_Importer_Manager
{
    const COMPANIES   = 'companies';
    const CONTACTS    = 'contacts';
    const PARTNERS    = 'partners';
    const OFFERS      = 'offers';
    const ORDERS      = 'orders';

    protected $_subject;
    
    protected $_part = false;
    
    protected $_file_path;
    
    protected $_libs_loaded = false;
    
    protected $_allowedSubjects = array(
        self::COMPANIES,
        self::CONTACTS,
        self::PARTNERS,
        self::OFFERS,
        self::ORDERS,
    );
    
    protected $_db;
    
    public function __construct()
    {
        $this->_db = Zend_Registry::get('dbAdapter');
        $this->_config = new Simplex_Importer_Config();
    }
    
    public function setSubject($subject, $part = false)
    {
        if(!in_array($subject, $this->_allowedSubjects))
        {
            throw new Exception('subject not valid!');
        }
        
        $this->_part = $part;
        $this->_subject = $subject;
    }
    
    public function setFile($file_path)
    {
        if(!file_exists($file_path))
        {
            throw new Exception('file not found!');
        }
        
        $this->_file_path = $file_path;
    }
    
    public function import()
    {
        $initial = memory_get_peak_usage();
        echo 'Initial memory: ' . $initial . '<br />';
        $this->_initLibs();
        
        $reader = $this->_getReader();
        
        $objWorksheet = $reader->getSheet(0);
        
        switch($this->_subject)
        {
            case self::COMPANIES:
                $importer = new Simplex_Importer_Companies();
                break;
            case self::CONTACTS:
                $importer = new Simplex_Importer_Contacts();
                break;
            case self::PARTNERS:
                $importer = new Simplex_Importer_Partners();
                break;
            case self::OFFERS:
                $importer = new Simplex_Importer_Offers();
                break;
            case self::ORDERS:
                $importer = new Simplex_Importer_Orders();
                break;
        }
        
        $importer->setReader($reader);
        $importer->setConfig($this->_config);
        $importer->setDb($this->_db);
        
        $importer->import();
        
        $this->_config->save();
        
        $final = memory_get_peak_usage();
        echo 'Final memory: ' . $final . '<br />';
        echo 'Delta: ' . ($final - $initial) . '<br />';
        exit;
        echo 'yup!';
        return true;
    }
    
    protected function _initLibs()
    {
        if(!$this->_libs_loaded)
        {
            set_include_path(LIBRARY_PATH . '/PHPExcel' .
                    //set_include_path(APPLICATION_PATH . DS . 'Prometeo' . DS . 'PHPExcel' .
                    PATH_SEPARATOR .
                    get_include_path());

            include 'PHPExcel.php';
            
            /** PHPExcel_IOFactory */
            include 'PHPExcel/IOFactory.php';
            
            $this->_libs_loaded = true;
        }
    }
    
    protected function _getReader()
    {
        $objReader = PHPExcel_IOFactory::createReader('Excel2007');
        $objReader->setReadDataOnly(true);

        $objPHPExcel = $objReader->load($this->_file_path);

        return $objPHPExcel;
    }
}
