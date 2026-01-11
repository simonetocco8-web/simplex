<?php
/**
 * Created by JetBrains PhpStorm.
 * User: marcello
 * Date: 27/05/13
 * Time: 11.35
 * To change this template use File | Settings | File Templates.
 */


class UsersController extends Zend_Controller_Action
{
    public function init()
    {
        $ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('index', 'html')
            ->addActionContext('list', 'html')
            ->initContext();
    }


    public function indexAction(){
        $user = Zend_Auth::getInstance()->getIdentity()->user_object;
        if($user->has_permission('admin'))
        {
            $this->_redirect('admin/users');
        }
        else
        {
            $this->_forward('list');
        }
    }

    public function listAction()
    {
        $deleted = 0;

        $repo = Maco_Model_Repository_Factory::getRepository('user');

        $sort = $this->_request->getParam('_s', 'username');
        $dir = $this->_request->getParam('_d', 'ASC');

        if(trim($sort) == '')
        {
            $sort = 'username';
        }
        if(trim($dir) == '')
        {
            $dir = 'ASC';
        }

        $id_internal = Zend_Auth::getInstance()->getIdentity()->internal_id;

        $search = array_merge($_GET, array('active' => 1));

        $users = $repo->getUsers($sort, $dir, $search, $deleted, true);

        $this->view->deleted = $deleted;

        $g = new Maco_DaGrid_Grid();
        $g->addColumns(array(
            array(
                'label' => 'Nome Utente',
                'field' => 'username',
                'class' => 'link', // class -> link set renderer -> link
                'options' => array(
                    'linksData' => array(
                        'id' => 'user_id'
                    ),
                    'base' => '/users/detail'
                )
            ),
            array(
                'field' => 'nome'
            ),
            array(
                'field' => 'cognome'
            ),
            array(
                'label' => 'Telefono',
                'field' => 'numbers',
            ),
            array(
                'label' => 'Email',
                'field' => 'mails',
            ),
        ));

        $r = new Maco_DaGrid_Render_Html();
        $r->setView($this->view);
        $r->addClasses(array('fluid', 'listview'));
        //        $r->setId('users');
        $r->setTrIdPrefix('user_');
        $s = new Maco_DaGrid_Source_Array();
        $s->pushData($users);
        $g->setRenderer($r);
        $g->setSource($s);
        //$g->setPaginator(true);
        $g->setRowsPerPage($this->_request->getParam('perpage', 10));
        $g->setId('users');

        $this->view->dag = $g;
        $this->view->readonly = true;

        if ($this->_request->isXmlHttpRequest()) {
            $this->_helper->viewRenderer->setNoRender();
            $g->deploy();
        }
    }

    public function detailAction() {
        $id = $this->_request->getParam('id', false);

        if (!$id) {
            throw new Zend_Controller_Action_Exception('Necessario id per il dettaglio utente', 404);
        }

        $repo = Maco_Model_Repository_Factory::getRepository('user');

        $this->view->user = $repo->find($id);
    }
}