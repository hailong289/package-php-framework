<?php

namespace System\Core;
use System\Interfaces\FormRequestInterface\DataInterface;

class FormRequest extends Request {
    private $data_errors;
    private $data = [];
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
                http_response_code(403);
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
                    echo json_encode($data);
                    exit();
                }
                Response::view($name_view, $data);
                exit();
            }
        }

        if(!method_exists($this,'rules')) {
            $class = get_class($this);
            throw new \RuntimeException("Function rules does not exist in $class");
        }
        $validate = Validation::create($request->all(), $this->rules());
        if(!empty($validate->errors())) {
            $this->data_errors = $validate->errors();
            if ($is_json) {
                echo json_encode([
                    'errors' => $validate->errors()
                ]);
                exit();
            }
            $GLOBALS['share_data_errors'] = [
                'errors' => $validate->errors()
            ];
        }
        $this->data = $validate->data();
        return;
    }

    public function errors()
    {
        return $this->data_errors;
    }

    public function data()
    {
        return new class implements DataInterface {
            function value() {
                return $this->data;
            }
            function toArray() {
                return $this->data;
            }
            function toJson() {
                return $this->data;
            }
        };
    }
}