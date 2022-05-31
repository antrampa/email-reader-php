<?php

$sec = $_GET['sec'];
if($sec != "XXXXXXXXXX"){
 
    http_response_code(403);
    die('Forbidden');
}


// /https://www.techfry.com/php-tutorial/how-to-read-emails-using-php
// Open IMAP Stream: imap_open()
$mailbox = "{xxx.xxxxxxxx.xxx:993/imap/ssl}INBOX";
$username = "xxxxxxxxxxx"; 
$password = "xxxxxxxxxxx";
$inbox = imap_open($mailbox, $username, $password) or die('Cannot connect to email: ' . imap_last_error());


$emails = imap_search($inbox, 'ALL');

rsort($emails);

if($emails)
 {
     rsort($emails);
     foreach($emails as $msg_number) 
     {
       // Get email headers and body
        $header = imap_headerinfo($inbox, $msg_number);
        
        /*
        $header->date
        $header->subject
        $header->toaddress
        $header->to: Array of objects with properties: personal, adl, mailbox, and host
        $header->fromaddress
        $header->from: Array of objects with properties: personal, adl, mailbox, and host
        $header->reply_toaddress
        $header->reply_to: Array of objects with properties: personal, adl, mailbox, and host
        $header->Size: The message size
        $header->udate: Mail message date in Unix time
        */
        
        $from = $header->from;
         foreach ($from as $id => $object) 
         {
            $fromname = $object->personal;
            $fromaddress = $object->mailbox . "@" . $object->host;
         }
         echo "<h2>fromname:" .  $fromname ."</h2>";
         echo "<h2>fromaddress:" .  $fromaddress ."</h2>";
         echo "<h2>subject:" .  quoted_printable_decode($header->subject) ."</h2>";
         echo "<h2>date: " . $header->date ." </h2>";
         
         $message = imap_body($inbox, $msg_number);
        
        
        $part_number = 1;
        $message = imap_fetchbody($inbox, $msg_number, $part_number);
         echo "<h4>body</h4>";
         echo "<p>".quoted_printable_decode($message)."</p>";
        
        //Source: https://electrictoolbox.com/extract-attachments-email-php-imap/
        $structure = imap_fetchstructure($inbox, $msg_number);
        echo "<h4>Attachements</h4>"; 
        
        $attachments = array();
        if(isset($structure->parts) && count($structure->parts)) {
        
        	for($i = 0; $i < count($structure->parts); $i++) {
        
        		$attachments[$i] = array(
        			'is_attachment' => false,
        			'filename' => '',
        			'name' => '',
        			'attachment' => ''
        		);
        		
        		if($structure->parts[$i]->ifdparameters) {
        			foreach($structure->parts[$i]->dparameters as $object) {
        				if(strtolower($object->attribute) == 'filename') {
        					$attachments[$i]['is_attachment'] = true;
        					$attachments[$i]['filename'] = $object->value;
        				}
        			}
        		}
        		
        		if($structure->parts[$i]->ifparameters) {
        			foreach($structure->parts[$i]->parameters as $object) {
        				if(strtolower($object->attribute) == 'name') {
        					$attachments[$i]['is_attachment'] = true;
        					$attachments[$i]['name'] = $object->value;
        				}
        			}
        		}
        		
        		if($attachments[$i]['is_attachment']) {
        			$attachments[$i]['attachment'] = imap_fetchbody($inbox, $msg_number, $i+1);
        			if($structure->parts[$i]->encoding == 3) { // 3 = BASE64
        				$attachments[$i]['attachment'] = base64_decode($attachments[$i]['attachment']);
        			}
        			elseif($structure->parts[$i]->encoding == 4) { // 4 = QUOTED-PRINTABLE
        				$attachments[$i]['attachment'] = quoted_printable_decode($attachments[$i]['attachment']);
        			}
        		}
        	}
        }
      
        //Source https://stackoverflow.com/questions/39717000/using-php-to-read-email-attachment-csv
        /* iterate through each attachment and save it */
        foreach($attachments as $attachment)
        {
            if($attachment['is_attachment'] == 1)
            {
                $filename = $attachment['name'];
                if(empty($filename)) $filename = $attachment['filename'];

                if(empty($filename)) $filename = time() . ".dat";
                $folder = "attachment";
                if(!is_dir($folder))
                {
                     mkdir($folder);
                }
                $fp = fopen($folder . "/". $email_number . "-" . $filename, "w+");
                fwrite($fp, $attachment['attachment']);
                fclose($fp);
            }
        }
    }
         
     
}

/* close the connection */
imap_close($inbox);

echo "<h4>All E-Mails and Attachment Downloaded</h4>";
?>
