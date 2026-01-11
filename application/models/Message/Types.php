<?php
/**
 * Created by Marcello Stani.
 * User: Marcello
 * Date: 14/03/12
 * Time: 18.05
 */

class Model_Message_Types {

    const INFO  = 0;
    const TODO = 1;

    protected static $_status_names = array(
        self::INFO => 'Info',
        self::TODO => 'Da Fare',
    );
}