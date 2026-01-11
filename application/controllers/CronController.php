<?php
/**
 * Created by Marcello Stani.
 * Date: 01/07/13
 * Time: 17.33
 */

class CronController extends Zend_Controller_Action
{
    public function mailerAction()
    {
        $que = new Simplex_Email_Queuer();
        $que->handleQueue();

        echo 'done';exit;
    }
}