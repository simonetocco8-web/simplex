<?php
/**
 * Created by JetBrains PhpStorm.
 * User: marcello
 * Date: 26/04/13
 * Time: 16.37
 * To change this template use File | Settings | File Templates.
 */

class Model_Sdm2_Repository
{
    /**
     * Users mysql mapper
     *
     * @var Model_Sdm_Mapper
     */
    protected $_sdmMapper;

    public function __construct()
    {
        $this->_sdmMapper = new Model_Sdm2_Mapper();
    }

    public function getNewSdm()
    {
        $sdm = new Model_Sdm2();
        return $sdm;
    }

    public function find($id)
    {
        $sdm = $this->_sdmMapper->find($id);
        return $sdm;
    }

    public function findWithDependencies($id)
    {
        $db = $this->_sdmMapper->getDbAdapter();

        $select = $db->select();

        $select->from('sdm2', array(
            'sdm_id',
            'created_by',
            'date_created',
            'modified_by',
            'date_modified',
            'year',
            'code',
            'id_status',
            'with_prevention',
        ))
            ->joinLeft(array('u1' => 'users'), 'u1.user_id = sdm2.created_by', array('creator' => 'u1.username'))
            ->joinLeft(array('u2' => 'users'), 'u2.user_id = sdm2.modified_by', array('modifier' => 'u2.username'));

        $select->where('sdm_id = ?', $id);

        $data = $db->fetchRow($select);

        $sdm = $this->getNewSdm();

        if($data['sdm_id'] != '')
        {
            $sdm->setData($data);

            $sdm_story_repo = Maco_Model_Repository_Factory::getRepository('sdm2Story');

            $stories = $sdm_story_repo->findAllWithDependenciesBySdmId($id);
$sdm->newStories = $stories;
            $toAdd = array();
            foreach($stories as $story)
            {
                if(!isset($toAdd[$story->id_status]))
                {
                    $toAdd[$story->id_status] = array(
                        'active' => null,
                        'inactives' => array()
                    );
                }
                if($story->active == 1)
                {
                    $toAdd[$story->id_status]['active'] = $story;
                }
                else
                {
                    $toAdd[$story->id_status]['inactives'][] = $story;
                }
            }

            $sdm->stories = $toAdd;
        }

        return $sdm;
    }

    public function save($sdm)
    {
        return $this->_sdmMapper->save($sdm);
    }

    public function fetch($data = array())
    {
        $auth = Zend_Auth::getInstance()->getIdentity();

        $count = isset($data['count']) && $data['count'];

        $db = $this->_sdmMapper->getDbAdapter();
        $select = $db->select();

        $select->from('sdm2', array('sdm_id', 'id_status', 'code', 'with_prevention'))
            ->joinLeft(array('u1' => 'users'), 'u1.user_id = sdm2.created_by', array('creator' => 'username'))

            // rsq
            ->joinLeft(array('s2' => 'sdm_story'), 's2.id_sdm = sdm2.sdm_id and s2.id_status = ' . Model_Sdm2::STATUS_NEW . ' and s2.active = 1', array('problem' => 'text1', 'date_problem' => 'date1'))
            ->joinLeft(array('u2' => 'users'), 'u2.user_id = s2.id_user', array('responsible' => 'username'))

            // risolutore
            ->joinLeft(array('s3' => 'sdm_story'), 's3.id_sdm = sdm2.sdm_id and s3.id_status = ' . Model_Sdm2::STATUS_WORKING . ' and s3.active = 1', array('cause' => 'text1', 'note' => 'text2', 'date_expected_resolution' => 'date1', 'date_set_solver' => 'date2'))
            ->joinLeft(array('u3' => 'users'), 'u3.user_id = s3.id_user', array('solver' => 'username'))

            // trattamento
            ->joinLeft(array('s4' => 'sdm_story'), 's4.id_sdm = sdm2.sdm_id and s4.id_status = 4 and s4.active = 1', array('resolution' => 'text1', 'date_resolution' => 'date2'))

            ->joinLeft(array('s5' => 'sdm_story'), 's5.id_sdm = sdm2.sdm_id and s5.id_status = 5 and s5.active = 1', array('verification' => 'text1', 'date_verification' => 'date1'))
/*
            // verification
            ->joinLeft(array('s5' => 'sdm_story'), 's5.id_sdm = sdm2.sdm_id and s5.id_status = 5 and s5.active = 1', array('verification' => 'text1', 'date_verification' => 'date1'))
            ->joinLeft(array('u5' => 'users'), 'u5.user_id = s5.id_user', array('solver' => 'username'))
*/
        ;
            // TODO: DATE_FEEDBACK

        $aut = Zend_Auth::getInstance()->getIdentity();
        $user_id = $aut->user_id;
        //$select->where('id_status <> 0 || sdm.created_by = ' . $user_id);

        $select->where('internal_code = ?', strtolower($aut->internal_abbr));

        if(!$auth->user_object->has_permission('sdm', 'view') && $auth->user_object->has_permission('sdm', 'view_own'))
        {
            $select->where('sdm2.created_by = ' . $auth->user_id);
        }

        if(!$count)
        {
            if(isset($data['_s']) && trim($data['_s']) != '')
            {
                $select->order($data['_s'] . ' ' . (isset($data['_d']) ? $data['_d'] : 'ASC' ));
            }
            else
            {
                $select->order('sdm2.date_created DESC');
            }
        }

        if (isset($data['code']) && $data['code'] != '')
        {
            $select->where('code like ' . $db->quote('%' . $data['code'] . '%'));
        }

        if (isset($data['created_by']) && !empty($data['created_by']))
        {
            $select->where('sdm2.created_by = ' . implode(' or sdm2.created_by = ', $data['created_by']));
        }

        if (isset($data['id_status']) && !empty($data['id_status']))
        {
            $select->where('sdm2.id_status = ' . implode(' or sdm2.id_status = ', $data['id_status']));
        }

        if (isset($data['description']) && $data['description'] != '')
        {
            $select->where('s2.text1 like ' . $db->quote('%' . $data['description'] . '%'));
        }

        if (isset($data['date_problem']) && $data['date_problem'] != '')
        {
            $parts = explode('-', $data['date_problem']);

            if(count($parts) == 2)
            {
                $select->where('s2.date1 >= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))) . ' and s2.date1 <= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[1]))));
            }
            else
            {
                $select->where('s2.date1 = ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))));
            }
        }

        if (isset($data['cause']) && $data['cause'] != '')
        {
            $select->where('s3.text1 like ' . $db->quote('%' . $data['cause'] . '%'));
        }

        if (isset($data['area']) && $data['area'] != '')
        {
            $select->where('s3.text2 like ' . $db->quote('%' . $data['area'] . '%'));
        }

        if (isset($data['id_responsible']) && !empty($data['id_responsible']))
        {
            $select->where('s2.id_user = ' . implode(' or s2.id_user = ', $data['id_responsible']));
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
            $select->where('s3.id_user = ' . implode(' or s3.id_user = ', $data['id_solver']));
        }

        if (isset($data['treatment']) && $data['treatment'] != '')
        {
            $select->where('s4.text1 like ' . $db->quote('%' . $data['treatment'] . '%'));
        }

        if (isset($data['date_expected_resolution']) && $data['date_expected_resolution'] != '')
        {
            $parts = explode('-', $data['date_expected_resolution']);

            if(count($parts) == 2)
            {
                $select->where('s3.date2 >= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))) . ' and s3.date2 <= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[1]))));
            }
            else
            {
                $select->where('s3.date2 = ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))));
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
                $select->where('s4.date2 >= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))) . ' and s4.date2 <= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[1]))));
            }
            else
            {
                $select->where('s4.date2 = ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))));
            }
        }

        if (isset($data['verification']) && $data['verification'] != '')
        {
            $select->where('s5.text1 like ' . $db->quote('%' . $data['verification'] . '%'));
        }

        if (isset($data['date_verification']) && $data['date_verification'] != '')
        {
            $parts = explode('-', $data['date_verification']);

            if(count($parts) == 2)
            {
                $select->where('s5.date1 >= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))) . ' and s5.date1 <= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[1]))));
            }
            else
            {
                $select->where('s5.date1 = ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))));
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

    public function exportSdms($data)
    {
        include(LIBRARY_PATH . '/Tbs/tbs_class.php');
        include(LIBRARY_PATH . '/Tbs/plugins/tbs_plugin_opentbs.php');

        $filesMapper = new Model_FilesMapper();

        $template_name = 'sdms-new.xlsx';

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

        $auth = Zend_Auth::getInstance()->getIdentity();

        $db = $this->_sdmMapper->getDbAdapter();
        $select = $db->select();

        $select->from('sdm2', array('sdm_id', 'id_status', 'code', 'with_prevention'))
            ->joinLeft(array('u1' => 'users'), 'u1.user_id = sdm2.created_by', array('creator' => 'username'))

            // rsq
            ->joinLeft(array('s2' => 'sdm_story'), 's2.id_sdm = sdm2.sdm_id and s2.id_status = ' . Model_Sdm2::STATUS_NEW . ' and s2.active = 1', array('problem' => 'text1', 'date_problem' => 'date1'))
            ->joinLeft(array('u2' => 'users'), 'u2.user_id = s2.id_user', array('responsible' => 'username'))

            // risolutore
            ->joinLeft(array('s3' => 'sdm_story'), 's3.id_sdm = sdm2.sdm_id and s3.id_status = ' . Model_Sdm2::STATUS_WORKING . ' and s3.active = 1', array('cause' => 'text1', 'note' => 'text2', 'date_expected_resolution' => 'date1', 'date_set_solver' => 'date2'))
            ->joinLeft(array('u3' => 'users'), 'u3.user_id = s3.id_user', array('solver' => 'username'))

            // trattamento
            ->joinLeft(array('s4' => 'sdm_story'), 's4.id_sdm = sdm2.sdm_id and s4.id_status = 4 and s4.active = 1', array('resolution' => 'text1', 'date_resolution' => 'date2'))

            ->joinLeft(array('s5' => 'sdm_story'), 's5.id_sdm = sdm2.sdm_id and s5.id_status = 5 and s5.active = 1', array('verification' => 'text1', 'date_verification' => 'date1'))
            /*
                        // verification
                        ->joinLeft(array('s5' => 'sdm_story'), 's5.id_sdm = sdm2.sdm_id and s5.id_status = 5 and s5.active = 1', array('verification' => 'text1', 'date_verification' => 'date1'))
                        ->joinLeft(array('u5' => 'users'), 'u5.user_id = s5.id_user', array('solver' => 'username'))
            */
        ;
        // TODO: DATE_FEEDBACK

        $aut = Zend_Auth::getInstance()->getIdentity();
        $user_id = $aut->user_id;
        //$select->where('id_status <> 0 || sdm.created_by = ' . $user_id);

        $select->where('internal_code = ?', strtolower($aut->internal_abbr));

        if(!$auth->user_object->has_permission('sdm', 'view') && $auth->user_object->has_permission('sdm', 'view_own'))
        {
            $select->where('sdm2.created_by = ' . $auth->user_id);
        }

        if(isset($data['_s']) && trim($data['_s']) != '')
        {
            $select->order($data['_s'] . ' ' . (isset($data['_d']) ? $data['_d'] : 'ASC' ));
        }
        else
        {
            $select->order('sdm2.date_created DESC');
        }

        if (isset($data['code']) && $data['code'] != '')
        {
            $select->where('code like ' . $db->quote('%' . $data['code'] . '%'));
        }

        if (isset($data['created_by']) && !empty($data['created_by']))
        {
            $select->where('sdm2.created_by = ' . implode(' or sdm2.created_by = ', $data['created_by']));
        }

        if (isset($data['id_status']) && !empty($data['id_status']))
        {
            $select->where('sdm2.id_status = ' . implode(' or sdm2.id_status = ', $data['id_status']));
        }

        if (isset($data['description']) && $data['description'] != '')
        {
            $select->where('s2.text1 like ' . $db->quote('%' . $data['description'] . '%'));
        }

        if (isset($data['date_problem']) && $data['date_problem'] != '')
        {
            $parts = explode('-', $data['date_problem']);

            if(count($parts) == 2)
            {
                $select->where('s2.date1 >= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))) . ' and s2.date1 <= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[1]))));
            }
            else
            {
                $select->where('s2.date1 = ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))));
            }
        }

        if (isset($data['cause']) && $data['cause'] != '')
        {
            $select->where('s3.text1 like ' . $db->quote('%' . $data['cause'] . '%'));
        }

        if (isset($data['area']) && $data['area'] != '')
        {
            $select->where('s3.text2 like ' . $db->quote('%' . $data['area'] . '%'));
        }

        if (isset($data['id_responsible']) && !empty($data['id_responsible']))
        {
            $select->where('s2.id_user = ' . implode(' or s2.id_user = ', $data['id_responsible']));
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
            $select->where('s3.id_user = ' . implode(' or s3.id_user = ', $data['id_solver']));
        }

        if (isset($data['treatment']) && $data['treatment'] != '')
        {
            $select->where('s4.text1 like ' . $db->quote('%' . $data['treatment'] . '%'));
        }

        if (isset($data['date_expected_resolution']) && $data['date_expected_resolution'] != '')
        {
            $parts = explode('-', $data['date_expected_resolution']);

            if(count($parts) == 2)
            {
                $select->where('s3.date2 >= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))) . ' and s3.date2 <= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[1]))));
            }
            else
            {
                $select->where('s3.date2 = ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))));
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
                $select->where('s4.date2 >= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))) . ' and s4.date2 <= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[1]))));
            }
            else
            {
                $select->where('s4.date2 = ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))));
            }
        }

        if (isset($data['verification']) && $data['verification'] != '')
        {
            $select->where('s5.text1 like ' . $db->quote('%' . $data['verification'] . '%'));
        }

        if (isset($data['date_verification']) && $data['date_verification'] != '')
        {
            $parts = explode('-', $data['date_verification']);

            if(count($parts) == 2)
            {
                $select->where('s5.date1 >= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))) . ' and s5.date1 <= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[1]))));
            }
            else
            {
                $select->where('s5.date1 = ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))));
            }
        }

        if (isset($data['responsible']) && !empty($data['responsible']))
        {
            $select->where(' sdm_id in (select sr.id_sdm from sdm_responsibles sr where sr.id_user in (' . implode(', ', $data['responsible']) . '))');
        }

        $sdms = $db->fetchAll($select);

        $sdms_data = array();
        foreach($sdms as $sdm)
        {
            $sdms_data[] = array(
                'id' => utf8_decode($sdm['sdm_id']),
                'codice' => utf8_decode($sdm['code']),
                'descrizione' => $sdm['problem'],
                'dataemissione' => Maco_Utils_DbDate::fromDb($sdm['date_problem']),
                'emittente' => utf8_decode($sdm['creator']),
                'responsabile' => utf8_decode($sdm['responsible']),
                'risolutore' => utf8_decode($sdm['solver']),
                'stato' => utf8_decode(Model_Sdm2::getStatoDescription($sdm['id_status'], '')),
                'azione_preventiva' => utf8_decode(Model_Sdm2::getStatoDescription($sdm['with_prevention'], '')),
            );
        }
        $tbs->MergeBlock('s', $sdms_data);

        $file_name = 'elenco-sdm.xlsx';
        $tbs->Show(OPENTBS_DOWNLOAD, $file_name);
        exit;
    }


    public function getNextYearAndProgr($internal_code)
    {
        $db = $this->_sdmMapper->getDbAdapter();

        $year = date('Y');
        $progr = '1';
        $last_code = $db->fetchOne('select code from sdm2 where internal_code = \'' . $internal_code . '\' and code <> \'\' and code is not null order by sdm_id DESC limit 0, 1');

        $parts = explode('-', $last_code);
        if($parts[1] == $year)
        {
            // stesso anno
            $progr = ++$parts[2];
        }
        else
        {
            // anno nuovo
        }
        return array($year, $progr);
    }
}