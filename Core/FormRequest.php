<?php

namespace Hola\Core;

use Hola\Data\ShareData;
use Hola\Exceptions\AppException;
use Hola\Transport\Request;
use Hola\Transport\Response;

class FormRequest extends Request {
    protected array $attributes = [];
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
                if (method_exists($this,'failedView')) {
                    $name_view = $this->failedView();
                }
                if (method_exists($this,'withData')) {
                    $data = $this->withData();
                }
                if ($is_json) {
                    return Response::json($data)->setStatus(403)->send()->terminate();
                }
                return Response::view($name_view, $data)->setStatus(403)->send()->terminate();
            }
        }

        if(!method_exists($this,'rules')) {
            $class = get_class($this);
            throw new AppException("Function rules does not exist in $class");
        }

        $validate = Validation::create($request->all(), $this->rules());
        if (!empty($validate->errors())) {
            $this->attributes['errors'] = $validate->errors();
        } else if (method_exists($this, 'customValidation')) {
            try {
                $this->customValidation($request);
            } catch (\Throwable $e) {
                if (empty($this->attributes['errors'])) {
                    $this->attributes['errors']['custom'] = $e->getMessage();
                } else if (is_object($this->attributes['errors'])) {
                    $this->attributes['errors']->custom = $e->getMessage();
                } else {
                    $this->attributes['errors']['custom'] = $e->getMessage();
                }
            }
        }
        ShareData::init()->create('errors', $this->attributes['errors']);
        $this->attributes['data'] = $validate->data();
        return $this->attributes['data'];
    }

    public function errors()
    {
        return $this->attributes['errors'];
    }

    public function data(): array|object|null
    {
        return $this->attributes['data'];
    }
}