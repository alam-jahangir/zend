<?php
namespace Application\Model;
use Zend\Mail;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;
 
class Email
{
    public static function send($htmlBody = '', $textBody = '', $subject = '', $from_name = '', $from = '', $to = '', $cc = '', $bcc = '') {
        if (!$from || !$to || !$subject)
            return false;
            
        $htmlPart = new MimePart($htmlBody);
        $htmlPart->type = "text/html";
     
        $textPart = new MimePart($textBody);
        $textPart->type = "text/plain";
     
        //$image = new MimePart(fopen($pathToImage, 'r'));
        //$image->type = "image/jpeg";

        $body = new MimeMessage();
        $body->setParts(array($textPart, $htmlPart));
        //$body->setParts(array($textPart, $htmlPart, $image));
     
        $message = new Mail\Message();
        
        //$mail->setFrom('Freeaqingme@example.org', 'Dolf');
        //$mail->addTo('matthew@example.com', 'Matthew');
        if ($bcc)
            $message->addCc($bcc);
        if ($cc)
            $message->addBcc($cc);
        
        //$message->addReplyTo("matthew@weierophinney.net", "Matthew");
        
        $message->setFrom($from, $from_name);
        $message->addTo($to);
        $message->setSubject($subject);
        
        //->setTo($email)
        //->setReplyTo($replyTo)
        
        $message->setEncoding("UTF-8");
        $message->setBody($body);
        $message->getHeaders()->get('content-type')->setType('multipart/alternative');
        
        /*
        echo $message->toString();
        foreach ($message->from() as $address) {
            $address = $message->getSender();
            printf("%s: %s\n", $address->getEmail(), $address->getName());
        }
        */
        try {
            $transport = new Mail\Transport\Sendmail();
            $transport->send($message);
            return true;  
        } catch (Exception $ex) {
            return false;
        }
        
    }  
} 
