<?php

class AddressesController extends Zend_Controller_Action       
{
    public function init()
    {
        $ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('edit', 'html')
            ->addActionContext('save', 'html')
            ->initContext();
    }
    
    public function editAction()
    {
        $commonModel = new Model_Common();
        // carico le province
        $province = $commonModel->getArrayForSelectElementSimple('province', 'id', 'nomeprovincia');
        $this->view->province = array(0 => '') + $province;
        
        unset($commonModel);

        $id = $this->_request->getParam('id', false);

        $model = new Model_AddressesMapper();

        if($id)
        {
            $this->view->data = $model->getDetail($id);
            $this->view->contentTitle = "Modifica Indirizzo";
        }
        else
        {
            $this->view->data = $model->getEmptyDetail();
            $this->view->contentTitle = "Nuovo Indirizzo";
        }

        $this->view->inputNamePrefix = '';
    }
    
    public function deleteAction()
    {
        $ids = $this->_request->getParam('id', false);

        if(!$ids)
        {
            throw new Zend_Controller_Action_Exception('Necessario id per l\'eliminazione utente', 404);
        }

        $model = new Model_AddressesMapper();

        $result = $model->delete($ids);
        
        $this->view->result = array('result' => $result);

        //$this->_redirect('contacts/list');
    }
}
