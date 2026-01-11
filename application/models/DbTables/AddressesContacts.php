<?php
/**
 * This is the DbTable class for the Utenti table.
 */
class Model_DbTables_AddressesContacts extends Zend_Db_Table_Abstract
{
	/**
	 * Table name
	 */
	protected $_name = 'addresses_contacts';

	/**
	 * Primary key
	 */
	protected $_primary = array('id_address', 'id_contact');

	protected $_referenceMap    = array(
        'Address' => array(
            'columns'           => array('id_address'),
            'refTableClass'     => 'Model_DbTables_Addresses',
            'refColumns'        => array('id')
	),
        'Contact' => array(
            'columns'           => array('id_contact'),
            'refTableClass'     => 'Model_DbTables_Contacts',
            'refColumns'        => array('id')
	)
	);
}