<?php
/**
 * Created by Marcello Stani.
 * User: marcello
 * Date: 8-ott-2010
 * Time: 15.02.56
 * To change this template use File | Settings | File Templates.
 */

class Model_Contact_Repository
{
    /**
     * Companies mysql mapper
     *
     * @var Model_Contact_Mapper
     */
    protected $_contactMapper;

    public function __construct()
    {
        $this->_contactMapper = new Model_Contact_Mapper();
    }

    public function getNewContact()
    {
        $new = new Model_Contact();
        $mailsRepo = Maco_Model_Repository_Factory::getRepository('mail');
        $telephonesRepo = Maco_Model_Repository_Factory::getRepository('telephone');
        $addressesRepo = Maco_Model_Repository_Factory::getRepository('address');
        $new->mails = array($mailsRepo->getNewMail());
        $new->telephones = array($telephonesRepo->getNewTelephone());
        $new->addresses = array($addressesRepo->getNewAddress());
        return $new;
    }

    public function find($id)
    {
        $item = $this->_contactMapper->find($id);
        if($item->id_contact_title)
        {
            $db = $this->_contactMapper->getDbAdapter();
            $item->contact_title = $db->fetchOne(
                'select name from contact_titles where contact_title_id = ?', 
                (int)$item->id_contact_title);
        }
        $mailsRepo = Maco_Model_Repository_Factory::getRepository('mail');
        $telephonesRepo = Maco_Model_Repository_Factory::getRepository('telephone');
        $addressesRepo = Maco_Model_Repository_Factory::getRepository('address');

        $item->mails = $mailsRepo->findByContact($id);
        
        if(count($item->mails) == 0) $item->mails = array($mailsRepo->getNewMail());
        $item->telephones = $telephonesRepo->findByContact($id);
        if(count($item->telephones) == 0) $item->telephones = array($telephonesRepo->getNewTelephone());
        $item->addresses = $addressesRepo->findByContact($id);
        if(count($item->addresses) == 0) $item->addresses = array($addressesRepo->getNewAddress());

        return $item;
    }

    public function delete($id)
    {
        $this->_contactMapper->delete($id);

        $db = $this->_contactMapper->getDbAdapter();

        $db->update('offers', array('id_company_contact' => null), 'id_company_contact = ' . $db->quote($id));
    }

    public function save($item)
    {
        return $this->_companyMapper->save($item);
    }

    public function saveFromData($data, $prefix = '')
    {
        $contact = new Model_Contact();
        $contact->setValidatorAndFilter(new Model_Contact_Validator());
        $contact->setData($data, $prefix);
        
        if($contact->isValid())
        {
            Maco_Model_TransactionManager::beginTransaction();

            try
            {
                $contact_id = (int) $this->_contactMapper->save($contact);

                $contact_edited = $this->find($contact_id);

                $utils = new Maco_Input_Utils();
                // la utils toglie il prefisso
                
                $telephoneRepo = Maco_Model_Repository_Factory::getRepository('telephone');
                $telephonesData = $utils->formatDataForMultipleFields(array('telephone_id', 'number', 'description'), $prefix . 'telephones_', $data);
                foreach($telephonesData as $telephone)
                {
                    $empty = implode('', $telephone);
                    if(!empty($empty))
                    {
                        $telephone['id_contact'] = $contact_id;
                        if(is_array($res = $telephoneRepo->saveFromData($telephone)))
                        {
                            Maco_Model_TransactionManager::rollback();
                            return $res;
                        }
                    }
                }
                foreach($contact_edited->telephones as $old)
                {
                    $in = false;
                    foreach($telephonesData as $telephone)
                    {
                        if($old->telephone_id == $telephone['telephone_id'])
                        {
                            $in = true;
                        }
                    }
                    if(!$in)
                    {
                        $telephoneRepo->delete($old->telephone_id);
                    }
                }
                unset($telephoneRepo, $telephonesData);

                $mailRepo = Maco_Model_Repository_Factory::getRepository('mail');
                $mailsData = $utils->formatDataForMultipleFields(array('mail_id', 'mail', 'description'), $prefix . 'mails_', $data);
                foreach($mailsData as $mail)
                {
                    $empty = implode('', $mail);
                    if(!empty($empty))
                    {
                        $mail['id_contact'] = $contact_id;
                        if(is_array($res = $mailRepo->saveFromData($mail)))
                        {
                            Maco_Model_TransactionManager::rollback();
                            return $res;
                        }
                    }
                }
                foreach($contact_edited->mails as $old)
                {
                    $in = false;
                    foreach($mailsData as $mail)
                    {
                        if($old->mail_id == $mail['mail_id'])
                        {
                            $in = true;
                        }
                    }
                    if(!$in)
                    {
                        $mailRepo->delete($old->mail_id);
                    }
                }
                unset($mailRepo, $mailsData);

                $addressRepo = Maco_Model_Repository_Factory::getRepository('address');
                $addressesData = $utils->formatDataForMultipleFields(array('address_id', 'via', 'numero', 'cap', 'localita', 'provincia', 'description'), $prefix . 'addresses_', $data);
                foreach($addressesData as $address)
                {
                    $empty = implode('', $address);
                    if(!empty($empty))
                    {
                        $address['id_contact'] = $contact_id;
                        if(is_array($res = $addressRepo->saveFromData($address)))
                        {
                            Maco_Model_TransactionManager::rollback();
                            return $res;
                        }
                    }
                }
                foreach($contact_edited->addresses as $old)
                {
                    $in = false;
                    foreach($addressesData as $address)
                    {
                        if($old->address_id == $address['address_id'])
                        {
                            $in = true;
                        }
                    }
                    if(!$in)
                    {
                        $addressRepo->delete($old->address_id);
                    }
                }
                unset($addressRepo, $addressesData);

                Maco_Model_TransactionManager::commit();
                return $contact_id;
            }
            catch(Exception $e)
            {
                Maco_Model_TransactionManager::rollback();
                return array($e->getMessage());
            }
        }
        else
        {
            return $contact->getInvalidMessages();
        }

        // fist the contact
        $contactRepo = Maco_Model_Repository_Factory::getRepository('contact');
        $res = $contactRepo->saveFromData($data, 'contacts_');
        if(is_array($res))
        {
            // no good
            return $res;
        }
    }

     public function getContacts($sort = 'cognome', $dir = 'ASC', $search = array(), $deleted = null, $per_page = NULL)
    {
        $db = $this->_contactMapper->getDbAdapter();

        $select = $db->select();

        $select->from('contacts', array('contact_id', 'nome', 'cognome', 'id_company', 'id_contact_title', 'description'))
            ->joinLeft('mails', 'mails.id_contact = contacts.contact_id', array('mail'))
            ->joinLeft('contact_titles', 'contacts.id_contact_title = contact_titles.contact_title_id', array('contact_title' => 'contact_titles.name'))
            ->joinLeft('telephones', 'telephones.id_contact = contacts.contact_id', array('number'));

        $select->order($sort . ' ' . $dir);

        if(isset($deleted))
        {
            $select->where('contacts.deleted = ?', $db->quote($deleted));
        }

        if(isset($search['id_company']) && $search['id_company'] != '')
        {
            $select->where('contacts.id_company = ' . $db->quote($search['id_company']));
        }

        if(isset($search['cognome']) && $search['cognome'] != '')
        {
            $select->where('contacts.cognome LIKE ' . $db->quote('%' . $search['cognome'] . '%'));
        }

        if($per_page !== NULL)
        {
            $values = Zend_Paginator::factory($select);
            $values->setItemCountPerPage($per_page);
            $values->setCurrentPageNumber(isset($search['page']) ? $search['page'] : 1);
        }
        else
        {
            $values = $db->fetchAll($select);
        }

        $contacts = array();
        foreach($values as $c)
        {
            if(!array_key_exists($c['contact_id'], $contacts))
            {
                $contacts[$c['contact_id']] = $c;
                unset($contacts[$c['contact_id']]['mail']);
                $contacts[$c['contact_id']]['mails'] = array();
                $contacts[$c['contact_id']]['telephones'] = array();
                //$contacts[$u['id']]['internals'] = '';
            }
            if(!in_array($c['mail'], $contacts[$c['contact_id']]['mails']))
            {
                $contacts[$c['contact_id']]['mails'][] = $c['mail'];
            }
            if(!in_array($c['number'], $contacts[$c['contact_id']]['telephones']))
            {
                $contacts[$c['contact_id']]['telephones'][] = $c['number'];
            }
        }

        return $contacts;
    }
}
