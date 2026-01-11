<?php
/**
 * Created by Marcello Stani.
 * User: Marcello
 * Date: 20/04/12
 * Time: 11.39
 */

class Model_Templates{

    const COMPANY_NOTASKS = 'company_notasks';
    const COMPANY_TASKS = 'company_tasks';
    const RALI = 'rali';
    const INVOICE = 'invoice';
    const CARTA_INTESTATA = 'ci';
    const CONFERMA_APPUNTAMENTO = 'ca';

    protected $_default_templates = array(
        self::COMPANY_NOTASKS => 'company-notasks-default.docx',
        self::COMPANY_TASKS => 'company-tasks-default.docx',
        self::RALI => 'rali-default.docx',
        self::INVOICE => 'fattura-2-default.docx',
        self::CARTA_INTESTATA => 'ci-default.docx',
        self::CONFERMA_APPUNTAMENTO => 'ca-default.docx',
    );

    protected $_templates = array(
        self::COMPANY_NOTASKS => 'company-notasks.docx',
        self::COMPANY_TASKS => 'company-tasks.docx',
        self::RALI => 'rali.docx',
        self::INVOICE => 'fattura-2.docx',
        self::CARTA_INTESTATA => 'ci.docx',
        self::CONFERMA_APPUNTAMENTO => 'ca.docx',
    );

    protected $_template_names = array(
        self::COMPANY_NOTASKS => 'Scheda Azienda senza Impegni',
        self::COMPANY_TASKS => 'Scheda Azienda con Impegni',
        self::RALI => 'RALI',
        self::INVOICE => 'Fattura',
        self::CARTA_INTESTATA => 'Carta Intestata',
        self::CONFERMA_APPUNTAMENTO => 'Conferma Appuntamento',
    );

    public function getTemplates()
    {
        return $this->_template_names;
    }

    public function uploadTemplate($what)
    {
        if(!isset($this->_templates[$what]))
        {
            throw new Exception('this template not exists');
        }

        $template_name = $this->_templates[$what];

        $repo = new Model_FilesMapper();

        $path = $repo->getTemplatePath() . $template_name;

        if (!empty($_FILES)) {

            $tempFile = $_FILES['Filedata']['tmp_name'];

            $targetFile = $_FILES['Filedata']['name'];

            if(file_exists($path))
            {
                // sovrascrive
                unlink($path);
            }

            if(move_uploaded_file($tempFile, $path))
            {
                return true;
            }
            else
            {
                return false;
            }
        }

        return false;
    }

    public function downloadTemplate($what)
    {
        $filesMapper = new Model_FilesMapper();
        if(!isset($this->_templates[$what]))
        {
            throw new Exception('this template not exists');
        }

        $template_name = $this->_templates[$what];

        $template_path = $filesMapper->getTemplatePath(false) . $template_name;

        if(!$filesMapper->pathExists($template_path))
        {
            throw new Exception('template file not found: ' . $template_path);
        }

        $filesMapper->download($template_path);
        return true;
    }

    public function restoreTemplate($what)
    {
        if(!isset($this->_templates[$what]))
        {
            throw new Exception('this template not exists');
        }

        $template_name = $this->_templates[$what];
        $default_template_name = $this->_default_templates[$what];

        $repo = new Model_FilesMapper();
        $path = $repo->getTemplatePath() . $template_name;
        $default_path = $repo->getTemplatePath() . $default_template_name;

        if(file_exists($path))
        {
            // sovrascrive
            unlink($path);
        }

        return copy($default_path, $path);
    }
}