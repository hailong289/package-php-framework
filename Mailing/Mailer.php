<?php
namespace Hola\Mailing;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

class Mailer {
    private $mail;
    private $isConfig = false;

    public function __construct()
    {
        $this->mail = new PHPMailer(true);
    }

    public function config(
        $host = null,
        $username = null,
        $password = null,
        $port = null,
        $charset = null,
        $encryption = null,
        $auth = null,
        $debug = null
    ) {
        $this->isConfig = true;
        $this->mail->SMTPDebug = $debug ?? conval('MAIL_DEBUG', SMTP::DEBUG_OFF);// Enable verbose debug output
        $this->mail->isSMTP();
        $this->mail->Host = $host ?? conval('MAIL_HOST','smtp.gmail.com');
        $this->mail->SMTPAuth = $auth ?? conval('MAIL_AUTH', true);// Enable SMTP authentication
        $this->mail->Username = $username ?? conval('MAIL_USERNAME','user@gmail.com');// SMTP username
        $this->mail->Password = $password ?? conval('MAIL_PASSWORD','password'); // SMTP password
        $this->mail->CharSet = $charset ?? conval('MAIL_CHARSET', PHPMailer::CHARSET_UTF8);
        $this->mail->SMTPSecure = $encryption ?? conval('MAIL_ENCRYPTION', PHPMailer::ENCRYPTION_SMTPS); // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` also accepted
        $this->mail->Port = $port ?? conval('MAIL_PORT', 587); // TCP port to connect to
        return $this;
    }

    public function reConfigDefault() {
        $this->isConfig = false;
        return $this;
    }

    public function getMail()
    {
        return $this->mail instanceof PHPMailer ? $this->mail:false;
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

    public function setDebug($number = 0)
    {
        $this->mail->SMTPDebug = $number;
        return $this;
    }

    public function setEncryption($encryption = PHPMailer::ENCRYPTION_SMTPS)
    {
        $this->mail->SMTPSecure = $encryption;
        return $this;
    }

    public function setAuth($on_off = true)
    {
        $this->mail->SMTPAuth = $on_off;
        return $this;
    }

    public function to($to, $data = []) {
        if(is_array($to)) {
            foreach ($to as $email) {
                $this->mail->addAddress($email);
            }
        } else {
            $this->mail->addAddress($to);
        }
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
        if (!$this->isConfig) {
            $this->config();
        }
        $this->mail->send();
        return $this;
    }

    public function withAttachment($file)
    {
        if (is_array($file)) {
            foreach ($file as $item) {
                $this->mail->addAttachment($item);
            }
        } else {
            $this->mail->addAttachment($file);
        }
        return $this;
    }

    public function withCC($cc)
    {
        if (is_array($cc)) {
            foreach ($cc as $email) {
                $this->mail->addCC($email);
            }
        } else {
            $this->mail->addCC($cc);
        }
        return $this;
    }

    public function withBCC($bcc)
    {
        if (is_array($bcc)) {
            foreach ($bcc as $email) {
                $this->mail->addBCC($email);
            }
        } else {
            $this->mail->addBCC($bcc);
        }
        return $this;
    }

    public function withData($data)
    {
        foreach ($data as $key=>$value) {
            if ($key === 'title') {
                $this->setSubject($value);
            }
            if ($key === 'content') {
                $this->setBody($value);
            }
            if ($key === 'cc') {
                $this->withCC($value);
            }
            if ($key === 'bcc') {
                $this->withBCC($value);
            }
            if($key === 'attachment') {
                $this->withAttachment($value);
            }
        }
    }
}