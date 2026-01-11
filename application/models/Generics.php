<?php

class Model_Generics {
    protected $_categories = array(
        'singolare' => 'Categoria',
        'plurale' => 'Categorie',
        'pk' => 'category_id',
        'fields' => array(
            'name' => array(
                'label' => 'Nome',
                'class' => 'input-medium required',
                'required' => true
            ),
            'description' => array(
                'label' => 'Descrizione',
                'class' => 'input-big',
                'required' => false
            )
        )
    );

    protected $_fatturati = array(
        'singolare' => 'Fatturato',
        'plurale' => 'Fatturati',
        'pk' => 'fatturato_id',
        'fields' => array(
            'name' => array(
                'label' => 'Nome',
                'class' => 'input-medium required',
                'required' => true
            ),
            'description' => array(
                'label' => 'Descrizione',
                'class' => 'input-big',
                'required' => false
            )
        )
    );

    protected $_organici_medi = array(
        'singolare' => 'Organico Medio',
        'plurale' => 'Organici Medi',
        'pk' => 'organico_medio_id',
        'fields' => array(
            'name' => array(
                'label' => 'Nome',
                'class' => 'input-medium required',
                'required' => true
            ),
            'description' => array(
                'label' => 'Descrizione',
                'class' => 'input-big',
                'required' => false
            )
        )
    );

    protected $_contact_titles = array(
        'singolare' => 'Titolo Contatto',
        'plurale' => 'Titoli Contatto',
        'pk' => 'contact_title_id',
        'fields' => array(
            'name' => array(
                'label' => 'Nome',
                'class' => 'input-medium required',
                'required' => true
            ),
            'description' => array(
                'label' => 'Descrizione',
                'class' => 'input-big',
                'required' => false
            )
        )
    );

    protected $_status = array(
        'singolare' => 'Stato',
        'plurale' => 'Stati',
        'pk' => 'status_id',
        'fields' => array(
            'name' => array(
                'label' => 'Nome',
                'class' => 'input-medium required',
                'required' => true
            ),
            'description' => array(
                'label' => 'Descrizione',
                'class' => 'input-big',
                'required' => false
            )
        )
    );

    protected $_decorators = array(
        'singolare' => 'Decoratore',
        'plurale' => 'Decoratori',
        'pk' => 'decorator_id',
        'fields' => array(
            'caption' => array(
                'label' => 'Caption',
                'class' => 'input-medium required',
                'required' => true
            ),
            'text' => array(
                'label' => 'Corpo',
                'class' => 'input-big ckeditor',
                'element' => 'textarea',
                'require_js' => array('ckeditor/ckeditor.js', 'js/common/ckeditor.js'),
                'required' => false
            )
        )
    );

    protected $_ea = array(
        'singolare' => 'EA',
        'plurale' => 'EA',
        'pk' => 'ea_id',
        'fields' => array(
            'name' => array(
                'label' => 'Nome',
                'class' => 'input-medium required',
                'required' => true
            ),
            'description' => array(
                'label' => 'Descrizione',
                'class' => 'input-big',
                'required' => false
            )
        )
    );

    protected $_conosciuto_come = array(
        'singolare' => 'Come',
        'plurale' => 'Come',
        'pk' => 'conosciuto_come_id',
        'fields' => array(
            'name' => array(
                'label' => 'Nome',
                'class' => 'input-medium required',
                'required' => true
            ),
            'description' => array(
                'label' => 'Descrizione',
                'class' => 'input-big',
                'required' => false
            )
        )
    );

    protected $_interests = array(
        'singolare' => 'Programma di Interesse',
        'plurale' => 'Programmi di Interesse',
        'pk' => 'interest_id',
        'fields' => array(
            'name' => array(
                'label' => 'Nome',
                'class' => 'input-medium required',
                'required' => true
            ),
            'description' => array(
                'label' => 'Descrizione',
                'class' => 'input-big',
                'required' => false
            )
        )
    );

    protected $_internals = array(
        'singolare' => 'Azienda Interna',
        'plurale' => 'Aziende Interne',
        'pk' => 'internal_id',
        'fields' => array(
            'full_name' => array(
                'label' => 'Nome',
                'class' => 'input-medium required',
                'required' => true
            ),
            'abbr' => array(
                'label' => 'Abbr',
                'class' => 'input-medium required',
                'required' => true
            ),
            'partita_iva' => array(
                'label' => 'Partita IVA',
                'class' => 'input-medium validate-digits maxLength:11 minLength:11',
                'required' => false,
            ),
            'sede_legale' => array(
                'label' => 'Sede Legale',
                'class' => 'input-big',
                'required' => false
            ),
            'rea' => array(
                'label' => 'R.E.A.',
                'class' => 'input-medium',
                'required' => false
            ),
        )
    );

    protected $_offices = array(
        'singolare' => 'Sede',
        'plurale' => 'Sedi',
        'pk' => 'office_id',
        'fields' => array(
            'name' => array(
                'label' => 'Nome',
                'class' => 'input-medium required',
                'required' => true
            ),
            'id_internal' => array(
                'label' => 'Azienda Interna',
                'class' => 'input-medium required',
                'required' => true,
                'depends' => array(
                    'pk' => 'internal_id',
                    'table' => 'internals',
                    'field' => array('internal' => 'abbr')
                )
            ),
            'indirizzo' => array(
                'label' => 'Indirizzo',
                'class' => 'input-big',
                'required' => false,
            ),
            'telefono' => array(
                'label' => 'Telefono',
                'class' => 'input-medium',
                'required' => false
            ),
            'fax' => array(
                'label' => 'Fax',
                'class' => 'input-medium',
                'required' => false
            ),
            'description' => array(
                'label' => 'Descrizione',
                'class' => 'input-big',
                'required' => false
            ),
        )
    );

    protected $_ibans = array(
        'singolare' => 'IBAN',
        'plurale' => 'IBAN',
        'pk' => 'iban_id',
        'fields' => array(
            'bank' => array(
                'label' => 'Banca',
                'class' => 'input-medium required',
                'required' => true
            ),
            'iban' => array(
                'label' => 'IBAN',
                'class' => 'input-medium required',
                'required' => true
            ),
        )
    );


    protected $_services = array(
        'singolare' => 'Servizio',
        'plurale' => 'Servizi',
        'pk' => 'service_id',
        'fields' => array(
            'name' => array(
                'label' => 'Nome',
                'class' => 'input-medium required',
                'required' => true
            ),
            'cod' => array(
                'label' => 'Codice',
                'class' => 'input-small required',
                'required' => true
            ),
            'description' => array(
                'label' => 'Descrizione',
                'class' => 'input-big',
                'required' => false
            ),
        )
    );

    protected $_subservices = array(
        'singolare' => 'Sotto-Servizio',
        'plurale' => 'Sotto-Servizi',
        'pk' => 'subservice_id',
        'fields' => array(
            'name' => array(
                'label' => 'Nome',
                'class' => 'input-medium required',
                'required' => true
            ),
            'id_service' => array(
                'label' => 'Servizio',
                'class' => 'input-medium required',
                'required' => true,
                'depends' => array(
                    'pk' => 'service_id',
                    'table' => 'services',
                    'field' => array('service' => 'name')
                )
            ),
            'cod' => array(
                'label' => 'Codice',
                'class' => 'input-small required',
                'required' => true
            ),
            'description' => array(
                'label' => 'Descrizione',
                'class' => 'input-big',
                'required' => false
            ),
        )
    );

    protected $_moment_defs = array(
        'singolare' => 'Momento di Lavorazione',
        'plurale' => 'Momenti di Lavorazioni',
        'pk' => 'moment_def_id',
        'fields' => array(
            'id_service' => array(
                'label' => 'Servizio',
                'class' => 'input-medium required',
                'required' => true,
                'depends' => array(
                    'pk' => 'service_id',
                    'table' => 'services',
                    'field' => array('service' => 'name')
                )
            ),
            'id_subservice' => array(
                'label' => 'Sotto-Servizio',
                'class' => 'input-medium required',
                'required' => true,
                'depends' => array(
                    'with-parent' => array(
                        'self_field' => 'id_service',
                        'parent_field' => 'subservice_id',
                        'self_parent_field' => 'id_service',
                        'parent_table' => 'services',
                        'self_label_field' => 'name'
                    ),
                    'pk' => 'subservice_id',
                    'table' => 'subservices',
                    'field' => array('subservice' => 'name')
                )
            ),
            'name' => array(
                'label' => 'Nome',
                'class' => 'input-medium required',
                'required' => true
            ),
            'description' => array(
                'label' => 'Descrizione',
                'class' => 'input-big',
                'required' => false
            ),
        )
    );

    protected $_interests_levels = array(
        'singolare' => 'Livello di Interesse',
        'plurale' => 'Livelli di Interesse',
        'pk' => 'interests_level_id',
        'fields' => array(
            'name' => array(
                'label' => 'Nome',
                'class' => 'input-medium required',
                'required' => true
            ),
            'description' => array(
                'label' => 'Descrizione',
                'class' => 'input-big',
                'required' => false
            )
        )
    );

    protected $_pagamenti = array(
        'singolare' => 'Metodo di Pagamento',
        'plurale' => 'Metodi di Pagamento',
        'pk' => 'pagamento_id',
        'fields' => array(
            'name' => array(
                'label' => 'Nome',
                'class' => 'input-medium required',
                'required' => true
            ),
            'description' => array(
                'label' => 'Descrizione',
                'class' => 'input-big',
                'required' => false
            )
        )
    );

    protected $_offer_status = array(
        'singolare' => 'Stato Offerta',
        'plurale' => 'Stati Offerta',
        'pk' => 'offer_status_id',
        'fields' => array(
            'name' => array(
                'label' => 'Nome',
                'class' => 'input-medium required',
                'required' => true
            ),
            'description' => array(
                'label' => 'Descrizione',
                'class' => 'input-big',
                'required' => false
            ),
            'id_depends_on' => array(
                'label' => 'Attiva Dopo',
                'class' => 'input-medium required',
                'required' => false,
                'depends' => array(
                    'pk' => 'offer_status_id',
                    'table' => 'offer_status',
                    'field' => array('name_depends_on' => 'name')
                )
            ),
        ),
    );

    protected $_order_status = array(
        'singolare' => 'Stato Commessa',
        'plurale' => 'Stati Commessa',
        'pk' => 'order_status_id',
        'fields' => array(
            'name' => array(
                'label' => 'Nome',
                'class' => 'input-medium required',
                'required' => true
            ),
            'description' => array(
                'label' => 'Descrizione',
                'class' => 'input-big',
                'required' => false
            )
        )
    );

    protected $_regioni = array(
        'singolare' => 'Regione',
        'plurale' => 'Regioni',
        'pk' => 'regione_id',
        'no-item-info' => true,
        'fields' => array(
            'nome' => array(
                'label' => 'Nome',
                'class' => 'input-medium required',
                'required' => true
            ),
        )
    );

    protected $_province = array(
        'singolare' => 'Provincia',
        'plurale' => 'Province',
        'pk' => 'provincia_id',
        'no-item-info' => true,
        'fields' => array(
            'nome' => array(
                'label' => 'Nome',
                'class' => 'input-medium required',
                'required' => true
            ),
            'abbr' => array(
                'label' => 'Abbr',
                'class' => 'input-medium required',
                'required' => true
            ),
            'id_regione' => array(
                'label' => 'Regione',
                'class' => 'input-medium required',
                'required' => true,
                'depends' => array(
                    'pk' => 'regione_id',
                    'table' => 'regioni',
                    'field' => array('regione' => 'nome')
                )
            ),
        )
    );

    protected $_comuni = array(
        'singolare' => 'Comune',
        'plurale' => 'Comuni',
        'pk' => 'comune_id',
        'no-item-info' => true,
        'fields' => array(
            'nome' => array(
                'label' => 'Nome',
                'class' => 'input-medium required',
                'required' => true
            ),
            'cap' => array(
                'label' => 'Abbr',
                'class' => 'input-medium required',
                'required' => true
            ),
            'id_provincia' => array(
                'label' => 'Province',
                'class' => 'input-medium required',
                'required' => true,
                'depends' => array(
                    'pk' => 'provincia_id',
                    'table' => 'province',
                    'field' => array('provincia' => 'nome')
                )
            ),
        )
    );


    /**
     * Returns the db adapter
     *
     * @return Zend_Db_Adapter_Abstract
     */
    protected function _getDbAdapter() {
        return Zend_Registry::get('dbAdapter');
    }

    public function getList($table, $search = array(), $per_page = NULL, $id_internal) {
        $fields = $this->getFieldsForTable($table);

        $realFields = array($this->getPrimaryKeyForTable($table));

        $db = $this->_getDbAdapter();

        $select = $db->select();
        $select->from($table, array());

        foreach ($fields as $fieldName => $options)
        {
            if (isset($options['depends'])) {
                $f = $options['depends']['field'];
                $t = $options['depends']['table'];
                $tin = $t;
                if ($table == $tin) {
                    // TODO: considerato solo un caso
                    $tin .= '_2';
                }
                $tpk = $options['depends']['pk'];
                $select->joinLeft($t, $tin . '.' . $tpk . ' = ' . $table . '.' . $fieldName, $f);

                // search 
                $ks = array_keys($f);
                $kk = $ks[0];
                $ff = $f[$kk];
                if(isset($search[$kk]) && $search[$kk] != '')
                {
                    $select->where($t . '.' . $ff . ' like ' . $db->quote('%' . $search[$kk] . '%'));
                }

            }
            //			else
            {
                $realFields[] = $fieldName;

                // search 
                if(isset($search[$fieldName]) && $search[$fieldName] != '')
                {
                    $select->where($table . '.' . $fieldName . ' like ' . $db->quote('%' . $search[$fieldName] . '%'));
                }
            }
        }

        $select->columns($realFields);

        //order
        if(isset($search['_s']) && $search['_s'] != '')
        {
            $sort = $search['_s'];
            $dir = isset($search['_d']) ? $search['_d'] : 'ASC';
        }
        else
        {
            $keys = array_keys($fields);
            $sort = $keys[0];
            $dir = 'ASC';
        }

        $select->order($sort . ' ' . $dir);

        // where
        if($id_internal)
        {
            if($table == 'subservices')
            {
                $select->join('subservice_internal', 'subservice_id = id_subservice and id_internal = ' . $db->quote($id_internal));
            }
            elseif($table == 'services')
            {
                $select->join('service_internal', 'service_id = id_service and id_internal = ' . $db->quote($id_internal));
            }
            elseif($table == 'moment_defs')
            {
                $select->join('subservice_internal', 'moment_defs.id_subservice = subservice_internal.id_subservice and subservice_internal.id_internal = ' . $db->quote($id_internal));
            }
        }


        // pagination
        $values = Zend_Paginator::factory($select);
        $values->setItemCountPerPage($per_page);
        $values->setCurrentPageNumber(isset($search['page']) ? $search['page'] : 1);

        return $values;

        $data = $db->fetchAll($select);

        return $data;
    }

    public function getFieldsForTable($table) {
        $var = $this->_checkExists($table);
        $arr = $this->$var;
        return $arr['fields'];
    }

    public function getPrimaryKeyForTable($table) {
        $var = $this->_checkExists($table);
        $arr = $this->$var;
        return $arr['pk'];
    }

    public function getLabelsForTable($table) {
        $var = $this->_checkExists($table);
        $arr = $this->$var;
        return array('singolare' => $arr['singolare'], 'plurale' => $arr['plurale']);
    }

    public function getTableInfo($table) {
        $var = $this->_checkExists($table);
        return $this->$var;
    }

    protected function _checkExists($table) {
        $var = '_' . strtolower($table);
        if (!isset($this->$var)) {
            throw new Exception('Table not found: ' . $table);
        }

        return $var;
    }

    public function getEmptyDetail($table) {
        $fields = $this->getFieldsForTable($table);
        $pk = $this->getPrimaryKeyForTable($table);
        $ret = array();
        $ret[$pk] = '';
        foreach ($fields as $k => $v)
        {
            $ret[$k] = '';
        }
        return $ret;
    }

    public function getDetail($table, $id, $id_internal = false) {
        $fields = $this->getFieldsForTable($table);
        $pk = $this->getPrimaryKeyForTable($table);

        $realFields = array($pk);

        $db = $this->_getDbAdapter();
        $select = $db->select();
        $select->from($table, array());

        foreach ($fields as $fieldName => $options)
        {
            if (isset($options['depends'])) {
                $t = $options['depends']['table'];
                $f = $options['depends']['field'];
                $tpk = $options['depends']['pk'];
                $select->joinLeft($t, $t . '.' . $tpk . ' = ' . $table . '.' . $fieldName, $f);
            }
            //else
            {
                $realFields[] = $fieldName;
            }
        }

        $select->columns($realFields)
                ->where($table . '.' . $pk . ' = ?', $id);

        if($id_internal)
        {
            if($table == 'subservices')
            {
                $select->join('subservice_internal', 'subservice_id = id_subservice and id_internal = ' . $db->quote($id_internal));
            }
            elseif($table == 'services')
            {
                $select->join('service_internal', 'service_id = id_service and id_internal = ' . $db->quote($id_internal));
            }
        }

        $data = $db->fetchRow($select);

        return $data;
    }

    public function save($table, $data, $id_internal = false) {
        $filters = array(
            '*' => 'StringTrim',
        );

        // TODO : This validators could be splitted per each data model

        // TODO: Manca il controllo dell'uguaglianza delle 2 password

        $pk = $this->getPrimaryKeyForTable($table);

        $validators = array(
            $pk => array(
                'allowEmpty' => true
            ),
        );

        $fields = $this->getFieldsForTable($table);
        foreach ($fields as $field => $info)
        {
            $validators[$field] = ($info['required'])
                    ? array('presence' => 'required')
                    : array('allowEmpty' => true);
        }

        $input = new Zend_Filter_Input($filters, $validators);

        $input->setData($data);

        if ($input->hasInvalid() || $input->hasMissing()) {
            return $input->getMessages();
        }

        $db = $this->_getDbAdapter();

        $db->beginTransaction();
        try
        {
            $id = $input->$pk;
            $edit = !empty($id);

            $safeData = array();

            foreach ($fields as $field => $info)
            {
                $safeData[$field] = $input->getUnescaped($field);
            }

            $aut = Zend_Auth::getInstance()->getIdentity();

            if (!$edit) {
                if(!isset($field['no-mod-info']) || !$field['no-mod-info'])
                {
                    $safeData['date_created'] = new Zend_Db_Expr('now()');
                    $safeData['created_by'] = $aut->user_id;
                }

                $db->insert($table, $safeData);
                $id = $db->lastInsertId();

                if($id_internal)
                {
                    if($table == 'subservices')
                    {
                        $db->insert('subservice_internal', array(
                            'id_internal' => $id_internal,
                            'id_subservice' => $id
                        ));
                    }
                    elseif($table == 'services')
                    {
                        $db->insert('service_internal', array(
                            'id_internal' => $id_internal,
                            'id_service' => $id
                        ));
                    }
                }
            }
            else
            {
                if(!isset($field['no-mod-info']) || !$field['no-mod-info'])
                {
                    $safeData['date_modified'] = new Zend_Db_Expr('now()');
                    $safeData['modified_by'] = $aut->user_id;
                }

                $db->update($table, $safeData, array($pk . ' = ?' => $id));
            }

            $db->commit();
            return $id;
        }
        catch (Exception $e)
        {
            $db->rollBack();
            return array('database_error' => $e->getFile() . ' - ' . $e->getLine() . ' - ' . $e->getMessage() . ' - ' . $e->getTraceAsString());
        }
    }

    public function delete($table, $id) {
        $db = $this->_getDbAdapter();
        $db->beginTransaction();
        $safeId = $db->quote($id);

        try
        {
            // TODO: CONTROLLARE CHE NON CI SIANO ENTITA COLLEGATE
            $db->delete($table, 'id = ' . $safeId);
            return $db->commit();
        }
        catch (Exception $e)
        {
            $db->rollBack();
            return array('database_error' => $e->getMessage());
        }
    }

    public function getSubservices($post, $id_internal = false)
    {
        $db = $this->_getDbAdapter();

        $select = $db->select();
        $select->from('subservices', array('name', 'cod', 'subservice_id', 'description'))
                ->joinLeft('services', 'id_service = service_id', array('service' => 'services.name', 'service_id'));

        if($id_internal)
        {
            $select->join('subservice_internal', 'subservice_id = subservice_internal.id_subservice and subservice_internal.id_internal = ' . $db->quote($id_internal));
            $select->join('service_internal', 'service_id = service_internal.id_service and service_internal.id_internal = ' . $db->quote($id_internal));
        }

        $sort = isset($post['_s']) && $post['_s'] != '' ? $post['_s'] : 'name';

        $dir = isset($post['_d']) && $post['_d'] != '' ? $post['_d'] : 'ASC';
        $perpage = isset($post['perpage']) ? $post['perpage'] : Zend_Registry::get('config')->entries_per_page;

        $select->order($sort . ' ' . $dir);

        if(isset($post['name']) && $post['name'] != '')
        {
            $select->where('subservices.name like ' . $db->quote('%' . $post['name'] . '%'));
        }
        if(isset($post['service']) && $post['service'] != '')
        {
            $select->where('services.name like ' . $db->quote('%' . $post['service'] . '%'));
        }
        if(isset($post['cod']) && $post['cod'] != '')
        {
            $select->where('subservices.cod like ' . $db->quote('%' . $post['cod'] . '%'));
        }
        if(isset($post['description']) && $post['description'] != '')
        {
            $select->where('subservices.description like ' . $db->quote('%' . $post['description'] . '%'));
        }

        if($perpage !== NULL)
        {
            $data = Zend_Paginator::factory($select);
            $data->setItemCountPerPage($perpage);
            $data->setCurrentPageNumber(isset($post['page']) ? $post['page'] : 1);
        }
        else
        {
            $data = $db->fetchAll($select);
        }

        //$data = $db->fetchAll($select);

        $files = new Model_FilesMapper();

        if($perpage !== NULL)
        {
            $items = $data->getCurrentItems();
            foreach($items as $k => $d)
            {
                $path = $files->getTemplatePath(false) . 'template_' . $d['service_id'] . '_' . $d['subservice_id'] . '.docx';
                if($files->pathExists($path))
                {
                    $items[$k]['path'] = base64_encode($path);
                    $items[$k]['template'] = 'scarica';
                }
                else
                {
                }
            }
        }
        else
        {
            foreach($data as $k => $d)
            {
                $path = $files->getTemplatePath(false) . 'template_' . $d['service_id'] . '_' . $d['subservice_id'] . '.docx';
                if($files->pathExists($path))
                {
                    $data[$k]['path'] = base64_encode($path);
                    $data[$k]['template'] = 'scarica';
                }
                else
                {
                }
            }
        }
        return $data;
    }

    public function getSubservice($id, $id_internal = false)
    {
        $db = $this->_getDbAdapter();
        $select = $db->select();
        $select->from('subservices', array('name', 'cod', 'description', 'subservice_id', 'description'))
                ->joinLeft('services', 'service_id = id_service', array('service' => 'services.name', 'service_id'))
                ->where('subservice_id = ?', $id);

        if($id_internal)
        {
            $select->join('subservice_internal', 'subservice_id = id_subservice and id_internal = ' . $db->quote($id_internal));
        }

        $data = $db->fetchRow($select);

        $files = new Model_FilesMapper();

        $path = $files->getTemplatePath(false) . 'template_' . $data['service_id'] . '_' . $data['subservice_id'] . '.docx';

        if($files->pathExists($path))
        {
            $data['path'] = base64_encode($path);
        }
        else
        {
            $data['path'] = false;
        }


        return $data;
    }
}