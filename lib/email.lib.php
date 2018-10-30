<?php
//发送邮件
require_once(PLUGIN."PHPMailer/class.phpmailer.php");
require_once(PLUGIN."PHPMailer/class.smtp.php");
//require_once  PLUGIN. "/PHPMailer/PHPMailerAutoload.php";

class EmailLib{
    public $mail = null;

    function init(){

    }

    function realSend($email,$content,$title){
//        $this->init();
////        $this->mail = $mail;


        $mail = new PHPMailer();
//        $mail->SMTPDebug = 1;
        $mail->isSMTP();
        $mail->SMTPAuth = true;

//        $mail->SMTPSecure = 'ssl';
//        $mail->Port = 465;

        $mail->Port = 25;
        $mail->CharSet = 'UTF-8';
        $mail->isHTML(true);// 邮件正文是否为html编码
//        $mail->SMTPSecure = "ssl";

        $mail->Host = $GLOBALS['main']['email']['smtpHost'];//发送件人昵称
        $mail->FromName = $GLOBALS['main']['email']['name'];
        $mail->Username = $GLOBALS['main']['email']['username'];
        $mail->Password = $GLOBALS['main']['email']['password'];
        // 设置发件人邮箱地址 同登录账号
        $mail->From =$GLOBALS['main']['email']['fromEmail'];


        $mail->Subject = $title;//邮件的主题
        $mail->Body = $content;// 添加邮件正文
        $mail->addAddress($email);// 添加多个收件人 则多次调用方法即可

        $rs = $mail->send();

        return $rs;

//
//        // 为该邮件添加附件
//        $mail->addAttachment('./example.pdf');
//        //同样该方法可以多次调用 上传多个附件
//        //$mail->addAttachment('./Jlib-1.1.0.js','Jlib.js');


    }

}