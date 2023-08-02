# PHP framework



## Getting started

```
composer create-project longdhdev/holaframework:dev-master@dev
```

## Router 
- Set up in router/web.php
- Router will receive 2 parameters, 1st parameter will be url, 2nd parameter will be array including controller and function in controller
```
use Core\Router;
use App\Controllers\HomeController;

Router::get('/', [HomeController::class,'index']);
Router::get('/home', [HomeController::class,'index']);
```

## App

- Create controller in folder app/Controllers
- Create model in folder app/Models
- Create view in folder app/views

### Use controller

- Create controller

```
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

### Use model

- Create model 
- To create a function in the model, make it a static function

```
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
```
public function index(){
  $category = $this->model(Categories::class)::index();
  echo 'index';
}
```

### Use view
- Create view in folder app/views with name {name_file}.view.php
- Use view controller
```
<?php
namespace App\Controllers;
use App\Models\Categories;
use Core\BaseController;
use Core\Request;

class HomeController extends BaseController {
    public function __construct()
    {}

    public function index(){
        echo 'index';
        $this->render_view('name_file', ["title" => "Home"]);
    }
}
```
- Use variable in view
```
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