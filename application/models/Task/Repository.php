<?php
/**
 * Created by Marcello Stani.
 * User: marcello
 * Date: 6-set-2010
 * Time: 13.39.20
 */

class Model_Task_Repository
{
    /**
     * Users mysql mapper
     *
     * @var Model_User_Mapper
     */
    protected $_taskMapper;

    public function __construct()
    {
        $this->_taskMapper = new Model_Task_Mapper();
    }

    public function getNewTask()
    {
        $task = new Model_Task();
        $task->when = date('d-m-Y H:i');
        $task->id_who = Zend_Auth::getInstance()->getIdentity()->user_id;
        return $task;
    }

    public function find($id)
    {
        $task = $this->_taskMapper->find($id);
        return $task;
    }

    public function save($task)
    {
        return $this->_taskMapper->save($task);
    }

    public function getTasks($sort = false, $dir = false, $search = array(), $per_page = NULL, $count = false)
    {
        $db = $this->_taskMapper->getDbAdapter();

        $select = $db->select();

        $select->from('tasks')
                ->joinLeft(array('u2' => 'users'), 'u2.user_id = tasks.id_who', array('who' => 'u2.username', 'id_who' => 'u2.user_id'))
                ->joinLeft(array('cm' => 'companies'), 'cm.company_id = tasks.id_company', array('company' => 'ragione_sociale', 'id_company' => 'cm.company_id'));
        //->joinLeft(array('cn' => 'contacts'), 'cn.contact_id = tasks.id_subject', array('receiver' => 'concat_ws(\' \', nome, cognome)', 'id_subject_name' => 'cn.contact_id'));

        $auth = Zend_Auth::getInstance()->getIdentity();
        $select->where('u2.user_id in (select id_user from users_internals where id_internal = ' . $auth->internal_id . ')');

        $select->where('tasks.id_company in (select distinct ci2.id_company from companies_internals ci2 where ci2.id_internal = ' . $auth->internal_id . ')');

        // non facciamo vedere nell'elenco i task generatori (quelli con id parent nullo)
        $select->where('id_parent is not null and id_parent <> 0');

        if(!$auth->user_object->has_permission('tasks', 'view') && $auth->user_object->has_permission('tasks', 'view_own'))
        {
            $select->where('tasks.id_who = ' . $auth->user_id);
        }

        if(!$count)
        {
            if($sort)
            {
                $select->order($sort . ' ' . $dir);
            }
            else
            {
                $select->order('tasks.when asc');
            }
        }

        if(is_array($search) && !empty($search))
        {
            if (isset($search['start']) && !empty($search['start']))
            {
                $start = date('Y-m-d 00:00:00', $search['start']);
                $select->where('tasks.when >= ' . $db->quote($start));
            }

            if (isset($search['end']) && !empty($search['end']))
            {
                $end = date('Y-m-d 23:59:59', $search['end']);
                $select->where('tasks.when <= ' . $db->quote($end));
            }

            if (isset($search['id_who']) && !empty($search['id_who']))
            {
                if(is_array($search['id_who']))
                {
                    $select->where('id_who = ' . implode(' or id_who = ', $search['id_who']));
                }
                else
                {
                    if($search['id_who'] == 'own')
                    {
                        $select->where('id_who = ?', $auth->user_id);
                    }
                    else
                    {
                        $select->where('id_who = ?', $search['id_who']);
                    }
                }
            }
            if (isset($search['created_by']) && !empty($search['created_by']))
            {
                if(is_array($search['created_by']))
                {
                    $select->where('tasks.created_by = ' . implode(' or tasks.created_by = ', $search['created_by']));
                }
                else
                {
                    $select->where('tasks.created_by = ?', $search['created_by']);
                }
            }
            if (isset($search['what']) && !empty($search['what']))
            {
                $select->where('what = ' . implode(' or what = ', $search['what']));
            }
            if (isset($search['when']) && $search['when'] != '')
            {
                $parts = explode('-', $search['when']);

                if(count($parts) == 2)
                {
                    $select->where('tasks.when >= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))) . ' and tasks.when <= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[1] . ' 23:59'))));
                }
                else
                {
                    $select->where('tasks.when = ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))));
                }
            }
            if (isset($search['date_created']) && $search['date_created'] != '')
            {
                $parts = explode('-', $search['date_created']);

                if(count($parts) == 2)
                {
                    $select->where('tasks.date_created >= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))) . ' and tasks.date_created <= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[1] . ' 23:59'))));
                }
                else
                {
                    $select->where('tasks.date_created = ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))));
                }
            }
            if (isset($search['date_done']) && $search['date_done'] != '')
            {
                $parts = explode('-', $search['date_done']);

                if(count($parts) == 2)
                {
                    $select->where('tasks.date_done >= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))) . ' and tasks.date_done <= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[1] . ' 23:59'))));
                }
                else
                {
                    $select->where('tasks.date_done = ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))));
                }
            }
            if (isset($search['where']) && $search['where'] != '')
            {
                $select->where('subject_data like ' . $db->quote('%' . $search['where'] . '%'));
            }
            if (isset($search['id_company']) && !empty($search['id_company']))
            {
                $idcparts = explode(',', $search['id_company']);
                $wh = '';
                foreach($idcparts as $id_company)
                {
                    if($id_company != '')
                    {
                        $wh .= 'tasks.id_company = ' . $db->quote($id_company) . ' OR ';
                    }
                }
                if($wh != '')
                {
                    $wh = substr($wh, 0, -4);
                    $select->where($wh);
                }
            }

            if (isset($search['subject']) && !empty($search['subject']))
            {
                $select->where('subject = ' . implode(' or subject = ', $search['subject']));
            }
            if (isset($search['done']) && $search['done'] != '')
            {
                $select->where('done = ?', (int)$search['done']);
            }
            if (isset($search['sector']) && $search['sector'] != '')
            {
                $select->where('sector = ?', (int)$search['sector']);
            }
            if (isset($search['id_parent']) && $search['id_parent'] != '')
            {
                $select->where('id_parent = ?', (int)$search['id_parent']);
            }
        }
        else if(is_string($search))
        {
            $select->where($search);
        }

        if($count)
        {
            return count($db->fetchAll($select));
        }

        if($per_page !== NULL)
        {
            $tasks = Zend_Paginator::factory($select);
            $tasks->setItemCountPerPage($per_page);
            $tasks->setCurrentPageNumber(isset($search['page']) ? $search['page'] : 1);
        }
        else
        {
            $tasks = $db->fetchAll($select);
        }

        return $tasks;
    }

    public function exportTasks($sort = false, $dir = false, $search = array())
    {
        include(LIBRARY_PATH . '/Tbs/tbs_class.php');
        include(LIBRARY_PATH . '/Tbs/plugins/tbs_plugin_opentbs.php');

        $filesMapper = new Model_FilesMapper();

        $template_name = 'tasks.xlsx';

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

        $tasks = $this->getTasks($sort, $dir, $search);

        $tasks_data = array();
        foreach($tasks as $task)
        {
            $tasks_data[] = array(
                'id' => utf8_decode($task['task_id']),
                'who' => utf8_decode($task['who']),
                'what' => utf8_decode(Model_Task::getWhatForValue($task['what'])),
                'sector' => utf8_decode(Model_Task::getSectorForValue($task['sector'])),
                'date' => utf8_decode(Maco_Utils_DbDate::fromDb($task['when'], Maco_Utils_DbDate::DBDATE_DATE)),
                'time' => utf8_decode(Maco_Utils_DbDate::fromDb($task['when'], Maco_Utils_DbDate::DBDATE_TIME)),
                'time_expected' => utf8_decode($task['time_expected'] . ' ore'),
                'company' => utf8_decode($task['company']),
                'subject' => utf8_decode($task['subject']),
                'subject_data' => utf8_decode($task['subject_data']),
                'done' => $task['done'] ? 'si' : 'no',
            );
        }
        $tbs->MergeBlock('o', $tasks_data);

        $file_name = 'impegni.xlsx';
        $tbs->Show(OPENTBS_DOWNLOAD, $file_name);
        exit;
    }
}