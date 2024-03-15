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
        $this->mail->SMTPDebug = config_env('MAIL_DEBUG', SMTP::DEBUG_OFF);// Enable verbose debug output
        $this->mail->isSMTP();
        $this->mail->Host = config_env('MAIL_HOST','smtp.gmail.com');
        $this->mail->SMTPAuth = true;// Enable SMTP authentication
        $this->mail->Username = config_env('MAIL_USERNAME','user@gmail.com');// SMTP username
        $this->mail->Password = config_env('MAIL_PASSWORD','password'); // SMTP password
        $this->mail->SMTPSecure = config_env('MAIL_ENCRYPTION', PHPMailer::ENCRYPTION_SMTPS); // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` also accepted
        $this->mail->Port = config_env('MAIL_PORT', 587); // TCP port to connect to
    }

    public function getMail()
    {
        return $this->mail;
    }

    public function setSubject($subject)
    {
        $this->mail->Subject = $subject;
        return $this;
    }

    public function setBody($body)
    {
        $this->mail->Body = $body;
        return $this;
    }

    public function setAltBody($AltBody)
    {
        $this->mail->AltBody = $AltBody;
        return $this;
    }

    public function setCharset($charset = PHPMailer::CHARSET_UTF8)
    {
        $this->mail->CharSet = $charset;
        return $this;
    }

    public function to($to, $data = []) {
        $this->mail->addAddress($to);
        if (!empty($data)) $this->withData($data);
        return $this;
    }

    public function toWithName($to, $name, $data = []) {
        $this->mail->addAddress($to, $name);
        if (!empty($data)) $this->withData($data);
        return $this;
    }

    public function withHTML()
    {
        $this->mail->isHTML(true);
        return $this;
    }

    public function from($from, $name = '')
    {
        $this->mail->setFrom($from, $name);
        return $this;
    }

    public function work()
    {
        $this->mail->send();
        return $this;
    }

    public function withData($data)
    {
        foreach ($data as $key=>$value) {
            if ($key === 'title') {
                $this->mail->Subject = $value;
            }
            if ($key === 'content') {
                $this->mail->Body = $value;
            }
            if ($key === 'cc') {
                if (is_array($value)) {
                    foreach ($value as $cc) {
                        $this->mail->addCC($cc['email'], $cc['name'] ?? '');
                    }
                } else {
                    $this->mail->addCC($value);
                }
            }
            if ($key === 'bcc') {
                if (is_array($value)) {
                    foreach ($value as $cc) {
                        $this->mail->addBCC($cc['email'], $cc['name'] ?? '');
                    }
                } else {
                    $this->mail->addBCC($value);
                }
            }
            if($key === 'attachment') {
                if(is_array($value)) {
                    foreach ($value as $file) {
                        $this->mail->addAttachment($file['name']);
                    }
                } else {
                    $this->mail->addAttachment($value);
                }
            }
        }
    }
}