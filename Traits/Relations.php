<?php

namespace Hola\Traits;

trait Relations {

    public $HAS_MANY = 'HAS_MANY';
    public $HAS_ONE = 'HAS_ONE';
    public $BELONG_TO = 'BELONG_TO';
    public $MANY_TO_MANY = 'MANY_TO_MANY';
    public $BELONG_TO_MANY = 'BELONG_TO_MANY';

    private function relations($model, $model_many = null, $name, $foreign_key, $foreign_key2 = null, $primary_key, $relation)
    {
        $log_debug = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1];
        return [
            'model' => $model,
            'model_many' => $model_many,
            'name' => $name,
            'foreign_key' => $foreign_key,
            'foreign_key2' => $foreign_key2,
            'primary_key' => $primary_key,
            'relation' => $relation,
            'log' => $log_debug
        ];
    }

    public function hasMany($model, $foreign_key, $key = 'id') {
        $parent_function = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'];
        return $this->relations($model, null, $parent_function, $foreign_key, null, $key, $this->HAS_MANY);
    }

    public function hasOne($model, $foreign_key, $key = 'id') {
        $parent_function = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'];
        return $this->relations($model, null, $parent_function, $foreign_key, null, $key, $this->HAS_ONE);
    }

    public function belongsToMany($model, $model_many_to_many, $foreign_key, $foreign_key2, $key = 'id') {
        $parent_function = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'];
        return $this->relations($model, $model_many_to_many, $parent_function, $foreign_key, $foreign_key2, $key, $this->BELONG_TO_MANY);
    }

    public function belongsTo($model, $foreign_key, $key = 'id') {
        $parent_function = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'];
        return $this->relations($model, null, $parent_function, $foreign_key, null, $key, $this->BELONG_TO);
    }

    public function manyToMany($model, $model_many_to_many, $foreign_key, $foreign_key2, $key = 'id') {
        $parent_function = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'];
        return $this->relations($model, $model_many_to_many, $parent_function, $foreign_key, $foreign_key2, $key, $this->MANY_TO_MANY);
    }
}