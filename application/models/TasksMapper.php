<?php
/**
 * Created by Marcello Stani.
 * User: marcello
 * Date: 6-set-2010
 * Time: 9.40.28
 * To change this template use File | Settings | File Templates.
 */

class Model_TasksMapper
{
    public function fetch($options = array())
    {
        $db = $this->_getDbAdapter();

        $select = $db->select();

        $select->from('tasks')
                ->joinLeft(array('u2' => 'users'), 'u2.user_id = tasks.id_who', array('who' => 'u2.username', 'id_who' => 'u2.user_id'))
                ->joinLeft(array('cm' => 'companies'), 'cm.company_id = tasks.id_company', array('company' => 'ragione_sociale', 'id_company' => 'cm.company_id'));

        // non facciamo vedere nell'elenco i task generatori (quelli con id parent nullo)
        $select->where('id_parent is not null and id_parent <> 0');

        if(isset($options['where']))
        {
            foreach($options['where'] as $field => $value)
            {
                if($value != '')
                {
                    if($field == 'when')
                    {
                        if($value == 'FUTURI')
                        {
                            $select->where($db->quoteIdentifier('when') . ' > now()');
                        }
                        elseif($value == 'SETTIMANA')
                        {
                            $select->where($db->quoteIdentifier('when') . ' > date(now())')
                                ->where($db->quoteIdentifier('when') . ' < DATE_ADD(now(), INTERVAL 7 DAY)');
                        }
                        elseif($value == 'SETTIMANA_AND_OLD')
                        {
                            $select->where($db->quoteIdentifier('when') . ' < DATE_ADD(now(), INTERVAL 7 DAY)');
                        }
                        elseif($value != '')
                        {
                            $select->where($db->quoteIdentifier('when') . ' < ?', $value);
                        }
                    }
                    elseif($field == 'finishs')
                    {
                        $select->where('finishs > ?', $value);
                    }
                    elseif($field == 'who')
                    {
                        $select->where('u2.username like \'%' . $value . '%\'');
                    }
                    elseif($field == 'address')
                    {
                        $select->where('subject_data like \'%' . $value . '%\'');
                    }
                    elseif($field == 'address')
                    {
                        $select->where($field . ' = ?', (int)$value);
                    }
                    else
                    {
                        $select->where($field . ' = ?', $value);
                    }
                }
            }
        }

        /*
        if(isset($_POST['who']) && $_POST['who'] != '')
        {
            $select->where('u2.username like \'%' . $_POST['who'] . '%\'');
        }
        */
        if(isset($_POST['receiver']) && $_POST['receiver'] != '')
        {
            $select->where('(cn.nome like \'%' . $_POST['receiver'] . '%\' or cn.cognome like \'%' . $_POST['receiver'] . '%\')');
        }
        if(isset($_POST['company']) && $_POST['company'] != '')
        {
            $select->where('cm.ragione_sociale like \'%' . $_POST['company'] . '%\'');
        }

        if(isset($_POST['_s']))
        {
            $sort = $_POST['_s'];
            $dir = (isset($_POST['_d'])) ? $_POST['_d'] : 'ASC';
            $select->order($sort . ' ' . $dir);
        }
        else
        {
            $select->order('when ASC');
        }

        $values = $db->fetchAll($select);

        return $values;
    }

    public function getDependencesFor($id)
    {
        $db = $this->_getDbAdapter();

        $select = $db->select();

        $select->from('tasks', array())
                ->joinLeft(array('u2' => 'users'), 'u2.user_id = tasks.id_who', array('who' => 'u2.username'))
                ->joinLeft(array('cm' => 'companies'), 'cm.company_id = tasks.id_company', array('company' => 'ragione_sociale'))
                ->where('tasks.task_id = ?', $id);

        return $db->fetchRow($select);
    }

    public function save($data)
    {
        $task = new Task($data);
        
    }

    public function find($id)
    {
        $db = $this->_getDbAdapter();
    }
    
    /**
     * Returns the db adapter
     *
     * @return Zend_Db_Adapter_Abstract
     */
    protected function _getDbAdapter()
    {
        return Zend_Registry::get('dbAdapter');
    }
}
