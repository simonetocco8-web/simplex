<?php
  
class Model_Permissions
{
    protected $_resources = array(
        'companies' => array(
            'label' => 'Aziende',
            'actions' => array(
                'view' => array(
                    'label' => 'Pu&ograve; visualizzare le aziende',
                    'childs' => array(
                        'create',
                        'modify',
                        'modify_own',
                        'exclude',
                    ), 
                    'parents' => array()
                ),
                'view_own' => array(
                    'label' => 'Pu&ograve; visualizzare le aziende da lui create',
                    'childs' => array('modify_own', 'view'),
                    'parents' => array()
                ),
                'view_excluded' => array(
                    'label' => 'Pu&ograve; visualizzare le aziende escluse',
                    'childs' => array(),
                    'parents' => array()
                ),
                'create' => array(
                    'label' => 'Pu&ograve; creare le aziende',
                    'childs' => array(),
                    'parents' => array('view_own')
                ),
                'modify' => array(
                    'label' => 'Pu&ograve; modificare le aziende',
                    'childs' => array('link_office'),
                    'parents' => array('view')
                ),
                'modify_own' => array(
                    'label' => 'Pu&ograve; modificare le aziende da lui create',
                    'childs' => array(),
                    'parents' => array('view_own')
                ),
                'link_office' => array(
                    'label' => 'Pu&ograve; associare sede',
                    'childs' => array(),
                    'parents' => array('modify')
                ),
                'exclude' => array(
                    'label' => 'Pu&ograve; escludere le aziende',
                    'childs' => array(),
                    'parents' => array('view')
                ),  
            ),
        ),
        'contacts' => array(
            'label' => 'Contatti',
            'actions' => array(
                'view' => array(
                    'label' => 'Pu&ograve; visualizzare i contatti',
                    'childs' => array('create', 'modify', 'modify_own'),
                    'parents' => array()
                ),
                'create' => array(
                    'label' => 'Pu&ograve; creare contatti',
                    'childs' => array(),
                    'parents' => array('view')
                ),
                'modify' => array(
                    'label' => 'Pu&ograve; modificare contatti',
                    'childs' => array(),
                    'parents' => array('view')
                ),
                'modify_own' => array(
                    'label' => 'Pu&ograve; modificare contatti da lui creati',
                    'childs' => array(),
                    'parents' => array('view')
                ),
            )
        ),
        'offers' => array(
            'label' => 'Offerte',
            'actions' => array(
                'view' => array(
                    'label' => 'Pu&ograve; visualizzare le offerte',
                    'childs' => array('create', 'modify', 'approve'),
                    'parents' => array('view_own')
                ),
                'view_own' => array(
                    'label' => 'Pu&ograve; visualizzare le offerte da lui create',
                    'childs' => array('modify_own', 'view'),
                    'parents' => array()
                ),
                'create' => array(
                    'label' => 'Pu&ograve; creare offerte',
                    'childs' => array(),
                    'parents' => array('view_own')
                ),
                'modify' => array(
                    'label' => 'Pu&ograve; modificare offerte',
                    'childs' => array(),
                    'parents' => array('view', 'view_own', 'modify_own')
                ),
                'modify_own' => array(
                    'label' => 'Pu&ograve; modificare offerte da lui create',
                    'childs' => array(),
                    'parents' => array('view_own')
                ),
                'approve' => array(
                    'label' => 'Pu&ograve; approvare una offerta',
                    'childs' => array(),
                    'parents' => array('view')
                ),
                'approve_any' => array(
                    'label' => 'Pu&ograve; approvare qualsiasi offerta',
                    'childs' => array(),
                    'parents' => array('view')
                ),
                'modify_always' => array(
                    'label' => 'Modifica offerte chiuse',
                    'childs' => array(),
                    'parents' => array('modify')
                ),
            )
        ),
        'orders' => array(
            'label' => 'Commesse',
            'actions' => array(
                'view' => array(
                    'label' => 'Pu&ograve; visualizzare le commesse',

                    'childs' => array('create', 'incarico', 'modify_plan', 'modify_cons', 'view_budget'),
                    'parents' => array()
                ),
                'view_own' => array(
                    'label' => 'Pu&ograve; visualizzare le commesse a lui assegnate (RC)',
                    'childs' => array('incarico'),
                    'parents' => array()
                ),
                'create' => array(
                    'label' => 'Pu&ograve; creare commesse',
                    'childs' => array(),
                    'parents' => array('view')
                ),
                'assign' => array(
                    'label' => 'Pu&ograve; assegnare le commesse',
                    'childs' => array(),
                    'parents' => array('view')
                ),
                'incarico' => array(
                    'label' => 'Pu&ograve; prendere in carico le commesse',
                    'childs' => array(),
                    'parents' => array('view')
                ),
                'view_budget' => array(
                    'label' => 'Pu&ograve; visualizzare il budget delle commesse',
                    'childs' => array(),
                    'parents' => array('view')
                ),
                'cancel' => array(
                    'label' => 'Pu&ograve; annullare, sospendere e riattivare le commesse',
                    'childs' => array(),
                    'parents' => array('view')
                ),
                'modify_always' => array(
                    'label' => 'Modifica commesse chiuse',
                    'childs' => array(),
                    'parents' => array()
                ),
            )
        ),
        'tasks' => array(
            'label' => 'Impegni',
            'actions' => array(
                'view_own' => array(
                    'label' => 'Pu&ograve; visualizzare i suoi impegni',
                    'childs' => array('create_own', 'modify_own', 'view'),
                    'parents' => array()
                ),
                'view' => array(
                    'label' => 'Pu&ograve; visualizzare gli impegni di tutti',
                    'childs' => array('create', 'modify'),
                    'parents' => array('view_own')
                ),
                'create_own' => array(
                    'label' => 'Pu&ograve; creare nuovi impegni per se stesso',
                    'childs' => array('create'),
                    'parents' => array('view_own')
                ),
                'create' => array(
                    'label' => 'Pu&ograve; creare nuovi impegni',
                    'childs' => array(),
                    'parents' => array('view', 'create_own')
                ),
                'modify_own' => array(
                    'label' => 'Pu&ograve; aggiornare i suoi impegni',
                    'childs' => array('modify'),
                    'parents' => array('view_own')
                ),
                'modify' => array(
                    'label' => 'Pu&ograve; aggiornare gli impegni',
                    'childs' => array(),
                    'parents' => array('view', 'modify_own')
                ),
            )
        ),
        'sdm' => array(
            'label' => 'Sdm',
            'actions' => array(
                'view' => array(
                    'label' => 'Pu&ograve; visualizzare le SDM',
                    'childs' => array('create', 'modify', 'responsabile', 'trattamento', 'verifica'),
                    'parents' => array()
                ),
                'view_own' => array(
                    'label' => 'Pu&ograve; visualizzare solo le sue SDM',
                    'childs' => array(),
                    'parents' => array()
                ),
                'create' => array(
                    'label' => 'Pu&ograve; creare SDM',
                    'childs' => array(),
                    'parents' => array('view')
                ),
                'responsabile' => array(
                    'label' => 'Pu&ograve; essere responsabile (RSQ)',
                    'childs' => array(),
                    'parents' => array('view')
                ),
                'risolutore' => array(
                    'label' => 'Pu&ograve; essere risolutore SDM',
                    'childs' => array(),
                    'parents' => array('view')
                ),
                'preventer' => array(
                    'label' => 'Pu&ograve; essere responsabile azione preventiva SDM',
                    'childs' => array(),
                    'parents' => array('view')
                ),
                'view_responsibles' => array(
                    'label' => 'Pu&ograve; visualizza i responsabili della SDM',
                    'childs' => array(),
                    'parents' => array('view')
                ),
            )
        ),
        'administration' => array(
            'label' => 'Amministrazione',
            'actions' => array(
                'view' => array(
                    'label' => 'Pu&ograve; visualizzare i dati di amministrazione',
                    'childs' => array('edit'),
                    'parents' => array()
                ),
                'edit' => array(
                    'label' => 'Pu&ograve; modificare i dati di amministrazione',
                    'childs' => array(),
                    'parents' => array('view')
                ),
                'receive_messages' => array(
                    'label' => 'Riceve messaggi alla chiusura delle commesse',
                    'childs' => array(),
                    'parents' => array('view')
                ),
            )
        ),
        'admin' => array(
            'label' => 'Pannello di Admin',
            'actions' => array(
                'view' => array(
                    'label' => 'Pu&ograve; visualizzare i dati del pannello di admin',
                    'childs' => array('edit'),
                    'parents' => array()
                ),
                'edit' => array(
                    'label' => 'Pu&ograve; modificare i dati del pannello di admin',
                    'childs' => array(),
                    'parents' => array('view')
                ),
            )
        ),
    );
    
    public function getPermissions()
    {
        return $this->_resources;
    }
}