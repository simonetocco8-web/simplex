<?php
    
class Simplex_Importer_Partners extends Simplex_Importer_Companies {
    
    protected $_offset = 3743;
    
    protected $_internals = array(
        1 => 'EX',
        2 => 'EP',
        3 => 'AS',
        4 => 'EC',
        5 => 'NW',
        6 => 'HQ',
        7 => 'SS',
    );
    
    public function get_offset()
    {
        return $this->_offset;
    }
    
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
        
        $this->_buildInternals();
        
        $this->_loadCompanies();
        
        foreach ($objWorksheet->getRowIterator() as $row)
        {
            $index = $row->getRowIndex();
            if ($index > 1)
            {
                $id = $this->_getValue($objWorksheet, 'Q', $index) + $this->_offset;
                if ($id == '')
                {
                    // end
                    echo 'fine ' . $index . '<br />';
                    break;
                }
                $ragione_sociale = $this->_getValue($objWorksheet, 'B', $index);
                if($ragione_sociale == '')
                {
                    echo 'salto: ' . $id . '<br />';
                    continue;
                }

                $partita_iva = $this->_getValue($objWorksheet, 'W', $index);
                
                if(strlen($partita_iva) != 11)
                {
                    // partita iva non valida. Generare una partita iva fittizia
                    $progr = $this->_config->piva_progr + 1;
                    $partita_iva = 'XXX' . str_pad($progr, 8, 0, STR_PAD_LEFT);
                    $this->_config->piva_progr = $progr;
                }
                
                $status = $this->_getValue($objWorksheet, 'R', $index);
                
                $company = $company_repo->getNewCompany();
                $company->setValidatorAndFilter(new Model_Company_Validator());
                
                $company->company_id = $id;
                $company->deleted = 0;
                $company->ragione_sociale = $ragione_sociale;
                $company->cf = $this->_getValue($objWorksheet, 'V', $index);
                $company->partita_iva = $partita_iva;
                //$company->iban = $this->_getValue($objWorksheet, 'BF', $index);
                if($status != '')
                {
                    $company->status = $this->_getStatusIdByValue($status);
                }
                //$company->note = $this->_getValue($objWorksheet, 'U', $index);
                //$company->prodotti = $this->_getValue($objWorksheet, 'AD', $index);
                
                // RCO
                //$company->rco = $user_importer->getUserId(strtolower($this->_getValue($objWorksheet, 'AG', $index)));
                
                // segnalato_da
                $company->segnalato_da = $this->_getValue($objWorksheet, 'K', $index);
                
                $company->categoria = $this->_getIdByValue($this->_categorie_def, ucfirst($this->_getValue($objWorksheet, 'I', $index)));
                //$company->ea = $this->_getIdByValue($this->_ea_def, ucfirst($this->_getValue($objWorksheet, 'AC', $index)));
                //$company->organico_medio = $this->_getIdByValue($this->_organici_medi_def, ucfirst($this->_getValue($objWorksheet, 'BK', $index)));
                //$company->fatturato = $this->_getIdByValue($this->_fatturati_def, $this->_getValue($objWorksheet, 'AI', $index));
                //$company->conosciuto_come = $this->_getIdByValue($this->_conosciuto_come_def, ucfirst($this->_getValue($objWorksheet, 'AJ', $index)));
                $company->is_cliente = 0;
                $company->is_fornitore = 0;
                $company->is_partner = 0;
                $company->is_promotore = 1;
                
                $edit = false;
                
                if($company->isValid())
                {
                    if(array_key_exists($company->company_id, $this->_cached_companies))
                    {
                        // update
                        $edit = true;
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
                if( ! $edit )
                {
                    $this->_linkInternals($objWorksheet, $index, $company->company_id);
                }
                
                // indirizzo
                // prima eliminiamo ogni possibile collegamento
                $this->_db->delete('addresses', 'id_company = ' . $company->company_id);
                
                $address = $address_repo->getNewAddress();
                $address->setValidatorAndFilter(new Model_Address_Validator());
                $address->via = $this->_getValue($objWorksheet, 'C', $index);
                //$address->numero = $this->_getValue($objWorksheet, 'N', $index);
                $address->cap = $this->_getValue($objWorksheet, 'H', $index);
                $address->localita = $this->_getValue($objWorksheet, 'D', $index);
                
                if($address->cap != '' || $address->localita != '')
                {
                    // ok, procediamo
                    $provincia_regione = $this->_findProvinciaRegione($address->cap, $address->localita);
                    if($provincia_regione)
                    {
                        $address->provincia = $provincia_regione['provincia'];
                        $address->regione = $provincia_regione['regione'];
                        
                        if($address->cap == '')
                        {
                            $address->cap = $provincia_regione['cap'];
                        }
                        
                        $address->id_company = $company->company_id;
                        
                        if($address->isValid())
                        {
                            $address_repo->save($address);
                        }
                        else
                        {
                            -dd($address->getInvalidMessages());
                        }
                    }
                }
                
                // tel1 - tel2 - fax - cellulare | v w x y
                // prima eliminiamo ogni possibile collegamento
                $this->_db->delete('telephones', 'id_company = ' . $company->company_id);
                
                $this->_loadTelephone($objWorksheet, 'F', $index, 'telefono 1', $company->company_id);
                $this->_loadTelephone($objWorksheet, 'T', $index, 'telefono 2', $company->company_id);
                $this->_loadTelephone($objWorksheet, 'G', $index, 'fax', $company->company_id);
                $this->_loadTelephone($objWorksheet, 'S', $index, 'cellulare', $company->company_id);
            
                // mail | z
                // prima eliminiamo ogni possibile collegamento
                $this->_db->delete('mails', 'id_company = ' . $company->company_id);
                
                $mail_value = str_replace(' ', '', $this->_getValue($objWorksheet, 'X', $index));
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
                
                $url = $this->_getValue($objWorksheet, 'U', $index);
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
            }
            
        }   
    }
    
    
    
    protected function _buildInternals()
    {
        foreach($this->_internals as $id_internal => $abbr)
        {
            $this->_pushInternal($id_internal, $abbr);
        }
    }
    
    protected function _pushInternal($internal_id, $abbr)
    {
        $this->_db->insert('internals', array(
            'created_by' => 1,
            'date_created' => new Zend_Db_Expr('now()'),
            //'internal_id' => $internal_id,
            'abbr' => $abbr
        ));
        
        $id = $this->_db->lastInsertId();
        
        return $id;
    }
    
    protected function _linkInternals($objWorksheet, $index, $company_id)
    {
        foreach($this->_internals as $id_internal => $abbr)
        {
            $this->_db->insert('companies_internals', array(
                'id_company' => $company_id,
                'id_internal' => $id_internal
            ));
            
        }
    }
}