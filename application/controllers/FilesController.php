<?php


class FilesController extends Zend_Controller_Action
{
    public function init()
    {
        /* Initialize action controller here */
        $ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('list', 'html')
                ->addActionContext('mkdir', 'json')
                ->addActionContext('rm', 'json')
                ->initContext();
    }

    public function indexAction()
    {
        $this->_forward('list');
    }

    public function listAction()
    {
        $id = $this->_request->getParam('id');
        $w = $this->_request->getParam('w');
        $path = $this->_request->getParam('p');

        $repo = new Model_FilesMapper();

        $sort = $this->_request->getParam('_s', false);
        $dir = $this->_request->getParam('_d', 'ASC');

        $options = array(
            'order' => array(
                'sort' => $sort,
                'dir' => $dir
            )
        );

        if($this->_request->isXmlHttpRequest())
        {
            $files = $repo->getFiles($path, $w, $id, $options);
        }
        else
        {
            $files = array();
        }


        $g = new Maco_DaGrid_Grid();
        $g->addColumns(array(
            array(
               'label' => '',
               'class' => 'checkbox',
               'field' => 'name',
               'options' => array(
                   'cb_name' => 'file[]',
               ),
               'search' => false
           ),
            array(
                'label' => 'File',
                'field' => 'name',
                'class' => 'linkFiles', // class -> link set renderer -> link
                'options' => array(
                    'linksData' => array(
                        'p' => 'path'
                    ),
                    'base' => '/files/'
                )
            ),
            array(
                'field' => 'last_modified',
                'label' => 'Ultima Modifica',
                'renderer' => 'datetime',
            ),
            array(
                'field' => 'size',
                'label' => 'Dimensione',
            ),
        ));
        $g->addColumns(array(
            array(
                'label' => '',
                'field' => '',
                'class' => 'links',
                'search' => false,
                'sortable' => false,
                'options' => array(
                    'links' => array(
                        /*   array(
                           'linkData' => array(
                               'p' => 'path',
                           ),
                           'base' => '/files/mv',
                           'img' => '/img/edit.png',
                           'title' => 'rinomina',
                       ), */
                        array(
                            'linkData' => array(
                                'p' => 'path',
                            ),
                            'base' => '/files/rm',
                            'img' => '/img/delete.png',
                            'title' => 'Elimina',
                        ),
                    )
                )
            )
        ));

        $r = new Maco_DaGrid_Render_Html();
        $r->setView($this->view);
        $r->addClasses(array('fluid', 'listview'));
        //        $r->setId('users');
        $r->setTrIdPrefix('files_');

        $r->files_path = $repo->getDecodedPublicPath($path);

        $s = new Maco_DaGrid_Source_Array();
        $s->pushData($files);
        $g->setRenderer($r);
        $g->setSource($s);
        //$g->setPaginator(true);
        $g->setRowsPerPage($this->_request->getParam('perpage', 20));
        $g->setId('tasks');

        $this->view->dag = $g;

        if($this->_request->isXmlHttpRequest())
        {
            $this->_helper->viewRenderer->setNoRender();
            $g->deploy('files.phtml');
        }
    }

    public function archiveAction()
    {
        // encoded path
        $path = $this->_request->getParam('p');

        $repo = new Model_FilesMapper();

        $realPath = $repo->decodePath($path);
        $filesInfo = base64_decode($this->_request->getParam('f'));
        $files = explode('/f/', $filesInfo);
        $files = array_filter($files, function($el){
            return $el;
        });

        if(!$repo->pathExists($realPath))
        {
            if(!$repo->createFolderIfCompanyOrUser($realPath))
            {
                throw new Zend_Controller_Action_Exception('file o cartella non esistente', 404);
            }
        }


        $repo->downloadArchive($realPath, $files);
        exit;
    }

    public function getAction()
    {
        // encoded path
        $path = $this->_request->getParam('p');
        $this->view->path = $path;
        $repo = new Model_FilesMapper();

        $realPath = $repo->decodePath($path);

        if(!$repo->pathExists($realPath))
        {
            if(!$repo->createFolderIfCompanyOrUser($realPath))
            {
                throw new Zend_Controller_Action_Exception('file o cartella non esistente', 404);
            }
        }

        if($repo->isDir($realPath))
        {
            $this->_forward('list', 'files', 'default', array('p' => $path));
        }
        else
        {
            echo json_encode(array(
                'redirect' => $path
            ));

            exit;
        }
    }

    public function downloadAction()
    {
        $path = $this->_request->getParam('p');
        $repo = new Model_FilesMapper();
        $realPath = $repo->decodePath($path);
        if(!$repo->pathExists($realPath))
        {
            throw new Zend_Controller_Action_Exception('file o cartella non esistente', 404);
        }
        $repo->download($realPath);
        exit;
    }

    public function rmAction()
    {
        $repo = new Model_FilesMapper();
        $path = $this->_request->getParam('p');
        $realPath = $repo->decodePath($path);

        $this->view->result = $repo->rm($realPath);
    }

    public function mvAction()
    {

    }

    public function mkdirAction()
    {
        $repo = new Model_FilesMapper();
        $this->view->result = $repo->createFolder($this->_request->getParam('p'), $this->_request->getParam('name'));
    }

    public function rmdirAction()
    {
    }

    public function uploadAction()
    {
        $repo = new Model_FilesMapper();

        $path = $this->_request->getParam('p', '');
        $pathIsFolder = $this->_request->getParam('pif', false);
        $rename = $this->_request->getParam('r', false);
        $overwrite = $this->_request->getParam('o', false);
        $archive = $this->_request->getParam('a', false);
        echo $repo->upload($path, $rename, $pathIsFolder, $overwrite, $archive);
        exit;
    }

    public function uploadofferAction()
    {
        $repo = new Model_FilesMapper();

        $offer_id = $this->_request->getParam('id');
        $offer_repo = Maco_Model_Repository_Factory::getRepository('offer');

        $offer = $offer_repo->findWithDependenciesById($offer_id);

        $path = $repo->getOfferPdfFolder($offer);

        $repo->buildPath($path);

        $path = $repo->encodePath($path);

        // $path = $this->_request->getParam('p', '');
        $pathIsFolder = true;
        $rename = $repo->getOfferPdfFileName($offer);
        $overwrite = true;
        $archive = false;
        echo $repo->upload($path, $rename, $pathIsFolder, $overwrite, $archive);
        exit;
    }

    public function uploadorderAction()
    {
        $repo = new Model_FilesMapper();

        $order_id = $this->_request->getParam('id');
        $order_repo = Maco_Model_Repository_Factory::getRepository('order');

        $order = $order_repo->findWithDependenciesById($order_id);

        $path = $repo->getOrderPdfFolder($order);

        $repo->buildPath($path);

        $path = $repo->encodePath($path);

        // $path = $this->_request->getParam('p', '');
        $pathIsFolder = true;
        $rename = $repo->getOrderPdfFileName($order);
        $overwrite = true;
        $archive = false;
        echo $repo->upload($path, $rename, $pathIsFolder, $overwrite, $archive);
        exit;
    }
}
