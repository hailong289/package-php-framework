<?php

namespace Hola\Mailing;

class MailerBuilder extends Mailer {

    public function __construct() {
        parent::__construct();
    }

    public function send()
    {
        try {
            if (method_exists($this,'title')) {
                $this->setSubject($this->title());
            }
            if (method_exists($this,'view')) {
                $this->withHtml();
                $this->setBody($this->view());
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