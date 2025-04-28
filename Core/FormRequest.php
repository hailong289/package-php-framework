<?php

namespace Hola\Core;

use Hola\Data\ShareData;
use Hola\Exceptions\AppException;
use Hola\Transport\Request;
use Hola\Transport\Response;

class FormRequest extends Request {
    private $data_errors = null;
    private $data = null;
    public function __construct()
    {
        $this->validate();
    }

    private function validate()
    {
        $request = new Request();
        $is_json = $request->isJson();
        if(method_exists($this,'auth')) {
            if(!$this->auth()) {
                $data = [
                    'message' => 'unauthorized',
                    'code' => 403
                ];
                $name_view = 'error.index';
                if (method_exists($this,'view_auth')) {
                    $name_view = $this->view_auth();
                }
                if (method_exists($this,'data_auth')) {
                    $data = $this->data_auth();
                }
                if ($is_json) {
                    return Response::json($data)->setStatus(403)->callback()->exit();
                }
                return Response::view($name_view, $data)->setStatus(403)->callback()->exit();
            }
        }

        if(!method_exists($this,'rules')) {
            $class = get_class($this);
            throw new AppException("Function rules does not exist in $class");
        }
        $validate = Validation::create($request->all(), $this->rules());
        if(!empty($validate->errors())) {
            $this->data_errors = $validate->errors();
            ShareData::init()->create('errors', $this->data_errors);
        }
        $this->data = $validate->data();
        return $this->data;
    }

    public function errors()
    {
        return $this->data_errors;
    }

    public function data(): array|object|null
    {
        return $this->data;
    }
}