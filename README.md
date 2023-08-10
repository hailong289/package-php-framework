# PHP framework



## Getting started

```
composer create-project longdhdev/holaframework:dev-master@dev
```

## Router 
- Set up in router/web.php 
- Router will receive 2 parameters, 1st parameter will be url, 2nd parameter will be array including controller and function in controller

```php
use Core\Router;
use App\Controllers\HomeController;

Router::get('/', [HomeController::class,'index']);
Router::get('/home', [HomeController::class,'index']);
```
- Use parameters
```php
use Core\Router;
use App\Controllers\HomeController;

// url {domain}/home/1
Router::get('/home/{id}', [HomeController::class,'index']); 

// url {domain}/home/detail/2
Router::get('/home/detail/{id}', [HomeController::class,'detail']); 

```
- Parameters in controller
```php
<?php
namespace App\Controllers;

class HomeController extends BaseController {
    public function index($id){
        echo $id;
    }
    public function detail($id){
        echo $id;
    }
}
```

## App

- Create controller in folder app/Controllers
- Create model in folder app/Models
- Create view in folder app/views

### Use controller

- Create controller

```php
<?php
namespace App\Controllers;
use Core\BaseController;

class HomeController extends BaseController {
    public function __construct()
    {}

    public function index(){
        echo 'index';
    }
    public function store(){
        echo 'store';
    }
}

```
- How to create namespace in controller
```php
File in folder app/Controllers -> namespace App\Controllers 
File in folder app/Controllers/{name_folder} -> namespace App\Controllers\{name_folder} 
```
### Use model

- Create model 
- To create a function in the model, make it a static function

```php
<?php
namespace App\Models;
use Core\Model\Model;

class Categories extends Model {
    protected static $tableName = 'categories';
    protected static $field = [
        'id',
        'name'
    ];

    public static function index(){
        echo 'categories index';
    }
}
```

- Use model in controller

=== use way 1 ===
```php
public function index(){
  $category = $this->model(Categories::class)::index();
}
```
=== use way 2 ===
```php
class Controller extends BaseController {
    public function __construct()
    {
        $this->model([
            Categories::class,
            Product::class
        ]);
    }
    public function listCategories(){
         $data = $this->Categories::get();
    }
    public function listProduct(){
         $data = $this->Product::get();
         return $data;
    }
}
```
=== use way 3 ===
```php
class Controller extends BaseController {
    public function __construct()
    {
        $this->model([
            Categories::class,
            Product::class
        ]);
    }
    public function listCategories(){
         $data = Categories::get();
    }
    public function listProduct(){
         $data = Product::get();
         return $data;
    }
}
```

### Use view
- Create view in folder app/views with name {name_file}.view.php
- Use view controller
```php
<?php
namespace App\Controllers;
use App\Models\Categories;
use Core\BaseController;
use Core\Request;

class HomeController extends BaseController {
    public function __construct()
    {}

    public function index(){
        return $this->render_view('name_file', ["title" => "Home"]);
    }
}
```
- Use variable in view
```html
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?=$title ?? ''?></title> // get data
</head>

<body>
</body>
</html>
```

### Query Builder