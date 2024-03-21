<?php

namespace System\Core;

class FormRequest {
    private $data_errors;
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
                if ($is_json) {
                    echo json_encode([
                        'message' => 'unauthorized',
                        'code' => 403
                    ]);
                    exit();
                }
                Response::view('error.index',[
                    "message" => 'unauthorized',
                    "code" => 403
                ]);
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
                echo $validate->errors();
                exit();
            }
            $GLOBALS['share_date_view'] = $validate->errors();
        }
        return;
    }

    public function errors()
    {
        return $this->data_errors;
    }
}