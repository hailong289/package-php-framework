<?php

namespace Hola\Mailing;

class MailerBuilder extends Mailer {

    public function send()
    {
        parent::__construct();
        if (method_exists($this,'title')) {
            $this->setSubject($this->title());
        }
        if (method_exists($this,'view')) {
            $this->withHtml();
            $this->setBody($this->view());
        }
        return $this->work();
    }

}