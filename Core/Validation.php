<?php

namespace Hola\Core;

class Validation {
     public static \stdClass $bind;

     public static function create($data, $rules = []) {
         self::$bind = new \stdClass();
         self::$bind->errors = new \stdClass();
         self::$bind->data = new \stdClass();
         if (!empty($data)) {
             $keys_validate = array_keys($rules);
             foreach ($keys_validate as $name) {
                 if (!isset($data[$name])) {
                     $value = '';
                     self::handleRule($name, $value, self::$bind->errors, $rules);
                 } else {
                     $value = $data[$name];
                     self::handleRule($name, $value, self::$bind->errors, $rules);
                 }
             }
         } else {
             $keys_validate = array_keys($rules);
             foreach ($keys_validate as $name) {
                 self::handleRule($name, '', self::$bind->errors, $rules);
             }
         }
         self::$bind->data = $data;
         return new self();
     }

    public function errors() {
        $isCount = count((array)self::$bind->errors);
        return $isCount ? self::$bind->errors : null;
    }

    public function data() {
        $data = convert_to_object(self::$bind->data);
        $isCount = count((array)$data);
        return $isCount ? $data : null;
    }

    public function errorsArray() {
        $errors = convert_to_array(self::$bind->errors);
        $isCount = count($data);
        return $isCount ? $errors :null;
    }

    public function dataArray() {
        $data = convert_to_array(self::$bind->data);
        $isCount = count($data);
        return $isCount ? $data : null;
    }

    private static function handleRule($name, $value, &$errors, $rules = []){
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
            $errors->{$name} = new \stdClass();
            foreach ($rules[$name] as $key => $rule) {
                $key = explode(':', $key);
                $rule = explode(':', $rule);
                if (is_string($key[0]) && isset($list_rule[$key[0]])) {
                    $data_key = $key;
                    $key = $key[0];
                    $errors->{$name}->{$key} = call_user_func($list_rule[$key]['function'], $value, $data_key[1] ?? 'none');
                    if ($errors->{$name}->{$key}) {
                        $errors->{$name}->{$key} = $rule[0];
                        $errors->{$name}->{$key} = str_replace("{{" . $name . "}}", $data_key[1] ?? '', $errors->{$name}->{$key});
                    } else {
                        unset($errors->{$name}->{$key});
                    }
                } else if (isset($list_rule[$rule[0]])) {
                    $data_rule = $rule;
                    $rule = $data_rule[0];
                    $errors->{$name}->{$rule} = call_user_func($list_rule[$rule]['function'], $value, $data_rule[1] ?? 'none');
                    if ($errors->{$name}->{$rule}) {
                        $errors->{$name}->{$rule} = preg_replace("({{field}}|{{max}}|{{min}})", $name, $list_rule[$rule]['text']);
                    } else {
                        unset($errors->{$name}->{$rule});
                    }
                }
            }
            if (!count((array)$errors->{$name})) unset($errors->{$name});
        }
        return $errors;
    }

}