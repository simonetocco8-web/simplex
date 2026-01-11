<?php
/**
 * Created by Marcello Stani.
 * Date: 01/07/13
 * Time: 13.11
 */

class Simplex_Email_Sender{

    public function send($email)
    {
        if(stripos($email->subject, 'nuovo impegno') !== FALSE)
        {
            // will not send tasks
            return;
        }

        // TODO: move to config
        $smtpServer = 'smtp.sendgrid.net';
        $username = 'excellentia';
        $password = 'smarzano';

        $config = array('ssl' => 'tls',
            'port' => '587',
            'auth' => 'login',
            'username' => $username,
            'password' => $password);

        $transport = new Zend_Mail_Transport_Smtp($smtpServer, $config);

        $mail = new Zend_Mail();

        $fromAddress = is_array($email->from) ? $email->from[0] : $email->from;
        $fromName = is_array($email->from) ? $email->from[1] : null;

        $toAddress = is_array($email->to) ? $email->to[0] : $email->to;
        $toName = is_array($email->to) ? $email->to[1] : null;

        $subject = $email->subject;

        if(APPLICATION_ENV == 'development')
        {
            $subject .= ' via ' . $toAddress;
            $toName .= ' via ' . $toAddress;
            $toAddress = 'marcellostani@gmail.com';
        }

        $mail->setFrom($fromAddress, $fromName);
        $mail->addTo($toAddress,$toName);
        // $mail->addTo('f.mantella@excellentia.it',$toName); // TODO: REMOVE
        $mail->setSubject($subject);

        if($email->bodyHtml)
        {
            $body = html_entity_decode($email->bodyHtml);

            $body = str_replace('href="', 'href="http://www.simplexcrm.it', $body);

            $mail->setBodyHtml($body);
        }

        if($email->bodyText)
        {
            $mail->setBodyText($email->bodyText);
        }

        $mail->send($transport);
    }
}