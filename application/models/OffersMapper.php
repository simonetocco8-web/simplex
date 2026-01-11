<?php
           
class Model_OffersMapper
{
    protected $_moments = array(
		'id' => '',
		'id_offer' => '',
		'id_revision' => '',
		'importo' => '',
		'tipologia' => '',
    	'expected_date' => '',
    	'fatturazione' => '0',
	);
	
	public function getLastOffers($number)
	{
		return $this->getList(null, null, null, $number);
	}
	 
    public function getEmptyDetail()
    {
        $offers = new Model_Offers();
        
        $data = $offers->getEmptyDetail();
        
        $data['date_offer'] = date('d/m/Y');
        
        $data['moments'] = array(
        	$this->_moments
        );
        
        return $data;
    }
    
    public function getStatusById($id)
    {
    	$db = $this->_getDbAdapter();
    	$sql = 'select id_status from offers where id = ?';
        $bind = array($id);
        
        if($depends !== null)
        {
            $sql .= ' and id_depends_on = ?';
            $bing[] = $depends;
        }
        
    	return $db->fetchOne($sql, $bind);
    }
    
	public function setStatus($id_offer, $id_status)
    {
    	$db = $this->_getDbAdapter();
    	
    	return $db->update('offers', 
    		array('id_status' => (int)$id_status), 
    		'id = ' . $db->quote($id_offer));
    }
    
    public function activateRevision($id_offer, $rev)
    {
    	$db = $this->_getDbAdapter();
    	$id = $db->fetchOne('select id from offers where id_offer = ' 
    		. $db->quote($id_offer) . ' and revision = ' 
    		. $db->quote($rev));
    	
    	return $this->_activeRevision($id);
    }
    
    protected function _activeRevision($id)
    {
    	$db = $this->_getDbAdapter();
    	$db->beginTransaction();
    	try 
    	{
			$id_offer = $db->fetchOne('select id_offer from offers where id = ' . $db->quote($id));
			$db->update('offers', array('active' => 1), 'id = ' . $db->quote($id));
			$db->update('offers', array('active' => 0), 'id_offer = '. $id_offer . ' and id <> ' . $db->quote($id));
			$db->commit();
			return true;
    	}
    	catch (Exception $e)
    	{
    		$db->rollBack();
    		return false;
    	}
    }
    
    public function getRevisionsStory($id_offer)
    {
        $db = $this->_getDbAdapter();
        
        $select = $db->select();
        
        $select->from('offers', array('revision', 'date_created', 'created_by', 'date_modified', 'modified_by', 'active'))
            ->joinLeft(array('u1' => 'users'), 'u1.id = offers.created_by', array('cusername' => 'username'))
            ->joinLeft(array('u2' => 'users'), 'u2.id = offers.modified_by', array('musername' => 'username'))
            ->where('id_offer = ?', $id_offer);
        
        return $db->fetchAll($select);
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
    
    public function save($data, $inputNamePrefix = '')
    {
        $db = $this->_getDbAdapter();
        $db->beginTransaction();
    
        try
        {
            $edit = isset($data['id']) && $data['id'] != '';

            // informazioni personali
            $offerModel = new Model_Offers();

            $cdata = array(
                'id' => $data[$inputNamePrefix . 'id'],
            	'internal' => $data[$inputNamePrefix . 'internal'],
                'id_company' => $data[$inputNamePrefix . 'id_company'],
                'id_service' => $data[$inputNamePrefix . 'id_service'],
                'id_subservice' => $data[$inputNamePrefix . 'id_subservice'],
                'luogo' => $data[$inputNamePrefix . 'luogo'],
                'id_partner' => $data[$inputNamePrefix . 'id_partner'],
                'promotore_percent' => $data[$inputNamePrefix . 'promotore_percent'],
                'date_offer' => $data[$inputNamePrefix . 'date_offer'],
                'validita' => $data[$inputNamePrefix . 'validita'],
                'scadenza' => $data[$inputNamePrefix . 'scadenza'],
                'subject' => $data[$inputNamePrefix . 'subject'],
                'note' => $data[$inputNamePrefix . 'note'],
                'scadenze' => $data[$inputNamePrefix . 'scadenze'],
                'id_company_contact' => $data[$inputNamePrefix . 'id_company_contact'],
                'id_interest' => $data[$inputNamePrefix . 'id_interest'],
                'sconto' => $data[$inputNamePrefix . 'sconto'],
                'id_pagamento' => $data[$inputNamePrefix . 'id_pagamento'],
            	'id_rco' => $data[$inputNamePrefix . 'id_rco'],
            	'segnalato_da' => $data[$inputNamePrefix . 'segnalato_da'],
            
            	'nr' => $data[$inputNamePrefix . 'nr'],
            	'id_offer' => $data[$inputNamePrefix . 'id_offer'],
            	'revision' => $data[$inputNamePrefix . 'revision'],
            );

            $id = $offerModel->save($cdata);
            
            $this->_activeRevision($id);
            
            unset($offerModel);
            
            $dbHelper = new Maco_Db_Helper($db);            

            $util = new Maco_Input_Utils();
            
            $partials = $util->formatDataForMultipleFields(array(
            	'id', 'id_offer', 'index', 'importo', 'tipologia', 'expected_date', 'fatturazione'
            	), $inputNamePrefix. 'moments_', $data);
            
            foreach($partials as $k => $partial)
            {
            	$moment_id = $partial['id'];
            	if(isset($partial['id']) && $id == $partial['id_offer'])
            	{
					// se id_offer è lo stesso -> allora modfico
					$db->update('moments', array(
						'importo' => $partial['importo'],
						'tipologia' => $partial['tipologia'],
						'expected_date' => implode('-', array_reverse(explode('/', $partial['expected_date']))),
						'fatturazione' => (isset($partial['fatturazione']) && $partial['fatturazione'] == 1) ? 1 : 0,
						'index' => ($k + 1)), array('id = ?' => $partial['id']));            	
            	}
            	else 
            	{
            		// altrimenti aggiungo
            		$db->insert('moments', array(
						'importo' => $partial['importo'],
						'tipologia' => $partial['tipologia'],
            			'expected_date' => implode('-', array_reverse(explode('/', $partial['expected_date']))),
						'fatturazione' => (isset($partial['fatturazione']) && $partial['fatturazione'] == 1) ? 1 : 0,
            			'id_offer' => $id,
						'index' => ($k + 1)));
            		$moment_id = $db->lastInsertId();
            	}
 				$presents[] = $moment_id;
            }
            
            // eliminiamo le non pi� presenti
            $db->query('delete from moments ' .
                'where id_offer = ' . $id . 
                ((!empty($presents)) 
                	? ' and id not in  (' . implode(', ', $presents) . ')'
                	: ''));
            
            
            $db->commit();

            return $id;
        }
        catch (Exception $e)
        {
            $db->rollBack();

            return array('database_error' => $e->getFile() . ' - ' . $e->getLine() . ' - ' . $e->getMessage() . ' - ' . $e->getTraceAsString());
        }        
    }
    
    public function getDetail($id, $rev = null)
    {
    	$db = $this->_getDbAdapter();

    	$select = $db->select();
    	
    	$select->from('offers')
    		->joinLeft('companies', 'companies.id = offers.id_company', 'ragione_sociale')
    		->joinLeft('services', 'services.id = offers.id_service', array('service' => 'services.name'))
    		->joinLeft('subservices', 'subservices.id = offers.id_subservice', array('subservice' => 'subservices.name', 'subservicecode' => 'subservices.cod'))
    		->joinLeft('interests_levels', 'interests_levels.id = offers.id_interest', array('level' => 'interests_levels.name'))
    		->joinLeft(array('c2' => 'companies'), 'c2.id = offers.id_partner', array('partner' => 'c2.ragione_sociale'))
    		//->joinLeft('contacts', 'contacts.id = offers.segnalato_da', array('snome' => 'contacts.nome', 'scognome' => 'contacts.cognome'))
    		->joinLeft(array('co2' => 'contacts'), 'co2.id = offers.id_company_contact', array('cnome' => 'co2.nome', 'ccognome' => 'co2.cognome'))
    		->joinLeft('users', 'users.id = offers.id_rco', array('rco' => 'users.username'))
    		->joinLeft('offer_status', 'offer_status.id = offers.id_status', array('status' => 'offer_status.name'))
    		->joinLeft('pagamenti', 'pagamenti.id = offers.id_pagamento', array('pagamento' => 'pagamenti.name'))
            ->joinLeft('orders', 'orders.id_offer = offers.id', array('id_order' => 'orders.id'));
    		
    	if($rev === NULL)
    	{
    		$select->where('offers.id = ?', $id);
    	}
    	else
    	{
    		$select->where('offers.id_offer = ?', $id)
    			->where('offers.revision = ?', $rev);	
    	}

		$offer = $db->fetchRow($select);   		
		
		$id = $offer['id'];

		unset($select);
		$select = $db->select();
		
		$select->from('moments', array('importo', 'tipologia', 'id', 'id_offer', 'expected_date', 'fatturazione', 'date_done', 'done'))
			->where('id_offer = ?', $id)
			->order('index asc');
		
		$moments = $db->fetchAll($select);
		
		$offer['moments'] = (!empty($moments)) 
			? $moments 
			: array($this->_moments);

        if(!empty($moments))
        {
            $tot = 0;
            $totraw = 0;
            foreach($moments as $m)
            {
                $p = $m['importo'];
                $totraw += $p;
                if($offer['sconto'] != '' && $offer['sconto'] != 0)
                {
                    $p = $p - ($p * $offer['sconto'] / 100);
                }
                if($offer['promotore_percent'] != '' && $offer['promotore_percent'] != 0)
                {
                    $p = $p - ($p * $offer['promotore_percent'] / 100);
                }
                $tot += $p;
            }
            $offer['total'] = $tot;
            $offer['total_raw'] = $totraw;
        }
        else
        {
            $offer['total'] = 0;
        }
           
		unset($select);
		$select = $db->select();
		
		$select->from('offers', 'revision')
			->where('offers.id_offer = ?', $offer['id_offer']);
		
		$offer['revisions'] = $db->fetchCol($select);

    	return $offer;
    }
    
    public function getList($sort = null, $dir = 'ASC', $search = array(), $count = null)
    {
        $db = $this->_getDbAdapter();

        $select = $db->select();
			
        $select->from('offers', array('id', 'internal', 'id_offer', 'revision', 'year', 'date_offer'))
	        ->joinLeft(array('c1' => 'companies'), 'c1.id = offers.id_company', array('cliente' => 'ragione_sociale'))
	        ->joinLeft(array('c2' => 'companies'), 'c2.id = offers.id_partner', array('partner' => 'ragione_sociale'))
	        ->joinLeft(array('s' => 'services'), 's.id = offers.id_service', array('service' => 's.name'))
	        ->joinLeft(array('ss' => 'subservices'), 'ss.id = offers.id_subservice', array('subservice' => 'ss.name', 'subservicecode' => 'ss.cod'))
	        ->joinLeft(array('u' => 'users'), 'u.id = offers.id_rco', array('rco' => 'u.username'))
	        //->joinLeft(array('c' => 'contacts'), 'c.id = offers.segnalato_da', array('snome' => 'c.nome', 'scognome' => 'c.cognome'))
	        ->joinLeft(array('os' => 'offer_status'), 'os.id = offers.id_status', array('status' => 'os.name'))
			->where('offers.active = 1');

		
		if(!$count)
		{
				
	        if($sort)
	        {
	        	if(stripos($sort, '|'))
	        	{
	        		$sorts = explode('|', $sort);
	        		foreach($sorts as $sort)
	        		{
	        			$select->order($sort . ' ' . $dir);	
	        		}
	        	}
	        	else
	        	{
	            	$select->order($sort . ' ' . $dir);
	        	}
	        }
	        else
	        {
	            $select->order('year ASC');
	            $select->order('id_offer ASC');
	            $select->order('revision ASC');
	        }
	    
		}
    	else 
		{
			$select->order('offers.date_modified desc');
			$select->order('offers.date_created desc');
			$select->limit($count, 0);
		}
		
        if(is_array($search) && !empty($search))
        {
            foreach($search as $k => $s)
            {
                if($k == 'telephones' && $s != '')
                {
                    $select->where('number like ' . $db->quote('%' . $s . '%'));
                }
                else if($k == 'mails' && $s != '')
                {
                    $select->where('mail like ' . $db->quote('%' . $s . '%'));
                }
                else if($k == 'internals' && $s != '')
                {
                    $select->where('abbr like ' . $db->quote('%' . $s . '%'));
                }
                else
                if($s != '' && $k != 'page' && $k != 'format' && $k != 'perpage' && $k != 'sdl' && $k != 'sfl' && $k != '_s' && $k != '_d')
                {
                	$fields = array($k);
                	if(stripos($k, '|'))
                	{
                		$fields = explode('|', $k);
                	}
                    
                	foreach($fields as $f)
                	{
                		// todo: ci vuole l'or nel caso siano un array                	
                    	$select->where($f . ' like ' . $db->quote('%' . $s . '%'));
                	}
                }
            }
        }
        else if(is_string($search))
        {
        	$select->where($search);
        }

        $offers = $db->fetchAll($select);

        return $offers;
        
        $ret = array();
        foreach($offers as $offer)
        {
        	if(!array_key_exists($offer['id_offer'], $ret))
        	{
        		$ret[$offer['id_offer']] = $offer;
        		$ret[$offer['id_offer']]['reviovions'] = array($offer['revision']);
        	} 
        	else  
        	{
        		$revs = $ret[$offer['id_offer']]['reviovions'];
        		if($offer['revision'] > $ret[$offer['id_offer']]['revision'])
        		{
        			$ret[$offer['id_offer']] = $offer;
        		}
        		$revs[] = $offer['revision'];
        		$ret[$offer['id_offer']]['reviovions'] = $revs;
        	}
        }
        
        return $ret;
    }
    
    public function makeOrder($id_offer, $offer_new_status_id, $id_dtg)
    {
	    //$this->setStatus($id_offer, $offer_new_status_id);
        
        $ordersModel = new Model_OrdersMapper();
        
        $result = $ordersModel->makeOrderFromOffer($id_offer, $id_dtg);

        $companiesModel = new Model_CompaniesMapper();

        $db = $this->_getDbAdapter();
        $select = $db->select();
        
        $select->from('offers', 'id_company')
        	->where('id = ?', $id_offer);
        
		$id_company = $db->fetchOne($select);
		
        // TODO: id STATO CLIENTE HARDCODED
        $companiesModel->setStatus($id_company, '12');
        
        $db->insert('private_messages', 
            array(
                'id_sender' => 0, 
                'id_receiver' => $id_dtg,
                'date_sent' => new Zend_Db_Expr('now()'),
                'subject' => 'nuova commessa',
                'text' => 'Ti &egrave; stata assegnata una nuova <a href="/orders/detail/id/' . $result . '" title="apri la commessa">commessa</a>.'
            )
        );
        
        return $result;
    }
}
