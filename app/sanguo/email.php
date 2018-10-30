<?php

/**
 * Description of email
 * 发送邮件
 * @author tyj
 */
class Mod_Tools_Send_email extends Mod_Tools_Send {

    protected function run($email, $subject, $content) {
        if (! is_array($email)) {
            $email = explode(";", $email);
        }
        $config = MooConfig::get("main.EMAIL");
        $mail = new MooMail();
        $mail->setSMTP($config["smtp"], $config["username"], $config["password"],465,"ssl");
        $mail->setFrom($config["username"], $config["name"]);
        $mail->setTo($email, $email);

        return $mail->sendMail($subject, $content);
    }
}
