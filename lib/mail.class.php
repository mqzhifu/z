<?php
$rs = include  PLUGIN.'phpmailer/class.phpmailer.php';
class Mail{
	public $ErrorInfo;
	function send($content,$toMail,$title){
		try {
			$mail = new PHPMailer(true); //New instance, with exceptions enabled
		    
			
			$mail->CharSet = "UTF-8";
			
			$body             = $content;
			$body             = preg_replace('/\\\\/','', $body); //Strip backslashes
		
			$mail->IsSMTP();                           // tell the class to use SMTP
			//$mail->SMTPAuth   = true;                  // enable SMTP authentication
			$mail->Port       = 25;                    // set the SMTP server port
// 			$mail->Host       = "smtp.ym.163.com"; 	//公司 
// 			$mail->Host       = "smtp.163.com";		//163
// 			$mail->Username   = "mqzhifu@163.com";
// 			$mail->Password   = "wangdongyan";
// 			$mail->Host       = "smtp.easyedm.com";
// 			$mail->Username   = "gamebean.com";
// 			$mail->Password   = "gamebean667";
//		 	$mail->IsSendmail();  // tell the class to use Sendmail
// 			$mail->AddReplyTo("easyedm.com","First Last");

			$mail->Host       = "mail.chinaejf.com";
			//$mail->Username   = "service";
			//$mail->Password   = "123";
			$mail->From       = "service@mail.chinaejf.com";
			$mail->FromName   = '中国积分网';
		
			$mail->AddAddress($toMail);
		
			$mail->Subject  = $title;
		
			$mail->AltBody    = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test
			$mail->WordWrap   = 80; // set word wrap
		
			$mail->MsgHTML($body);
		
			$mail->IsHTML(true); // send as HTML
			
			$this->Mailer= 'smtp';
			
			$mail->Send();
			//var_dump($mail->ErrorInfo);
			$this->ErrorInfo = $mail->ErrorInfo;
			
			return 'Message has been sent.';
		} catch (phpmailerException $e) {
			echo $e->errorMessage();
			return 0;
		}
	}
}