<?php
//cron.php
define('CRON_TAB',true);
$host = dirname(__FILE__);
//echo $host;
switch(strtolower($host)){
	//Developer machine
	case 'd:\webapps\projectpulse.local\html\cron':
	require_once("/WebApps/projectpulse.local/html/include/config.inc.php");
	break;
	
	//Live Server
	case "/home1/smartap1/app013.smartapps4free.com/cron":
	require_once("/home1/smartap1/app013.smartapps4free.com/include/config.inc.php");
	break;
	
	default:
	die("$host is not valid path");
}

require_once(ABS_PATH.'include/classes/Alert.class.php');
require_once(ABS_PATH.'include/phpmailer/src/PHPMailer.php');
require_once(ABS_PATH.'include/phpmailer/src/Exception.php');
require_once(ABS_PATH.'include/phpmailer/src/SMTP.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


ignore_user_abort(1);
set_time_limit(0);

//send out email alerts
if(EMAIL_ALERT){

	$email_alert_list = $db->select("tbl_alert","fStatus=20 AND fTo IS NOT NULL AND fAlertType=2",false);
	//echo '<pre>'.print_r($email_alert_list,true).'</pre>';
    if(empty($email_alert_list) === false){
		
		try{
			$mail = new PHPMailer(true);
			$mail->IsSMTP();
			$mail->SMTPDebug  = 0;          // enables SMTP debug information (for testing)
			$mail->SMTPAuth   = true;       // enable SMTP authentication
			$mail->Host       = SMTP_HOST; 	// SMTP server
			$mail->Port       = SMTP_PORT;  // set the SMTP port for the GMAIL server
			$mail->SMTPSecure = 'ssl'; 		//use post 25 with tls and 465 with ssl, port 587 requires port opened with ISP to work
			$mail->Username   = SMTP_USERNAME; // SMTP account username
			$mail->Password   = SMTP_PASSWORD;        // SMTP account password
			//$mail->ReturnPath = 'support@'.DOMAIN_NAME;
			$mail->AddReplyTo(ADMIN_EMAIL, DOMAIN_NAME);
			$mail->SetFrom(SMTP_USERNAME, DOMAIN_NAME);
			
			foreach($email_alert_list as $item){
				$to = EMAIL_MODE == 'sandbox' ? ADMIN_EMAIL : $item['fTo'];
				$mailsubject = $item['fSubject'];
				$mailbody = html_entity_decode($item['fAlertText']);
				$mail->Subject = $mailsubject;
				$mail->MsgHTML($mailbody);
				$mail->AddAddress($to);
				if(EMAIL_MODE == 'sandbox'){
					$mail->AddAddress(WEBMASTER_EMAIL);
				}
				$mail->Send();
				$alert = new Alert($item);
				$alert->status = 21;
				$alert->save();
				$mail->ClearAddresses();
			}

		}catch(phpmailerException $ex){
			$log = "PHPMailer Exception:".$ex->errorMessage().PHP_EOL;
			$log .= PHP_EOL;
			file_put_contents('cron.log',$log);
		}

	}

}
?>
