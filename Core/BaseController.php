<?php
namespace Hola\Core;
class BaseController extends \stdClass {
    public function model($names) {
        $result = [];
        if (is_array($names)) {
            foreach ($names as $name){
                $variable = str_replace('App\\Models\\','', $name);
                $model = $name;
                if(class_exists($model)){
                    $model = new $model();
                    $this->{$variable} = new $model();
                    return $this->{$variable};
                }else{
                    throw new \RuntimeException("Model $name does not exits", 500);
                }
            }
        } else {
            $model = $names;
            if(class_exists($model)){
                $model = new $model();
                $this->{$variable} = new $model();
                return $this->{$variable};
            }else{
                throw new \RuntimeException("Model $model does not exits", 500);
            }
        }
    }

    public function middleware($name)
    {
        $middleware = new Middleware();
        $middleware->set($name);
    }
}