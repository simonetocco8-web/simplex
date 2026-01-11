<?php

class Simplex_Importer_Companies extends Simplex_Importer_Abstract
{
    protected $users = false;
    
    protected $_categorie_def = array(
        'table' => 'categories',
        'id' => 'category_id',
        'field' => 'name'
    );
    protected $categories = false;
    
    protected $_ea_def = array(
        'table' => 'ea',
        'id' => 'ea_id',
        'field' => 'name'
    );
    protected $ea = false;
    
    protected $_organici_medi_def = array(
        'table' => 'organici_medi',
        'id' => 'organico_medio_id',
        'field' => 'name'
    );
    protected $organici_medi = false;
    
    protected $_fatturati_def = array(
        'table' => 'fatturati',
        'id' => 'fatturato_id',
        'field' => 'name'
    );
    protected $fatturati = false;
    
    protected $_conosciuto_come_def = array(
        'table' => 'conosciuto_come',
        'id' => 'conosciuto_come_id',
        'field' => 'name'
    );
    protected $conosciuto_come = false;
    
    protected $_internals_def = array(
        'table' => 'internals',
        'id' => 'internal_id',
        'field' => 'abbr'
    );
    protected $internals = false;
    
    protected $_offices_def = array(
        'table' => 'offices',
        'id' => 'office_id',
        'field' => 'name'
    );
    protected $offices = false;
    
    protected $_linked_to_one_internal = false;
    
    protected $_cached_companies = array();
    
    protected $_cap_cached = false;
    
    protected $_statuses = false;
    
    public function import()
    {
        $company_repo = Maco_Model_Repository_Factory::getRepository('company');
        $address_repo = Maco_Model_Repository_Factory::getRepository('address');
        $mail_repo = Maco_Model_Repository_Factory::getRepository('mail');
        $website_repo = Maco_Model_Repository_Factory::getRepository('website');
        $contact_repo = Maco_Model_Repository_Factory::getRepository('contact');
        
        $user_importer = new Simplex_Importer_Users();
        $user_importer->setConfig($this->_config);
        $user_importer->setDb($this->_db);
        
        $objWorksheet = $this->_reader->getSheet(0);
        
        $partners_importer = new Simplex_Importer_Partners();
        $partners_offset = $partners_importer->get_offset();
        unset($partners_importer);
        
        foreach ($objWorksheet->getRowIterator() as $row)
        {
            $index = $row->getRowIndex();
            if ($index > 1)
            {
                $id = $this->_getValue($objWorksheet, 'A', $index);
                if ($id == '')
                {
                    // end
                    break;
                }
                
                $partita_iva = $this->_getValue($objWorksheet, 'S', $index);
                
                if(strlen($partita_iva) != 11)
                {
                    // partita iva non valida. Generare una partita iva fittizia
                    $progr = $this->_config->piva_progr + 1;
                    $partita_iva = 'XXX' . str_pad($progr, 8, 0, STR_PAD_LEFT);
                    $this->_config->piva_progr = $progr;
                }
                
                $status = $this->_getValue($objWorksheet, 'K', $index);
                
                $company = $company_repo->getNewCompany();
                $company->setValidatorAndFilter(new Model_Company_Validator());
                
                $company->company_id = $id;
                $company->deleted = ($status == 'escluso') ? 1 : 0;
                $company->ragione_sociale = $this->_getValue($objWorksheet, 'L', $index);
                $company->cf = $this->_getValue($objWorksheet, 'R', $index);
                $company->partita_iva = $partita_iva;
                $company->iban = $this->_getValue($objWorksheet, 'BF', $index);
                $company->status = $this->_getStatusIdByValue($status);
                $company->note = $this->_getValue($objWorksheet, 'U', $index);
                $company->prodotti = $this->_getValue($objWorksheet, 'AD', $index);
                
                // RCO
                $company->rco = $user_importer->getUserId(strtolower($this->_getValue($objWorksheet, 'AG', $index)));
                
                // segnalato_da
                $company->segnalato_da = $this->_getValue($objWorksheet, 'AH', $index);
                
                $company->categoria = $this->_getIdByValue($this->_categorie_def, ucfirst($this->_getValue($objWorksheet, 'AB', $index)));
                $company->ea = $this->_getIdByValue($this->_ea_def, ucfirst($this->_getValue($objWorksheet, 'AC', $index)));
                $company->organico_medio = $this->_getIdByValue($this->_organici_medi_def, ucfirst($this->_getValue($objWorksheet, 'BK', $index)));
                $company->fatturato = $this->_getIdByValue($this->_fatturati_def, $this->_getValue($objWorksheet, 'AI', $index));
                $company->conosciuto_come = $this->_getIdByValue($this->_conosciuto_come_def, ucfirst($this->_getValue($objWorksheet, 'AJ', $index)));
                $company->is_cliente = ($status == 'cliente') ? 2 : 1;
                $company->is_fornitore = 0;
                $company->is_partner = 0;
                $company->is_promotore = 0;
                
                $id_promotore = (int) $this->_getValue($objWorksheet, 'AR', $index);
                if($id_promotore)
                {
                    $company->id_promotore = $id_promotore + $partners_offset;
                }
                
                if($company->isValid())
                {
                    if(array_key_exists($company->company_id, $this->_cached_companies))
                    {
                        // update
                        $company_repo->save($company);
                    }
                    else
                    {
                        // insert
                        $company_repo->saveWithId($company);
                        
                        $this->_cached_companies[$company->company_id] = array(
                            'ragione_sociale' => $company->ragione_sociale,
                            'partita_iva' => $company->partita_iva,
                        );
                    }
                }
                else
                {
                    -dd($company->getInvalidMessages());
                }
                
                // internals
                $this->_linkInternals($objWorksheet, $index, $company->company_id);
                
                // indirizzo
                // prima eliminiamo ogni possibile collegamento
                $this->_db->delete('addresses', 'id_company = ' . $company->company_id);
                
                // ora li carico
                $address = $address_repo->getNewAddress();
                $address->setValidatorAndFilter(new Model_Address_Validator());
                $address->via = $this->_getValue($objWorksheet, 'M', $index);
                $address->numero = $this->_getValue($objWorksheet, 'N', $index);
                $address->cap = $this->_getValue($objWorksheet, 'O', $index);
                $address->localita = $this->_getValue($objWorksheet, 'P', $index);
                
                if($address->cap != '' || $address->localita != '')
                {
                    // ok, procediamo
                    $provincia_regione = $this->_findProvinciaRegione($address->cap, $address->localita);
                    if($provincia_regione)
                    {
                        $address->provincia = $provincia_regione['provincia'];
                        $address->regione = $provincia_regione['regione'];
                        
                        $address->id_company = $company->company_id;
                        
                        if($address->isValid())
                        {
                            $address_repo->save($address);
                        }
                        else
                        {
                            //-dd($address->getInvalidMessages());
                        }
                    }
                }
                
                // tel1 - tel2 - fax - cellulare | v w x y
                // prima eliminiamo ogni possibile collegamento
                $this->_db->delete('telephones', 'id_company = ' . $company->company_id);
                $this->_loadTelephone($objWorksheet, 'V', $index, 'tel 1', $company->company_id);
                $this->_loadTelephone($objWorksheet, 'W', $index, 'tel 2', $company->company_id);
                $this->_loadTelephone($objWorksheet, 'X', $index, 'fax', $company->company_id);
                $this->_loadTelephone($objWorksheet, 'Y', $index, 'cellulare', $company->company_id);
            
                // mail | z
                // prima eliminiamo ogni possibile collegamento
                $this->_db->delete('mails', 'id_company = ' . $company->company_id);
                $mail_value = str_replace(' ', '', $this->_getValue($objWorksheet, 'Z', $index));
                if($mail_value != '')
                {
                    $mail = $mail_repo->getNewMail();
                    $mail->setValidatorAndFilter(new Model_Mail_Validator());
                    $mail->mail = $mail_value;
                    $mail->id_company = $company->company_id;
                    if($mail->isValid())
                    {
                        $mail_repo->save($mail);
                    }        
                    else
                    {
                       //echo '<br />' . $index . '<br />';
                       //var_dump($mail->getInvalidMessages());
                    }
                }
                
                // web | AA
                // prima eliminiamo ogni possibile collegamento
                $this->_db->delete('websites', 'id_company = ' . $company->company_id);
                $url = $this->_getValue($objWorksheet, 'AA', $index);
                if($url != '')
                {
                    $web = $website_repo->getNewWebsite();
                    $web->setValidatorAndFilter(new Model_Website_Validator());
                    $web->url = $url;
                    $web->id_company = $company->company_id;
                    if($web->isValid())
                    {
                        $website_repo->save($web);
                    }        
                    else
                    {
                        //-dd($web->getInvalidMessages());
                    }
                }
                
                // contatto - rappresentante legale
                // prima eliminiamo ogni possibile collegamento
                $this->_db->delete('contacts', 'id_company = ' . $company->company_id);
                
                // create the dummy contact
                $contact_name = $this->_getValue($objWorksheet, 'T', $index);
                if($contact_name)
                {
                    $this->_db->insert('contacts', array(
                        'created_by' => 1,
                        'date_created' => new Zend_Db_Expr('now()'),
                        'deleted' => 0,
                        'cognome' => $contact_name,
                        'id_company' => $company->company_id
                    ));
                }
                
                // contatti - referenti
                $this->_addReferenti($objWorksheet, $index, $company->company_id);
            }
            
        }   
    }
    
    protected function _addReferenti(&$objWorksheet, $row, $comany_id)
    {
        $this->_addReferente($objWorksheet, 'AW', 'AZ', 'BC', $row, $comany_id);
        $this->_addReferente($objWorksheet, 'AX', 'BA', 'BD', $row, $comany_id);
        $this->_addReferente($objWorksheet, 'Ay', 'BB', 'BE', $row, $comany_id);
    }
    
    protected function _addReferente(&$objWorksheet, $colr, $colt, $colm, $row, $company_id)
    {
        $cognome = $this->_getValue($objWorksheet, $colr, $row);
        if($cognome)
        {
            $this->_db->insert('contacts', array(
                'created_by' => 1,
                'date_created' => new Zend_Db_Expr('now()'),
                'deleted' => 0,
                'cognome' => $cognome,
                'id_company' => $company_id
            ));
            
            $contact_id = $this->_db->lastInsertId();
            
            $number = $this->_getValue($objWorksheet, $colt, $row);
            if($number)
            {
                $telephone_repo = Maco_Model_Repository_Factory::getRepository('telephone');
        
                $tel = $telephone_repo->getNewTelephone();
                $tel->setValidatorAndFilter(new Model_Telephone_Validator());
                $tel->number = $number;
                $tel->id_contact = $contact_id;
                if($tel->isValid())
                {
                    $telephone_repo->save($tel);
                }        
                else
                {
                    -dd($tel->getInvalidMessages());
                }
            }
            
            $mail_value = str_replace(' ', '', $this->_getValue($objWorksheet, $colm, $row));
            if($mail_value)
            {
                $mail_repo = Maco_Model_Repository_Factory::getRepository('mail');
                $mail = $mail_repo->getNewMail();
                $mail->setValidatorAndFilter(new Model_Mail_Validator());
                $mail->mail = $mail_value;
                $mail->id_contact = $contact_id;
                if($mail->isValid())
                {
                    $mail_repo->save($mail);
                }        
                else
                {
                   //echo '<br />' . $index . '<br />';
                   //var_dump($mail->getInvalidMessages());
                }
            }
        }
    }
    
    protected function _loadTelephone($objWorksheet, $col, $row, $description, $id_company)
    {
        $number = $this->_getValue($objWorksheet, $col, $row);
        if($number == '') return;
        
        $telephone_repo = Maco_Model_Repository_Factory::getRepository('telephone');
        
        $tel = $telephone_repo->getNewTelephone();
        $tel->setValidatorAndFilter(new Model_Telephone_Validator());
        $tel->number = $number;
        $tel->description = $description;
        $tel->id_company = $id_company;
        if($tel->isValid())
        {
            $telephone_repo->save($tel);
        }        
        else
        {
            -dd($tel->getInvalidMessages());
        }
    }
    
    protected function _findProvinciaRegione($cap, $localita)
    {
        if(!$this->_cap_cached)
        {
            $select = $this->_db->select();
            $select->from('comuni', array('localita' => 'comuni.nome', 'cap'))
                ->joinLeft('province', 'provincia_id = comuni.id_provincia', array('provincia' => 'province.nome'))
                ->joinLeft('regioni', 'regione_id = province.id_regione', array('regione' => 'regioni.nome'));
            
            $vals = $this->_db->fetchAll($select);
            
            $this->_cap_cached = array();
            foreach($vals as $v)
            {
                $this->_cap_cached[$v['cap']] = array(
                    'localita' => $v['localita'],
                    'provincia' => $v['provincia'],
                    'regione' => $v['regione'],
                    'cap' => $v['cap'],
                );
            }
        }
        
        if($cap != '' && isset($this->_cap_cached[$cap]))
        {
            return $this->_cap_cached[$cap];
        }
        
        if($localita != '')
        {
            foreach($this->_cap_cached as $l)
            {
                if($l['localita'] == $localita)
                {
                    return $l;
                }
            }
        }
        
        return false;
    }
    
    protected function _linkInternals($objWorksheet, $index, $company_id)
    {
        // prima eliminiamo ogni possibile collegamento
        $this->_db->delete('companies_internals', 'id_company = ' . $company_id);
        
        // ora carichiamo i collegamenti
        $this->_linked_to_one_internal = false;
        $this->_linkInternal($objWorksheet, 'D', $index, $company_id);
        $this->_linkInternal($objWorksheet, 'E', $index, $company_id);
        $this->_linkInternal($objWorksheet, 'F', $index, $company_id);
        $this->_linkInternal($objWorksheet, 'G', $index, $company_id);
        $this->_linkInternal($objWorksheet, 'H', $index, $company_id);
        $this->_linkInternal($objWorksheet, 'I', $index, $company_id);
        
        //$this->_linkInternal($objWorksheet, 'C', $index, $company_id, !$this->_linked_to_one_internal);
        // tutte a excellentia
        $this->_linkInternal($objWorksheet, 'C', $index, $company_id, true);
    }
    
    protected function _linkInternal($objWorksheet, $col, $row, $company_id, $force = false)
    {
        $has_abbr = $this->_getValue($objWorksheet, $col, $row);
            
        if($has_abbr == '1' || $force)
        {
            $internals_cached = $this->_loadCached($this->_internals_def);
            $abbr = strtoupper($this->_getValue($objWorksheet, $col, 1));
            
            $key = array_search($abbr, $internals_cached);
            
            if(!$key)
            {
                $key = $this->_pushValue($this->_internals_def, $abbr);
            }
            
            //offices
            $id_office = NULL;
            $office = $this->_getValue($objWorksheet, 'BL', $row);
            if($office != '')
            {
                $office_cached = $this->_loadCached($this->_offices_def);
                $key_office = array_search($office, $office_cached);
                if(!$key_office)
                {
                    $key_office = $this->_pushOffice($office);
                }
                $id_office = $key_office;
            }
            
            $this->_db->insert('companies_internals', array(
                'id_company' => $company_id,
                'id_internal' => $key,
                'id_office' => $id_office
            ));
            
            $this->_linked_to_one_internal = true;
        }
    }
    
    protected function _pushOffice($value)
    {
        $this->_db->insert('offices', array(
            'created_by' => 1,
            'date_created' => new Zend_Db_Expr('now()'),
            'name' => $value,
            'id_internal' => 1
        ));
        
        $id = $this->_db->lastInsertId();
        
        unset($this->offices);
        return $id;
    }
    
    protected function _getStatusIdByValue($value)
    {                                                          
        $statuses = $this->_loadStatuses();
        $value = ucfirst($value);
        
        $key = array_search($value, $statuses);
        
        return ($key) ? $key : 2;
    }
    
    protected function _loadStatuses()
    {
        if(!$this->_statuses)
        {
            $this->_statuses = $this->_db->fetchPairs('select status_id, name from status');
        }
        return $this->_statuses;
    }
    
    protected function _loadCompanies()
    {
        $companies = $this->_db->fetchAll('select company_id, ragione_sociale, partita_iva from companies');
        foreach($companies as $company)
        {
            $this->_cached_companies[$company['company_id']] = array(
                'ragione_sociale' => $company['ragione_sociale'],
                'partita_iva' => $company['partita_iva'],
            );
        }
    }
}
