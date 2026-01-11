<?php

class Model_OrdersMapper
{
    public function makeOrderFromOffer($id_offer, $id_dtg)
    {
        $ordersModel = new Model_Orders();
        
        $data = array(
            'id_offer' => $id_offer,
        	'id_dtg' => $id_dtg
            //'id_status' => 1
        );
        
        return $ordersModel->save($data);
    }
    
	public function getLastOrders($number)
	{
		return $this->getList(null, null, null, $number);
	}
    
    /**
    * Close the given moment. Checks if all the moment for the relative offers
    * are close and then change the order status to "closed"
    * 
    * @param mixed $data
    * @return bool|int false -> errori nella chiusura
     *                 2     -> fase chiusa
     *                 3     -> fase e commessa chiusa
    */
    public function closeFase($data)
    {
		$db = $this->_getDbAdapter();

        $date_done = implode('-', array_reverse(explode('/', $data['date_done'])));

		$dt = array(
			'date_done' => $date_done,
			'done' => 1
		);

       // $moment = $db->fetchRow('select * from moments where moment_id = ' . $db->quote('id_moment'));

//        if($moment['importo'] != '' && $moment['importo'] != 0)
        {
           // $dt['real_importo'] = $moment['importo'];
        }
        //else
        {
            
        }
        
        $db->beginTransaction();
        
        try
        {
            $to_return = 3;
            
		    //$res = $db->update('moments', $dt, 'moment_id = ' . $db->quote($data['id_moment']));
		    $res = $db->update('moments', $dt, 'moment_id = ' . $db->quote($data['moment_id']));

            // check to see if all moments for this offer are closed
            $momentRepo = Maco_Model_Repository_Factory::getRepository('moment');
            $moment = $momentRepo->findWithDependencies($data['moment_id']);
            //$moment = $momentRepo->findWithDependencies($data['id_moment']);
            
            $close_order = true;
            $last_date = '0000-00-00';
            foreach($moment->order->offer->moments as $mom)
            {
                //if($mom->date_done == '' || $mom->date_done == '0000-00-00')
                if($mom->done == 0)
                {
                    $to_return = 2;
                    $close_order = false;
                    break;
                }
                else
                {
                    if($mom->date_done > $last_date)
                    {
                        $last_date = $mom->date_done;
                    }
                }
            }

            // messaggio al DTG
            $id_dtg = $moment->order->id_dtg;
            if($id_dtg)
            {
                $message_repo = Maco_Model_Repository_Factory::getRepository('message');
                $message_title = 'Fase di lavorazione "' . $moment->tipologia . '" chiusa per la commessa ' . $moment->order->code_order;
                $cf = Zend_Controller_Front::getInstance();
                $base_url = /* $cf->getRequest()->getScheme()
                    . '://'
                    . $cf->getRequest()->getHttpHost()
                    . */ $cf->getBaseUrl();
                $message_body = 'La fase di lavorazione <b>' . $moment->tipologia . '</b> per la commessa '
                                . '<a href="' . $base_url . '/orders/detail/id/' . $moment->order->order_id .'"><b>'
                                . $moment->order->code_order . '</b></a> &egrave; stata chiusa.<br />';
                $message_repo->send($id_dtg, $message_title, $message_body, Model_Message_Types::INFO);
            }


            if($close_order)
            {
                $db->update(
                    'orders', 
                    array('id_status' => 3, 'date_completed' => $last_date),
                    'order_id = ' . $moment->order->order_id
                );

                // messaggio al DTG e al/ai RAM
                sleep(1);
                $message_repo = Maco_Model_Repository_Factory::getRepository('message');
                $message_title = 'Commessa Completata: ' . $moment->order->code_order;
                $cf = Zend_Controller_Front::getInstance();
                $base_url = /* $cf->getRequest()->getScheme()
                    . '://'
                    . $cf->getRequest()->getHttpHost()
                    . */ $cf->getBaseUrl();
                $message_body = 'Le commessa '
                                . '<a href="' . $base_url . '/orders/detail/id/' . $moment->order->order_id .'"><b>'
                                . $moment->order->code_order . '</b></a> &egrave; stata completata.<br />';
                $id_dtg = $moment->order->id_dtg;
                // TODO: RAM
                // 1. repository user
                $users_repo = Maco_Model_Repository_Factory::getRepository('user');
                $rams = $users_repo->getUsersOfType('RAM');
                $receivers = array();
                foreach($rams as $user_id => $ram)
                {
                    $receivers[] = $user_id;
                }
                if($id_dtg && ! in_array($id_dtg, $receivers))
                {
                    $message_repo->send($id_dtg, $message_title, $message_body, Model_Message_Types::INFO);
                    //$receivers[] = $id_dtg;
                }
                $message_title = 'Gestire Commessa Completata in Amministrazione: ' . $moment->order->code_order;
                $message_repo->send($receivers, $message_title, $message_body, Model_Message_Types::TODO, null, $moment->order->order_id . '-RAM');
            }


            $db->commit();
            return $to_return;
        }
        catch(Exception $e)
        {
            $db->rollBack();
            return false;
        }   
    }
    
    public function getList($sort = null, $dir = 'ASC', $search = array(), $count = null)
    {
        $db = $this->_getDbAdapter();

        $select = $db->select();
            
        $select->from('orders', array('id', 'or_year' => 'year'))
            ->joinLeft('offers', 'offers.id = orders.id_offer', array('id_ord' => 'id', 'id_offer', 'revision', 'of_year' => 'year', 'date_offer', 'of_internal' => 'internal'))
            ->joinLeft(array('c1' => 'companies'), 'c1.id = offers.id_company', array('cliente' => 'ragione_sociale'))
            ->joinLeft(array('c2' => 'companies'), 'c2.id = offers.id_partner', array('partner' => 'ragione_sociale'))
            ->joinLeft(array('s' => 'services'), 's.id = offers.id_service', array('service' => 's.name'))
            ->joinLeft(array('ss' => 'subservices'), 'ss.id = offers.id_subservice', array('subservice' => 'ss.name', 'subservicecode' => 'ss.cod'))
            ->joinLeft(array('u2' => 'users'), 'u2.id = orders.id_dtg', array('dtg' => 'u2.username'))
            //->joinLeft(array('c' => 'contacts'), 'c.id = offers.segnalato_da', array('snome' => 'c.nome', 'scognome' => 'c.cognome'))
            ->joinLeft(array('ors' => 'order_status'), 'ors.id = orders.id_status', array('status' => 'ors.name'));

            
		if(!$count)
		{            
	        if($sort)
	        {
	            $select->order($sort . ' ' . $dir);
	        }
	        else
	        {
                $select->order('of_internal ASC');
	            $select->order('or_year ASC');
	            $select->order('id ASC');
	        }
		}
		else
		{
			$select->order('orders.date_modified desc');
			$select->order('orders.date_created desc');
			$select->limit($count, 0);
		}
	       
        if(is_array($search) && !empty($search))
        {
            foreach($search as $k => $s)
            {
                if($s != '' && $k != 'page' && $k != 'format' && $k != 'perpage' && $k != 'sdl' && $k != 'sfl' && $k != '_s' && $k != '_d')
                {
                    $k = str_replace('|', '.', $k);
                    $select->where($k . ' like ' . $db->quote('%' . $s . '%'));
                }
			}
		}
		else if(is_string($search))
		{
			$select->where($search);
		}
        
        $orders = $db->fetchAll($select);

        return $orders;
    }
   
    public function savePianificazione($data)
    {
        $id = $data['id'];
        
        if(!$id)
        {
            return false;
        }
        
        $db = $this->_getDbAdapter();
        
        $db->beginTransaction();
        
        try
        {
	        
	        $cdata = array(
	        //    'date_assegnazione' => $data['date_assegnazione'],
	            'date_chiusura_richiesta' => $data['date_chiusura_richiesta'],
	            //'n_incontri' => $data['n_incontri'],
	            //'n_ore_studio' => $data['n_ore_studio'],
	            'note_pianificazione' => $data['note_pianificazione'],
	            //'valore_g_uomo' => $data['valore_g_uomo'],
	        );

            $code_order = $db->fetchOne('select code_order from orders where order_id = ' . $id);

	        $filter = new Zend_Filter_LocalizedToNormalized();

	        $date = $filter->filter($cdata['date_chiusura_richiesta']);
            if(isset($date['year']) && isset($date['month']) && isset($date['day']))
            {
                $cdata['date_chiusura_richiesta'] = $date['year'] . '-' . $date['month'] . '-' . $date['day'];
            }
	        
	        $db->update('orders', $cdata, array('order_id = ?' => $id));
	        
	        $dbHelper = new Maco_Db_Helper($db);            
	
            $util = new Maco_Input_Utils();
            
            $inputNamePrefix = '';
            
            // TODO: id_rco to rco
            $partials = $util->formatDataForMultipleFields(array(
            	'rco', 'note'
            	), $inputNamePrefix. 'rcos_', $data);
            
            $rcos = $db->fetchAll('select * from orders_rcos where id_order = ?', $id);

            $createRaliDate = empty($rcos);

            $select = $db->select();

            
            // prendiamo i vecchi rc per prevenire un nuovo messaggio
            $removed = $db->fetchAssoc(
                $select->from('orders_rcos', array('rco', 'note'))
                       ->where('id_order', $id)
            );

            $message_repo = Maco_Model_Repository_Factory::getRepository('message');

            $uid = $id . '-RC-WORK';
            $message_repo->deleteByUid($uid);

            $dbHelper->removeLinkNN('orders_rcos', array('field' => 'id_order', 'value' => $id));

            $new_receivers = $note_changed = array();

            foreach($partials as $k => $partial)
            {
            	if($partial['rco'] != '')
            	{
                    if($createRaliDate)
                    {
                        $cdata = array(
                            'rali_date' => date('Y-m-d'),
                            'id_status' => 2
                        );
                        $db->update('orders', $cdata, array('order_id = ?' => $id));
                        $createRaliDate = false;
                    }

	            	$partial['date_assigned'] = date('Y-m-d');

	            	foreach($rcos as $r)
	            	{
	            		if($partial['rco'] == $r['rco'])
	            		{
	            			$partial['date_assigned'] = $r['date_assigned'];
	            		}
	            	}
	            	
	            	$dbHelper->linkNN('orders_rcos', 
	            		array('field' => 'id_order', 'value' => $id), 
	            		array('field' => 'rco', 'value' => $partial['rco']),
	            		array('date_assigned' => $partial['date_assigned'], 'note' => $partial['note'], 'index' => $k + 1));
                    $username = explode(' - ', $partial['rco']);
                    $username = $username[0];
                    $id_user_rco = $db->fetchOne('select user_id from users where username = ' . $db->quote($username));
                    if($id_user_rco)
                    {
                        $company_data = $db->fetchRow('select company_id, ragione_sociale from companies,orders,offers where orders.order_id = ' . $id . ' and offers.offer_id = orders.id_offer and company_id = offers.id_company');
                        $message_title = 'Commessa da lavorare: ' . $code_order;
                        $cf = Zend_Controller_Front::getInstance();
                        $base_url = /* $cf->getRequest()->getScheme()
                    . '://'
                    . $cf->getRequest()->getHttpHost()
                    . */ $cf->getBaseUrl();
                        $message_body = 'Sei stato assegnato come responsabile per la commessa ' . '<a href="' . $base_url . '/orders/detail/id/' . $id .'"><b>'
                                        . $code_order . '</b></a>';/* relativa all\'azienda <a href="'
                                . $base_url . '/companies/detail/id/' . $company_data['company_id']
                                . '"><b>' . $company_data['ragione_sociale'] . '</b></a>.<br />';*/
                        $message_repo->send($id_user_rco, $message_title, $message_body, Model_Message_Types::TODO, null, $id . '-RC-WORK', true);
                    }
            	}

                // TODO: rimuovere DaFare a DTG
                $uid = $id . '-DTG-PLAN';
                $toRemoveID = $db->fetchOne('select id_dtg from orders where order_id = ' . $id);
                if($toRemoveID)
                {
                    $message_repo->deleteByToAndUid($toRemoveID, $uid);
                }
            }
	            
			$db->commit();
			return true;
        }
        catch(Exception $e)
        {
        	$db->rollBack();
        	return false;
        }   
    }
    
    public function saveConsuntivo($data)
    {
        $id = $data['id'];
        
        if(!$id)
        {
            return false;
        }
        
        $db = $this->_getDbAdapter();
        
        $db->beginTransaction();
        
        try
        {
	        
	        $cdata = array(
	        //    'date_assegnazione' => $data['date_assegnazione'],
	            'note_consuntivo' => $data['note_consuntivo'],
	            'ente' => $data['ente'],
	        );
            if($data['sal'] != '')
            {
                $cdata['sal'] = $data['sal'];
            }
	        
	        $db->update('orders', $cdata, array('order_id = ?' => $id));
	            
			$db->commit();
			return true;
        }
        catch(Exception $e)
        {
        	$db->rollBack();
        	return false;
        }   
    }

    public function saveCommessa($data)
    {
        $id = $data['id'];
        
        if(!$id)
        {
            return false;
        }
        
        $db = $this->_getDbAdapter();
        
        $db->beginTransaction();
        
        try
        {
            
            $cdata = array(
            //    'date_assegnazione' => $data['date_assegnazione'],
                'note' => $data['note'],
            );
            
            $db->update('orders', $cdata, array('order_id = ?' => $id));
                
            $db->commit();
            return true;
        }
        catch(Exception $e)
        {
            $db->rollBack();
            return false;
        }   
    }

    /**
     * Aggiorna i dati consuntivi di un momento di lavorazione
     *
     * @param $data
     * @return bool|int false -> in caso di errore.
     *                  1     -> dati consuntivi aggiornati
     *                  2     -> dati consuntivi aggiornati e fase chiusa
     *                  3     -> dati consuntivi aggiornati, fase e commessa chiusa
     */
    public function saveConsuntivoPerMoment($data)
    {
        $id = $data['moment_id'];

        if(!$id)
        {
            return false;
        }

        $db = $this->_getDbAdapter();

        $db->beginTransaction();

        try
        {
            $c_ore_studio = Maco_Utils_Time::toValue($data['c_ore_studio_hour'], $data['c_ore_studio_minute']);
            $c_ore_azienda = Maco_Utils_Time::toValue($data['c_ore_azienda_hour'], $data['c_ore_azienda_minute']);
            $c_ore_certificazione = Maco_Utils_Time::toValue($data['c_ore_certificazione_hour'], $data['c_ore_certificazione_minute']);
            $c_ore_viaggio = Maco_Utils_Time::toValue($data['c_ore_viaggio_hour'], $data['c_ore_viaggio_minute']);

            $moment_repo = Maco_Model_Repository_Factory::getRepository('moment');
            $moment = $moment_repo->find($data['moment_id']);
            
            $valore_g_uomo = ($moment->p_valore_g_uomo) ?: 0;
            
            $val1 = ($c_ore_studio + $c_ore_azienda) * $valore_g_uomo / 8;
            $val2 = $c_ore_certificazione * $valore_g_uomo / 8;
            $val3 = (int)$data['c_n_km'] * (int)$data['c_costo_km'];
            $val4 = $data['c_pl_importo'];
            $tot = $val1 + $val2 + $val3 + $val4;

	        $cdata = array(
	        //    'date_assegnazione' => $data['date_assegnazione'],
	            'c_ore_studio' => $c_ore_studio,
	            'c_ore_azienda' => $c_ore_azienda,
	            'c_ore_certificazione' => $c_ore_certificazione,
	        	//'c_val_cert' => $data['c_val_cert'],
	        	'c_n_incontri' => $data['c_n_incontri'],
	            'c_ore_viaggio' => $c_ore_viaggio,
	            'c_n_km' => $data['c_n_km'],
	        	'c_costo_km' => $data['c_costo_km'],
	            'c_pl_importo' => $data['c_pl_importo'],
	            'c_pl_note' => $data['c_pl_note'],
	            'c_note' => $data['c_note'],
	            'importo_real' => $tot,
	        );

	        $db->update('moments', $cdata, array('moment_id = ?' => $id));

			$db->commit();

            $to_return = 1;

            if(isset($data['date_done']) && $data['date_done'] != '')
            {
                $to_return = $this->closeFase($data);
            }

			return $to_return;
        }
        catch(Exception $e)
        {
        	$db->rollBack();
        	return false;
        }
    }
    
    public function savePianificazionePerMoment($data)
    {
        $id = $data['moment_id'];

        if(!$id)
        {
            return false;
        }

        $db = $this->_getDbAdapter();

        $db->beginTransaction();

        try
        {
            $repo = Maco_Model_Repository_Factory::getRepository('order');
            $order = $repo->findWithDependenciesById($data['id_order']);

            $n_ore_studio = Maco_Utils_Time::toValue($data['n_ore_studio_hour'], $data['n_ore_studio_minute']);

            $pdata = array(
                'p_valore_g_uomo' => $data['valore_g_uomo'],
                'p_n_incontri' => $data['n_incontri'],
                'p_ore_studio' => $n_ore_studio,
                'expected_date' => Maco_Utils_DbDate::toDb($data['expected_date'])
            );

            $db->update('moments', $pdata, array('moment_id = ?' => $id));
            
            $tot_hm = 0;
            $tot_ni = 0;
            $tot_no = 0;
            $tot_hm_h = 0;
            foreach($order->offer->moments as $m)
            {
                if($m['moment_id'] == $id)
                {
                    $tot_ni += $data['n_incontri'];
                    $tot_no += $n_ore_studio;
                    $tot_hm += $data['valore_g_uomo'];
                    if($data['valore_g_uomo'] > 0)
                    {
                        $tot_hm_h += $m->getImportoScontato() / $data['valore_g_uomo'];
                    }
                }
                else
                {
                    $tot_ni += $m['p_n_incontri'];
                    $tot_no += $m['p_ore_studio'];
                    $tot_hm += $m['p_valore_g_uomo'];
                    if($m['p_valore_g_uomo'] > 0)
                    {
                        $tot_hm_h += $m->getImportoScontato() / $m['p_valore_g_uomo'];
                    }
                }
            }
            $order->n_incontri = $tot_ni;
            $order->n_ore_studio =  $tot_no;
            $count = count($order->offer->moments);
            $order->valore_g_uomo = $order->offer->total / $tot_hm_h;
            
            $order->setValidatorAndFilter(new Model_Order_Validator());
            
            if($order->isValid())
            {
                $order_id = $repo->save($order);
            }
            else
            {
                $db->rollBack();
                return false;
            }
            
            $db->commit();
            return true;
        }
        catch(Exception $e)
        {
            $db->rollBack();
            return false;
        }
    }
    
    
    public function getDetail($id)
    {
        $db = $this->_getDbAdapter();

        $select = $db->select();
        
        $select->from('orders')            
            ->joinLeft(array('u2' => 'users'), 'u2.id = orders.id_dtg', array('dtg' => 'u2.username'))
            ->joinLeft(array('of' => 'offers'), 'of.id = orders.id_offer', array('of_internal' => 'of.internal'))
            ->joinLeft('order_status', 'order_status.id = orders.id_status', array('status' => 'order_status.name'))
            ->where('orders.id = ?', $id);
        
        $order = $db->fetchRow($select);           
        
        $id_order = $order['id'];
        $id_offer = $order['id_offer'];
        
        
        $offersModel = new Model_OffersMapper();
        
        $order['offer'] = $offersModel->getDetail($id_offer);

        unset($select);
        
        $select = $db->select();
        
        // TODO: id_rco to rco
        $select->from('orders_rcos', array('rco', 'note', 'date_assigned'))
        	->order('index asc')
        	->where('id_order = ?', $id);
        
        $rcos = $db->fetchAll($select);

        // TODO: id_rco to rco
        if(empty($rcos))
        {
        	$rcos = array(
        		array(
        			'rco' => '',
        			'note' => '',
        		)
        	);
        }
        
        $order['rcos'] = $rcos;
        
        return $order;
    }

    public function getMomentDetail($id_moment, $id_order)
    {
        $db = $this->_getDbAdapter();
         
        $select = $db->select();

		$select->from('moments', array('*'))
			->where('moments.moment_id = ?', $id_moment);

		$moment = $db->fetchRow($select);

        $repo = Maco_Model_Repository_Factory::getRepository('order');

        $moment['order'] = $repo->findWithDependenciesById($id_order);

        return $moment;
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
