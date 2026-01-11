<?php
/**
 * Created by Marcello Stani.
 * User: marcello
 * Date: 6-set-2010
 * Time: 13.39.20
 */
 
class Model_Sdm_Repository
{
    /**
     * Users mysql mapper
     *
     * @var Model_Sdm_Mapper
     */
    protected $_sdmMapper;

    public function __construct()
    {
        $this->_sdmMapper = new Model_Sdm_Mapper();
    }

    public function getNewSdm()
    {
        $sdm = new Model_Sdm();
        return $sdm;
    }

    public function find($id)
    {
        $sdm = $this->_sdmMapper->find($id);
        return $sdm;
    }

    public function save($sdm)
    {
        return $this->_sdmMapper->save($sdm);
    }
    
    public function fetch($data = array())
    {
        $count = isset($data['count']) && $data['count'];

        $db = $this->_sdmMapper->getDbAdapter();
        $select = $db->select();
        
        $select->from('sdm', array('problem', 'date_problem', 'sdm_id', 'id_status', 'code'))
            ->joinLeft(array('u1' => 'users'), 'u1.user_id = sdm.created_by', array('creator' => 'username'))
            ->joinLeft(array('u2' => 'users'), 'u2.user_id = id_responsible', array('responsible' => 'username'))
            ->joinLeft(array('u3' => 'users'), 'u3.user_id = id_solver', array('solver' => 'username'));

        $aut = Zend_Auth::getInstance()->getIdentity();
        $user_id = $aut->user_id;
        //$select->where('id_status <> 0 || sdm.created_by = ' . $user_id);

        if(!$count)
        {
            if(isset($data['_s']) && trim($data['_s']) != '')
            {
                $select->order($data['_s'] . ' ' . (isset($data['_d']) ? $data['_d'] : 'ASC' ));
            }
            else
            {
                $select->order('date_problem DESC');
            }
        }

        if (isset($data['code']) && $data['code'] != '')
        {
            $select->where('code like ' . $db->quote('%' . $data['code'] . '%'));
        }

        if (isset($data['created_by']) && !empty($data['created_by']))
        {
            $select->where('sdm.created_by = ' . implode(' or sdm.created_by = ', $data['created_by']));
        }

        if (isset($data['id_status']) && !empty($data['id_status']))
        {
            $select->where('id_status = ' . implode(' or id_status = ', $data['id_status']));
        }

        if (isset($data['description']) && $data['description'] != '')
        {
            $select->where('problem like ' . $db->quote('%' . $data['description'] . '%'));
        }
        
        if (isset($data['date_problem']) && $data['date_problem'] != '')
        {
            $parts = explode('-', $data['date_problem']);

            if(count($parts) == 2)
            {
                $select->where('date_problem >= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))) . ' and date_problem <= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[1]))));
            }
            else
            {
                $select->where('date_problem = ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))));
            }
        }

        if (isset($data['cause']) && $data['cause'] != '')
        {
            $select->where('cause like ' . $db->quote('%' . $data['cause'] . '%'));
        }

        if (isset($data['area']) && $data['area'] != '')
        {
            $select->where('area like ' . $db->quote('%' . $data['area'] . '%'));
        }

        if (isset($data['id_responsible']) && !empty($data['id_responsible']))
        {
            $select->where('sdm.id_responsible = ' . implode(' or sdm.id_responsible = ', $data['id_responsible']));
        }

        if (isset($data['date_feedback']) && $data['date_feedback'] != '')
        {
            $parts = explode('-', $data['date_feedback']);

            if(count($parts) == 2)
            {
                $select->where('date_feedback >= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))) . ' and date_feedback <= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[1]))));
            }
            else
            {
                $select->where('date_feedback = ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))));
            }
        }

        if (isset($data['id_solver']) && !empty($data['id_solver']))
        {
            $select->where('sdm.id_solver = ' . implode(' or sdm.id_solver = ', $data['id_solver']));
        }

        if (isset($data['treatment']) && $data['treatment'] != '')
        {
            $select->where('treatment like ' . $db->quote('%' . $data['treatment'] . '%'));
        }

        if (isset($data['date_expected_resolution']) && $data['date_expected_resolution'] != '')
        {
            $parts = explode('-', $data['date_expected_resolution']);

            if(count($parts) == 2)
            {
                $select->where('date_expected_resolution >= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))) . ' and date_expected_resolution <= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[1]))));
            }
            else
            {
                $select->where('date_expected_resolution = ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))));
            }
        }

        if (isset($data['resolution']) && $data['resolution'] != '')
        {
            $select->where('treatment like ' . $db->quote('%' . $data['treatment'] . '%'));
        }

        if (isset($data['date_resolution']) && $data['date_resolution'] != '')
        {
            $parts = explode('-', $data['date_resolution']);

            if(count($parts) == 2)
            {
                $select->where('date_resolution >= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))) . ' and date_resolution <= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[1]))));
            }
            else
            {
                $select->where('date_resolution = ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))));
            }
        }

        if (isset($data['verification']) && $data['verification'] != '')
        {
            $select->where('verification like ' . $db->quote('%' . $data['verification'] . '%'));
        }

        if (isset($data['date_verification']) && $data['date_verification'] != '')
        {
            $parts = explode('-', $data['date_verification']);

            if(count($parts) == 2)
            {
                $select->where('date_verification >= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))) . ' and date_verification <= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[1]))));
            }
            else
            {
                $select->where('date_verification = ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))));
            }
        }

        if (isset($data['responsible']) && !empty($data['responsible']))
        {
            $select->where(' sdm_id in (select sr.id_sdm from sdm_responsibles sr where sr.id_user in (' . implode(', ', $data['responsible']) . '))');
        }


        if($count)
        {
            return count($db->fetchAll($select));
        }
        
        return $db->fetchAll($select);
    }
    
    public function findWithDependencies($id)
    {
        $db = $this->_sdmMapper->getDbAdapter();
        $select = $db->select();
        
        $select->from('sdm')
            ->joinLeft(array('u1' => 'users'), 'u1.user_id = sdm.created_by', array('creator' => 'u1.username'))
            ->joinLeft(array('u2' => 'users'), 'u2.user_id = id_responsible', array('responsible' => 'u2.username'))
            ->joinLeft(array('u3' => 'users'), 'u3.user_id = id_solver', array('solver' => 'u3.username'))
            ->where('sdm_id = ?', $id);
            
        $sdm = $db->fetchRow($select);

        unset($select);
        $select = $db->select();
        // TODO: id_rco to rco
        $select->from('sdm_responsibles', array('note', 'date_assigned'))
            ->joinLeft('users', 'users.user_id = id_user', 'username')
            ->order('index asc')
            ->where('id_sdm = ?', $sdm['sdm_id']);

        $resps = $db->fetchAll($select);

        /*
        if(empty($resps))
        {
            $resps = array(
                array(
                    'rco' => '',
                    'note' => '',
                    'date_assigned' => ''
                )
            );
        }
        */

        $sdm['responsibles'] = $resps;

        return $sdm;
    }

    public function add_responsibles($id_sdm, $users)
    {
        if(!$id_sdm || !is_array($users))
        {
            return;
        }

        $db = Zend_Registry::get('dbAdapter');
        foreach($users as $idx => $id_user)
        {
            $db->insert('sdm_responsibles', array(
                'id_user' => $id_user,
                'date_assigned' => date('Y-m-d'),
                'id_sdm' => $id_sdm,
                'index' => $idx + 1
            ));
        }
    }

    public function exportSdms($post)
    {
        include(LIBRARY_PATH . '/Tbs/tbs_class.php');
        include(LIBRARY_PATH . '/Tbs/plugins/tbs_plugin_opentbs.php');

        $filesMapper = new Model_FilesMapper();

        $template_name = 'sdms.xlsx';

        /*
        if(!$filesMapper->pathExists($filesMapper->getTemplatePath(false) . $template_name))
        {
            $this->_helper->getHelper('FlashMessenger')->addMessage('Il file di template per l\'esportazione dell\'offerta non esiste.');
            $this->_redirect('orders/detail/id/' . $offer_id);
        }
        */

        $tbs = new clsTinyButStrong; // new instance of TBS
        $tbs->Plugin(TBS_INSTALL, OPENTBS_PLUGIN); // load OpenTBS plugin

        $tbs->LoadTemplate($filesMapper->getTemplatePath() . $template_name);

        $sdms = $this->fetch($_GET);

        $sdms_data = array();
        foreach($sdms as $sdm)
        {
            $sdms_data[] = array(
                'id' => utf8_decode($sdm['sdm_id']),
                'codice' => utf8_decode($sdm['code']),
                'dataemissione' => Maco_Utils_DbDate::fromDb($sdm['date_problem']),
                'descrizione' => utf8_decode($sdm['problem']),
                'emittente' => utf8_decode($sdm['creator']),
                'responsabile' => utf8_decode($sdm['responsible']),
                'stato' => utf8_decode(Model_Sdm::getStatoDescription($sdm['id_status'])),
            );
        }
        $tbs->MergeBlock('o', $sdms_data);

        $file_name = 'elenco_sdm.xlsx';
        $tbs->Show(OPENTBS_DOWNLOAD, $file_name);
        exit;
    }


    public function getNextYearAndProgr()
    {
        $db = $this->_sdmMapper->getDbAdapter();
        
        $year = date('Y');
        $progr = '1';
        $last_code = $db->fetchOne('select code from sdm where code <> \'\' and code is not null order by sdm_id DESC limit 0, 1');
        $parts = explode('-', $last_code);
        if($parts[0] == $year)
        {
            // stesso anno
            $progr = ++$parts[1];
        }
        else
        {
            // anno nuovo
        }
        return array($year, $progr);
    }

}
