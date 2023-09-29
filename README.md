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
- Use router with prefix
```php
  Router::prefix('home')->group(function (){
      Router::get('/', [HomeController::class,'index']);
      Router::get('/detail', [HomeController::class,'detail']);
      Router::get('/list', [HomeController::class,'list']);
  }); 
  // The path will be 
  // https://domain.com/home
  // https://domain.com/home/detail
  // https://domain.com/home/list
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
use App\Core\BaseController;

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

- Get one record in model
```sql
   Categories::first();
```

- Get one record buy column with function where
```sql
   Categories::where('id','=', 1)->first(); // get by id
   Categories::where('name','=', 1)->first(); // get by name
   Categories::where('name','like', '%value%')->first(); // get by name
```
- Get all record in model
```sql
   Categories::get();
```
- Get all record buy column with function where
 
  ``The get() function will return an object. If you want to return an array data type, you can use the getArray() function.``
```sql
   Categories::where('id','=', 1)->get(); // get by id
   Categories::where('name','=', 1)->get(); // get by name
   Categories::where('name','like', '%value%')->get(); // get by name
       
   // return data type array
   Categories::where('id','=', 1)->getArray(); // get by id
   Categories::where('name','=', 1)->getArray(); // get by name
   Categories::where('name','like', '%value%')->getArray(); // get by name
```
- use select()
```sql
   Categories::select('*')->get();
   Categories::select(['*'])->get();
   Categories::select(['id','name'])->get();

   // with sum and count 
   Summary::select([
       'SUM(amount) as amount',
       'SUM(amount2) as amount2',
   ])->get();
   Region::select([
       'COUNT(id) as number'
   ])->get();
```
- use findById()

```sql
   Categories::findById(1); 
```
- use orWhere()
```sql
   Categories::where('id','=', 1)->orWhere('id','=',2)->get(); 
```
- use whereLike()
```sql
   Categories::whereLike('name', '%long')->get(); 
   Categories::whereLike('name', 'long%')->get(); 
   Categories::whereLike('name', '%long%')->get(); 
```
- use join

```php
   // way 1
   Blog::select('*')->join('categories', function ($q) {
      $q->on('categories.id','=','category_blogs.category_id');
   })->get(); 
   // way 2
   Blog::select('*')->join('categories')->on('categories.id','=','category_blogs.category_id')->get(); 
```

- use left join

```php
   // way 1
   Blog::select('*')->leftJoin('categories', function ($q) {
      $q->on('categories.id','=','category_blogs.category_id');
   })->get(); 
   // way 2
   Blog::select('*')->leftJoin('categories')->on('categories.id','=','category_blogs.category_id')->get(); 
```


- use right join

```php
   // way 1
   Blog::select('*')->rightJoin('categories', function ($q) {
      $q->on('categories.id','=','category_blogs.category_id');
   })->get(); 
   // way 2
   Blog::select('*')->rightJoin('categories')->on('categories.id','=','category_blogs.category_id')->get(); 
```

- use order by

```php
   News::select('*')->orderBy('id', 'DESC')->get(); // ASC, DESC
```

- use group by

```php
   // way 1
   News::select('*')->groupBy('id')->get(); 
   // way 2
   News::select('*')->groupBy(['field1','field2','field3'])->get();
```

- use limit

```php
   News::select('*')->limit(100)->get();
```

- use limit and offset

```sql
   News::select('*')->page(0)->limit(100)->get(); // offset 0 limit 100
   News::select('*')->page(1)->limit(100)->get(); // offset 100 limit 100
   News::select('*')->page(2)->limit(100)->get(); // offset 200 limit 100
```

- use insert
```sql
   News::insert([
       'name' => 'New',
       'status' => 1
   ]);
       
   // returns id on successful insert
   News::insertLastId([
       'name' => 'New',
       'status' => 1
   ]);
```

- use update
- ```The second parameter in the update function will default to id```
- ```If you want to use another column, leave it as an array with the column key and value```
```sql
   News::update ([
       'name' => 'New2',
       'status' => 1
   ], 1); // id

   // other key
   News::update ([
        'name' => 'New2',
        'status' => 1
   ], [
       'id' => 1,
       'name' => 'New'
   ]); // id, name
```

- Additionally, you can use pure SQL statements with custom functions

```php
   News::custom("SELECT * FROM news WHERE id = 1")->get();
   News::custom("SELECT * FROM news")->get();
```

- In addition to the insert function, you can use the create function to insert data into the table
- ``Note that the create function will insert the column according to the key you declared the key in the $field variable inside the model. If you have not declared a key, when using the create function when inserting data, that column will be ignored.``

```php
<?php
namespace App\Models;
use App\Core\Database;
use App\Core\Model\Model;

class News extends Model {
    protected static $tableName = 'new';
    protected static $field = [
        'title',
        'name',
        'status',
        'date'
    ];

    public static function index(){
         News::create([
             'title' => 'title'
             'name' => 'new',
             'status' => 1,
             'date' => '2023-09-28'
        ]);
    }
}
  
```

- use table with Database class
```php
<?php
namespace App\Controllers;
use App\Core\BaseController;
use App\Core\Database;

class HomeController extends BaseController {
   
    public function index(){
        $all = Database::table('categories')->get();
        $first = Database::table('categories')->where('id','=',1)->first();
    }

}
```

- use transaction

```php
<?php
namespace App\Controllers;
use App\Core\BaseController;
use App\Core\Database;use App\Models\Categories;

class HomeController extends BaseController {
    public function __construct()
    {
        $this->model([Categories::class]);
    } 
   
    public function index(){
       Database::beginTransaction();
       try {
          Categories::insert(['name' => 'name1']);
          Database::commit();
       }catch (\Exception $e) {
          Database::rollBack();
       } 
    }
}
```
- log sql with Database

```php
<?php
namespace App\Controllers;
use App\Core\BaseController;
use App\Core\Database;use App\Models\Categories;

class HomeController extends BaseController {
    public function __construct()
    {
        $this->model([Categories::class]);
    } 
   
    public function index(){
       Database::enableQueryLog();
       Categories::get();
       log_debug(Database::getQueryLog());
    }
}
```

- log sql with model class

```php
<?php
namespace App\Controllers;
use App\Core\BaseController;
use App\Core\Database;use App\Models\Categories;

class HomeController extends BaseController {
    public function __construct()
    {
        $this->model([Categories::class]);
    } 
   
    public function index(){
       log_debug(Categories::where('id','=',1)->toSqlRaw());
    }
}
```
### Use middleware
- The middleware will be the place to check whether the request goes forward to be processed or not. It will often be used to authenticate the user and many other things depending on how you write the code in the middleware.
- To create middleware you will create it in the middleware folder
- Folder `` middleware/{name}Middleware.php``
=== way 1 ===
```php
<?php
namespace App\Middleware;

use App\Core\Response;
use App\Core\Session;
use App\Core\Request;

class Auth {
    // return with key error code in function
     public function handle(Request $request){
         if(!$request->session('auth')){
            return $request->close('Login does not exit');
         }
         return $request->next();
     }
}
```
=== way 2 ===
```php
<?php
namespace App\Middleware;

use App\Core\Response;
use App\Core\Session;
use App\Core\Request;

class Auth {
    // return with key error code in function
     public function handle(Request $request){
         if(!$request->session('auth')){
            return [
               "error_code" => 1,
               "msg" => "Login does not exit"
            ];
         }
         return [
               "error_code" => 0
         ];
     }
}
```
=== way 3 ===
```php
<?php
namespace App\Middleware;

use App\Core\Response;
use App\Core\Session;
use App\Core\Request;

class Auth {
    // return boolean in function
     public function handle(Request $request){
         if(!$request->session('auth')){
            return false;
         }
         return true;
     }
}
```
- Declare the middleware name in the Kernel.php file located in the middleware folder
```php
<?php
namespace App\Middleware;
class Kernel {
    public $routerMiddleware = [
        "auth" => \App\Middleware\Auth::class,
    ];
}
```
- use middleware trong router 

```php
Router::middleware(['auth'])->group(function (){ // use many middleware
    Router::get('home', [HomeController::class,'index']);
});
// or
Router::middleware('auth')->group(function (){ // use one middleware
    Router::get('home', [HomeController::class,'index']);
});
```