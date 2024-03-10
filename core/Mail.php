<?php
namespace System\Core;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

class Mail {
    private $mail;
    public function __construct()
    {
        $this->mail = new PHPMailer(true);
        $this->mail->SMTPDebug = SMTP::DEBUG_OFF;// Enable verbose debug output
        $this->mail->isSMTP();
        $this->mail->Host = config_env('MAIL_HOST','smtp.gmail.com');
        $this->mail->SMTPAuth = true;// Enable SMTP authentication
        $this->mail->Username = config_env('MAIL_USERNAME','user@gmail.com');// SMTP username
        $this->mail->Password = config_env('MAIL_PASSWORD','password'); // SMTP password
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` also accepted
        $this->mail->Port = config_env('MAIL_PORT', 587); // TCP port to connect to
    }

    public function getMail()
    {
        return $this->mail;
    }

    public function to($to, $data) {
        $this->mail->addAddress($to);
        foreach ($data as $key=>$value) {
             if($key === 'title') {
                 $this->mail->Subject = $value;
             }
            if($key === 'content') {
                $this->mail->Body = $value;
            }
        }
        return $this;
    }

    public function withHTML()
    {
        $this->mail->isHTML(true);
        return $this;
    }

    public function from($from, $name)
    {
        $this->mail->setFrom($from, $name);
        return $this;
    }

    public function work()
    {
        $this->mail->send();
        return $this;
    }
}