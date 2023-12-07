<?php
namespace App\Core;

class BaseController extends \stdClass {
    public function model($names) {
        $result = [];
        if (is_array($names)) {
            foreach ($names as $name){
                $variable = str_replace('App\\Models\\','', $name);
                $model = $name;
                if(file_exists(path_root($model.'.php'))){
                    require_once path_root($model.'.php');
                    if(class_exists($model)){
                        $model = new $model();
                        $this->{$variable} = $model;
                    }else{
                        $result[] = (object)[
                            'error_code' => 1,
                            'message' => "Model $name does not exits"
                        ];
                    }
                }
            }
            if (count($result) > 0) {
                if($result[0]->error_code){
                    echo json_encode($result[0]);
                    exit();
                }
            }
        }else{
            $model = $names;
            if(file_exists(path_root($model.'.php'))){
                require_once path_root($model.'.php');
                if(class_exists($model)){
                    return new $model();
                }else{
                    echo json_encode([
                        'error_code' => 1,
                        'message' => "Model $names does not exits"
                    ]);
                    exit();
                }
            }
        }
    }
    // Render ra view
    public function render_view($views, $data = [])
    {
        // Đổi key mảng thành biến
        if(count($data)) $GLOBALS['share_date_view'] = $data;
        extract($data);
        $views = preg_replace('/([.]+)/', '/' , $views);
        if(file_exists(__DIR__ROOT . '/app/views/'.$views.'.view.php')){
            require_once __DIR__ROOT . '/app/views/'.$views.'.view.php';
        }
    }

    public function validateRequest($data, $rules = []) {
        $errors = new \stdClass();
        $errors->errors = new \stdClass();
        foreach ($data as $name=>$value) {
            $this->handleRule($name, $value, $rules, $errors);
        }
        return count((array)$errors->errors) ? $errors:json_decode(json_encode($data));
    }

    private function handleRule($name, $value, $rules = [], &$errors){
        $list_rule = [
            'required' => [
                'function' => function (...$value) {
                    return empty($value[0]) ? true : false;
                },
                'text' => 'Field {{field}} is required'
            ],
            'number' => [
                'function' => function (...$value) {
                    return !is_numeric($value[0]) ? true : false;
                },
                'text' => 'Field {{field}} is number'
            ],
            'string' => [
                'function' => function (...$value) {
                    return !is_string($value[0]) ? true : false;
                },
                'text' => 'Field {{field}} is string'
            ],
            'max' => [
                'function' => function (...$value) {
                    return ($value[1] != 'none' && $value[0] > $value[1]) ? true : false;
                },
                'text' => 'Field {{field}} must be less than or equal to {{max}}'
            ],
            'min' => [
                'function' => function (...$value) {
                    return ($value[1] != 'none' && $value[0] < $value[1]) ? true : false;
                },
                'text' => 'Field {{field}} must be greater than or equal to {{min}}'
            ],
            'pattern' => [
                'function' => function (...$value) {
                    return ($value[1] != 'none' && !preg_match($value[1], $value[0])) ? true : false;
                },
                'text' => '{{field}} is invalid'
            ],
            'not_pattern' => [
                'function' => function (...$value) {
                    return ($value[1] != 'none' && preg_match($value[1], $value[0])) ? true : false;
                },
                'text' => '{{field}} is invalid'
            ],
            'email' => [
                'function' => function (...$value) {
                    return !filter_var($value[0], FILTER_VALIDATE_EMAIL) ? true : false;
                },
                'text' => '{{field}} is invalid'
            ],
            'boolean' => [
                'function' => function (...$value) {
                    return !is_bool(filter_var($value[0], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)) ? true : false;
                },
                'text' => 'Field {{field}} is boolean'
            ],
            'array' => [
                'function' => function (...$value) {
                    return !is_array($value[0]) ? true : false;
                },
                'text' => 'Field {{field}} is array'
            ],
            'date' => [
                'function' => function (...$value) {
                    return !(is_string($value[0]) && !isDate($value[0])) ? true : false;
                },
                'text' => 'Field {{field}} is date'
            ],
        ];
        if (!empty($rules[$name]) && count($rules[$name])) {
            $errors->errors->{$name} = new \stdClass();
            foreach ($rules[$name] as $key => $rule) {
                $key = explode(':', $key);
                $rule = explode(':', $rule);
                if (is_string($key[0]) && isset($list_rule[$key[0]])) {
                    $data_key = $key;
                    $key = $key[0];
                    $errors->errors->{$name}->{$key} = call_user_func($list_rule[$key]['function'], $value, $data_key[1] ?? 'none');
                    if ($errors->errors->{$name}->{$key}) {
                        $errors->errors->{$name}->{$key} = $rule[0];
                        $errors->errors->{$name}->{$key} = str_replace("{{" . $name . "}}", $data_key[1] ?? '', $errors->errors->{$name}->{$key});
                    } else {
                        unset($errors->errors->{$name}->{$key});
                        if (!count((array)$errors->errors->{$name})) unset($errors->errors->{$name});
                    }
                } else if (isset($list_rule[$rule[0]])) {
                    $data_rule = $rule;
                    $rule = $data_rule[0];
                    $errors->errors->{$name}->{$rule} = call_user_func($list_rule[$rule]['function'], $value, $data_rule[1] ?? 'none');
                    if ($errors->errors->{$name}->{$rule}) {
                        $errors->errors->{$name}->{$rule} = str_replace("{{field}}", $name, $list_rule[$rule]['text']);
                        $errors->errors->{$name}->{$rule} = str_replace("{{max}}", $data_rule[1] ?? '', $errors->errors->{$name}->{$rule});
                        $errors->errors->{$name}->{$rule} = str_replace("{{min}}", $data_rule[1] ?? '', $errors->errors->{$name}->{$rule});
                    } else {
                        unset($errors->errors->{$name}->{$rule});
                        if (!count((array)$errors->errors->{$name})) unset($errors->errors->{$name});
                    }
                }
            }
        }
        return $errors;
    }
}