<?php

namespace Hola\Mailing;

class MailerBuilder extends Mailer {

    public function __construct() {
        parent::__construct();
    }

    public function send()
    {
        $mailFrom = conval('MAIL_FROM_ADDRESS', null);
        $mailFromName = conval('MAIL_FROM_NAME', null);
        try {
            if ($mailFrom) {
                $this->from($mailFrom, $mailFromName);
            }

            if (method_exists($this,'title')) {
                $this->setSubject($this->title());
            }

            if (method_exists($this,'view')) {
                $this->withHtml();
                $this->setBody($this->view());
            }

            if (method_exists($this,'mailFrom')) {
                $name = method_exists($this,'mailFromName') ? $this->mailFromName() : null;
                $this->from($this->mailFrom(), $name);
            }

            if (method_exists($this,'mailTo')) {
                $this->to($this->mailTo());
            }

            $this->work();
            return true;
        } catch (\Throwable $e) {
            if (method_exists($this,'failed')) {
                $this->failed($e);
            }
            return false;
        }
    }

}