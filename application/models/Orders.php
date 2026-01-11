<?php
  
class Model_Orders
{
    protected $_data = array(
        'id' => '',
        'id_offer' => '',
        'rali_date' => '',
        'cod_offer' => '',
        'valore_g_uomo' => '',
        'n_incontri' => '',
        'n_ore_studio' => '',
        'date_chiusura_richiesta' => '',
        'id_status' => '',
    	'id_dtg' => '',
        'note' => '',
        'date_closed' => '',
    );
    
    protected $_validators = array(
        'id' => array(
            'allowEmpty' => true,
        ),
        'id_offer' => array(
            'presence' => 'required',
        ),
        'id_dtg' => array(
            'presence' => 'required',
        ),
        'rali_date' => array(
            'allowEmpty' => true,
        ),
        'cod_offer' => array(
            'allowEmpty' => true,
        ),
        'valore_g_uomo' => array(
            'allowEmpty' => true,
        ),
        'n_incontri' => array(
            'allowEmpty' => true,
        ),
        'n_ore_studio' => array(
            'allowEmpty' => true,
        ),
        'date_chiusura_richiesta' => array(
            'allowEmpty' => true,
        ),
        'date_closed' => array(
            'allowEmpty' => true,
        ),
        'id_status' => array(
            'allowEmpty' => true,
        ),
        'note' => array(
            'allowEmpty' => true,
        ),
    );
    
    protected $_filters = array(
        '*' => 'StringTrim',
    );
    
    public function save($data)
    {
        // TODO : This validators could be splitted per each data model
        // TODO: Manca il controllo dell'uguaglianza delle 2 password

        $input = new Zend_Filter_Input($this->_filters, $this->_validators);

        $input->setData($data);

        if($input->hasInvalid() || $input->hasMissing())
        {
            return $input->getMessages();
        }
        $table = $this->_getTable();

        $id = $input->id;

        $edit = ! empty($id);

        $filter = new Zend_Filter_LocalizedToNormalized();
        
        $rali_date = $filter->filter($input->rali_date);
        $rali_date = $rali_date['year'] . '-' . $rali_date['month'] . '-' . $rali_date['day'];
        
        $date_chiusura_richiesta = $filter->filter($input->date_chiusura_richiesta);
        $date_chiusura_richiesta = $date_chiusura_richiesta['year'] . '-' . $date_chiusura_richiesta['month'] . '-' . $date_chiusura_richiesta['day'];
        
        
        $safeData = array(
            'id_offer' => $input->id_offer,
            'rali_date' => $rali_date,
            'cod_offer' => $input->cod_offer,
            'valore_g_uomo' => $input->valore_g_uomo,
            'n_incontri' => $input->n_incontri,
            'n_ore_studio' => $input->n_ore_studio,
            'date_chiusura_richiesta' => $date_chiusura_richiesta,
            'id_status' => $input->id_status,
            'note' => $input->note,
        	'id_dtg' => $input->id_dtg
        );
        
        if(!$edit)
        {
            $id = $table->insert($safeData);
        }
        else
        {
            $table->update($safeData, array('id = ?' => $id));
        }
        
        return $id;
    }
    
    public function getDetail($id)
    {
        $table = $this->_getTable();
        
        
        
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
                
    /**
     * Returns the table db adapter
     *
     * @return Model_DbTables_Orders
     */
    protected function _getTable()
    {
        if (null === $this->_table)
        {
            // since the dbTable is not a library item but an application item,
            // we must require it to use it
            $this->_table = new Model_DbTables_Orders(array('db' => 'dbAdapter'));
        }
        return $this->_table;
    }
}