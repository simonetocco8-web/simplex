<?php
/**
 * Created by Marcello Stani.
 * Date: 01/07/13
 * Time: 13.07
 */

class Simplex_Email_Queuer{

    protected $queueName = 'emailQueue';
    protected $toRetrieve = 5;

    public function addToQueue($data)
    {
        // Get the queue adapter
        $queueAdapter = Zend_Registry::getInstance()->queueAdapter;

        // Get a queue we can smack around
        $options = array( 'name' => $this->queueName);
        $queue = new Zend_Queue( $queueAdapter, $options );

        // Create a silly email
        $email = new Simplex_Email_Container();
        $email->date = time();
        $email->from = $data['from'];
        $email->to = $data['to'];
        $email->subject = $data['subject'];
        $email->bodyHtml = $data['message'];

        $email->bodyText = strip_tags($data['message']);

        // serialize and shrink the POPO
        $message = base64_encode( gzcompress( serialize( $email ) ) );

        // Send it.
        $queue->send( $message );
    }

    public function handleQueue()
    {
        // Get the queue adapter
        $queueAdapter = Zend_Registry::getInstance()->queueAdapter;

        // Grab the email queue
        $options = array( 'name' => $this->queueName );
        $queue = new Zend_Queue( $queueAdapter, $options );

        // Read 2 messages.
        $messages = $queue->receive( $this->toRetrieve );

        $sender = new Simplex_Email_Sender();

        foreach( $messages as $message ) {

            try {
                // Decompose the email
                $email = unserialize( gzuncompress( base64_decode( $message->body ) ) );

                // see what happens when something goes snafu...
                //throw new Exception( "testing failure" );

                // ************************
                //
                // Do your magic here
                //
                // ************************
                $sender->send($email);

                // All's well, we can just delete the job
                $queue->deleteMessage( $message );

                echo sprintf(
                    "Sent email to %s (time: %s)<br/>",
                    $email->to,
                    new Zend_Date( $email->date )
                );

            } catch( Exception $ex ) {
                echo "Kaboom!: " . $ex->getMessage() . "<br/>";

                // At this point, the message is still
                // available in the DB but will not
                // be processed by future receive()'s

            }

        }
    }
}