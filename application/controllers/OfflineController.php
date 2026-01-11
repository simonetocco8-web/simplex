<?php
/**
 * Created by Marcello Stani.
 * User: Marcello
 * Date: 18/12/11
 * Time: 20.06
 */

class OfflineController extends Zend_Controller_Action
{
    public function indexAction()
    {
        Zend_Layout::getMvcInstance()->assign('title', 'simpl.ex :: offline');
        $this->_helper->layout->setLayoutPath(dirname(dirname(__FILE__)) . '/layouts/scripts')
            ->setLayout('login-layout');
    }
}