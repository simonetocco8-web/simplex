<?php
/**
 * Created by Marcello Stani.
 * Date: 30/07/13
 * Time: 16.22
 */

class ExportController extends Zend_Controller_Action
{
    public function init()
    {
        /*
        $ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('list', 'html')
            ->addActionContext('percentforpartner', 'json')
            ->addActionContext('tbl', 'json')
            ->addActionContext('detail', 'json')
            ->addActionContext('acaaddress', 'json')
            ->addActionContext('exclude', 'json')
            ->addActionContext('lo', 'json')
            ->addActionContext('lo', 'html')
            ->initContext();
        */
    }

    public function companiesAction()
    {
        if($w = $this->_request->getParam('w'))
        {
            $mod = new Model_Modulistica();
            $params = $this->_request->getParams();
            $mod->exec($params);
            return;
        }
    }

    public function tasksAction()
    {
        if($w = $this->_request->getParam('w'))
        {
            $mod = new Model_Modulistica();
            $params = $this->_request->getParams();
            $mod->exec($params);
            return;
        }
    }
}
