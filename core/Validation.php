<?php

namespace System\Core;

class Validation {
     private static $errors;
     private static $data;

     public static function create($data, $rules = []) {
         self::$errors = new \stdClass();
         self::$errors->errors = new \stdClass();
         if (!empty($data)) {
             $keys_validate = array_keys($rules);
             foreach ($keys_validate as $name) {
                 if (!isset($data[$name])) {
                     $value = '';
                     self::handleRule($name, $value, $rules, self::$errors);
                 } else {
                     $value = $data[$name];
                     self::handleRule($name, $value, $rules, self::$errors);
                 }
             }
         } else {
             $keys_validate = array_keys($rules);
             foreach ($keys_validate as $name) {
                 self::handleRule($name, '', $rules, self::$errors);
             }
         }
         self::$data = $data;
         return new static();
     }

    public function errors() {
        return count((array)self::$errors->errors) ? self::$errors->errors:null;
    }

    public function data() {
         return count((array)self::$data) ? json_decode(json_encode(self::$data)):null;
    }

    public function errorsArray() {
        return count((array)self::$errors->errors) ? json_decode(json_encode(self::$errors->errors), true):null;
    }

    public function dataArray() {
        return count((array)self::$data) ? json_decode(json_encode(self::$data), true):null;
    }



    private static function handleRule($name, $value, $rules = [], &$errors){
        $list_rule = [
            'required' => [
                'function' => function (...$value) {
                    return is_null($value[0]) || $value[0] === '' ? true : false;
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
            self::$errors->errors->{$name} = new \stdClass();
            foreach ($rules[$name] as $key => $rule) {
                $key = explode(':', $key);
                $rule = explode(':', $rule);
                if (is_string($key[0]) && isset($list_rule[$key[0]])) {
                    $data_key = $key;
                    $key = $key[0];
                    self::$errors->errors->{$name}->{$key} = call_user_func($list_rule[$key]['function'], $value, $data_key[1] ?? 'none');
                    if (self::$errors->errors->{$name}->{$key}) {
                        self::$errors->errors->{$name}->{$key} = $rule[0];
                        self::$errors->errors->{$name}->{$key} = str_replace("{{" . $name . "}}", $data_key[1] ?? '', self::$errors->errors->{$name}->{$key});
                    } else {
                        unset(self::$errors->errors->{$name}->{$key});
                    }
                } else if (isset($list_rule[$rule[0]])) {
                    $data_rule = $rule;
                    $rule = $data_rule[0];
                    self::$errors->errors->{$name}->{$rule} = call_user_func($list_rule[$rule]['function'], $value, $data_rule[1] ?? 'none');
                    if (self::$errors->errors->{$name}->{$rule}) {
                        self::$errors->errors->{$name}->{$rule} = preg_replace("({{field}}|{{max}}|{{min}})", $name, $list_rule[$rule]['text']);
                    } else {
                        unset(self::$errors->errors->{$name}->{$rule});
                    }
                }
            }
            if (!count((array)self::$errors->errors->{$name})) unset(self::$errors->errors->{$name});
        }
        return $errors;
    }

}