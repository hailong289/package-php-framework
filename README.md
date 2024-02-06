# PHP framework



## Getting started

```
composer create-project longdhdev/holaframework
```
```
composer install
```

## Router 
- Set up in router/web.php 
- Router will receive 2 parameters, 1st parameter will be url, 2nd parameter will be array including controller and function in controller

```php
use App\Core\Router;
use App\Controllers\HomeController;

Router::get('/', [HomeController::class,'index']);
Router::get('/home', [HomeController::class,'index']);
```
- Use parameters
```php
use App\Core\Router;
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
- When you want to create another router file, you can create it in the router folder and then add the code below in router/index.php
- The ``add()`` function will identify your path and the ``loadFile()`` function will load the router file you just created

```php
$configRouter->add('api')->loadFile('api'); // https://domain.com/api
$configRouter->add('api_v2')->loadFile('api_v2'); // https://domain.com/api_2
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
- Request and parameters in controller

```php
<?php
namespace App\Controllers;
use App\Core\Request;

class HomeController extends BaseController {
    public function index(Request $request, $id){
        echo $id;
    }
    public function detail(Request $request, $id){
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
- Run command create controller
```cmd
- cd scripts
- php controller.php create:NameController
// or
- php controller.php create:folder/folder/NameController
```
### Use model

- Create model 
- To create a function in the model, make it a static function
- The variable $times_auto set true when you use the `create()`,`insert()`, `insertLastId()` function will automatically create a date with the `date_created` column and using the `update()` function will create a date with the `date_updated` column set false, you can turn this function off
- You can change the 2 default date columns with the `$date_create` and `$date_update` variables
- You may not need to declare the 3 variables `$times_auto`, `$date_create`, `$date_update` if you do not use them.
- Run command create controller
```cmd
- cd scripts
- php model.php create:nameModel --table=name
```
```php
<?php
namespace App\Models;
use App\Core\Model;

class Categories extends Model {
    protected static $tableName = 'categories';
//    protected static $times_auto = false;
//    protected static $date_create = "date_created";
//    protected static $date_update = "date_updated";
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
         $data = $this->Categories::get()->values();
    }
    public function listProduct(){
         $data = $this->Product::get()->values();
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
         $data = Categories::get()->values();
    }
    public function listProduct(){
         $data = Product::get()->values();
         return $data;
    }
}
```
Use attribute in module
- Attribute in the model will include set and get functions. For example, if you want to set a column name in the table, the set and get functions will have the structure setAttribute{column_table} and getAttribute{column_table}
- Example code below:
```php
namespace App\Models;
use App\Core\Database;
use App\Core\Model;

class Categories extends Model {
    protected static $tableName = 'categories';
    protected static $times_auto = false;
    protected static $date_create = "date_created";
    protected static $date_update = "date_update";
    protected static $field = [
        'name',
        'view',
        'invalid'
    ];

    public function setAttributeName($value) {
        return json_encode($value);
    }

    public function getAttributeName($value) {
        return json_decode($value);
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
use App\Core\BaseController;
use App\Core\Request;

class HomeController extends BaseController {
    public function __construct()
    {}

    public function index(){
        return $this->render_view('name_file', ["title" => "Home"]);
    }
}
```
- Run command create view
```cmd
- cd scripts
- php view.php create:name_view
// or
- php view.php create:folder/folder/name_view
```

### Use Request
```php
use App\Core\Request;
class Controller extends BaseController {
    public function __construct()
    {}
    public function index(Request $request){
         $name = $request->get('name');
         $name = $request->name; // or
         $name = $request->value('name');
         $file = $request->get_file('file');
         $set_file = $request->file('file'); // use set file
         $extension = $request->extension(); // get extension file
         $size = $request->size(); // get size file
         $type = $request->type(); // get type file
         $originName = $request->originName(); // get originName file
         $isFile = $request->isFile(); // check file
         $all = $request->all(); // get all data request
         $session = $request->session('name'); // get session 
         $cookie = $request->cookie('name'); // get cookie 
         $cookie = $request->headers('name'); // get headers 
         $has = $request->has('name'); // check key 
    }
}
```
### Use validate request
=== way 1 ===
```php
       $validate = Validation::create(
            $request->all(),
            [
                'username' => [
                    'required',
                    'number',
                ],
                'password' => [
                    'required',
                    'number',
                    'max:6',
                    'min:6'
                ],
            ]
       );

```
=== way 2 ===
```php
       $validate = Validation::create(
            [
                'username' => $request->username,
                'password' => $request->password
            ],
            [
                'username' => [
                    'required',
                    'number',
                ],
                'password' => [
                    'required',
                    'number',
                    'max:6',
                    'min:6'
                ],
            ]
       );
```
You can return your error message with the code below
```php
       $validate = Validation::create(
            [
                'username' => $request->username,
                'password' => $request->password
            ],
            [
                'username' => [
                    'required' => 'Username is required',
                    'number' => 'username is number',
                ],
                'password' => [
                    'required',
                    'number',
                    'max:6',
                    'min:6'
                ],
            ]
       );
```
Use with regex
```php
      Validation::create([
            'username' => 'long'
        ], [
            'username' => [
                'pattern:/([0-9]+)/'=> 'You must be a number',
            ]
      ]);
```
Use ``validateRequest`` in controller. This ``validateRequest`` function will return an error if there are coder-identified fields. If the condition is met, the ``validateRequest`` function will return the data that the user submitted
```php
<?php
namespace App\Controllers;
use App\Core\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validation;

class HomeController extends BaseController {
    public function __construct()
    {}

    public function index(Request $request){
        $validate = Validation::create(
            $request->all(),
            [
                'username' => [
                    'required'=>'Không để trống',
                    'number',
                ],
                'password' => [
                    'required',
                    'number',
                    'max:6' => 'Pass phải lớn hơn hoặc bằng {{password}}',
                    'min:6'
                ],
            ]
        );
        if(!empty($validate->errors())) { // show error
            return false;
        }
        $data = $validate->data(); // get data access
        
        return [
           'data_request' => $data
        ];
    }

}
```
#### Rules can be used

- ``required`` // check empty
- ``number``  // check number
- ``max`` // check max
- ``min`` // check min
- ``pattern``// check with regex
- ``boolean`` // check boolean
- ``array`` // check array
- ``date`` // check date
- ``not_pattern`` // check with regex
### Use response

```php
use App\Core\Response;
class Controller extends BaseController {
    public function __construct()
    {}
    public function index(Request $request){
        $data = [];
        return Response::view('name_view', $data);
    }
    
    public function json(Request $request){
        $data = [];
        return Response::json($data, $status ?? 200);
    }
    public function redirectTo(Request $request){
        $data = [];
        return Response::redirectTo('/login');
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
   Categories::first()->values();
```

- Get one record buy column with function where
```sql
   Categories::where('id','=', 1)->first()->values(); // get by id
   Categories::where('name','=', 1)->first()->values(); // get by name
   Categories::where('name','like', '%value%')->first()->values(); // get by name
```
- Get all record in model
```sql
   Categories::get()->values();
```
- Get all record buy column with function where
 
  ``The get() function will return an object. If you want to return an array data type, you can use the getArray() function.``
```sql
   Categories::where('id','=', 1)->get()->values(); // get by id
   Categories::where('name','=', 1)->get()->values(); // get by name
   Categories::where('name','like', '%value%')->get()->values(); // get by name
       
   // return data type array
   Categories::where('id','=', 1)->getArray(); // get by id
   Categories::where('name','=', 1)->getArray(); // get by name
   Categories::where('name','like', '%value%')->getArray(); // get by name
   // version 1.0.6
   Categories::where('id','=', 1)->get()->toArray(); // get by id
   Categories::where('name','=', 1)->get()->toArray(); // get by name
   Categories::where('name','like', '%value%')->get()->toArray(); // get by name
```
```php
    $data1 = Categories::select('*')->where(function (Database $q){
        $q->where('id',3)->orWhere('id',2);
    })->get()->toArray();
    
    $data2 = Categories::select('*')->where(function (Database $q){
        $q->where('id',3);
    })->orWhere(function (Database $q){
        $q->where('id',2);
    })->get()->toArray();
```
- use select()
```sql
   Categories::select('*')->get()->values();
   Categories::select(['*'])->get()->values();
   Categories::select(['id','name'])->get()->values();

   // with sum and count 
   Summary::select([
       'SUM(amount) as amount',
       'SUM(amount2) as amount2',
   ])->get()->values();
   Region::select([
       'COUNT(id) as number'
   ])->get()->values();
```
- use findById()

```sql
   Categories::findById(1); 
```
- use orWhere()
```sql
   Categories::where('id','=', 1)->orWhere('id','=',2)->get()->values(); 
```
- use whereLike()
```sql
   Categories::whereLike('name', '%long')->get()->values(); 
   Categories::whereLike('name', 'long%')->get()->values(); 
   Categories::whereLike('name', '%long%')->get()->values(); 
```

- use orWhereLike()
```sql
   Categories::orWhereLike('name', '%long')->get()->values(); 
   Categories::orWhereLike('name', 'long%')->get()->values(); 
   Categories::orWhereLike('name', '%long%')->get()->values(); 
```
- use whereIn()
```sql
   Categories::whereIn('id', [1,2])->get()->values(); 
```
- use orWhereIn()
```sql
   Categories::orWhereIn('id', [1,2])->get()->values(); 
```
- use whereNotIn()
```sql
   Categories::whereNotIn('id', [1,2])->get()->values(); 
```
- use orWhereNotIn()
```sql
   Categories::orWhereNotIn('name', [1,2])->get()->values(); 
```
- use whereBetween()
```sql
   Categories::whereBetween('date', ['2023-01-01 00:00:01','2023-12-31 23:59:59'])->get()->values(); 
```
- use whereRaw()
```sql
   Categories::whereRaw('id = 1 and age = 18')->get()->values(); 
```
- use orWhereRaw()
```sql
   Categories::where('id', 1)->orWhereRaw('id = 2')->get()->values(); 
```
- use join

```php
   // way 1
   Blog::select('*')->join('categories', function ($q) {
      $q->on('categories.id','=','category_blogs.category_id');
   })->get()->values(); 
   // way 2
   Blog::select('*')->join('categories')->on('categories.id','=','category_blogs.category_id')->get()->values(); 
```

- use left join

```php
   // way 1
   Blog::select('*')->leftJoin('categories', function ($q) {
      $q->on('categories.id','=','category_blogs.category_id');
   })->get()->values(); 
   // way 2
   Blog::select('*')->leftJoin('categories')->on('categories.id','=','category_blogs.category_id')->get()->values(); 
```


- use right join

```php
   // way 1
   Blog::select('*')->rightJoin('categories', function ($q) {
      $q->on('categories.id','=','category_blogs.category_id');
   })->get()->values(); 
   // way 2
   Blog::select('*')->rightJoin('categories')->on('categories.id','=','category_blogs.category_id')->get()->values(); 
```

- use order by

```php
   News::select('*')->orderBy('id', 'DESC')->get()->values(); // ASC, DESC
```

- use group by

```php
   // way 1
   News::select('*')->groupBy('id')->get()->values(); 
   // way 2
   News::select('*')->groupBy(['field1','field2','field3'])->get()->values();
```

- use limit

```php
   News::select('*')->limit(100)->get()->values();
```

- use limit and offset

```sql
   News::select('*')->page(0)->limit(100)->get()->values(); // offset 0 limit 100
   News::select('*')->page(1)->limit(100)->get()->values(); // offset 100 limit 100
   News::select('*')->page(2)->limit(100)->get()->values(); // offset 200 limit 100
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
   News::custom("SELECT * FROM news WHERE id = 1")->get()->values();
   News::custom("SELECT * FROM news")->get()->values();
```
- Or you can use pure SQL statements with the database class like the example below
```php
  $database = new Database();
  $database->query("SELECT * FROM categories")->fetch();
  $database->query("SELECT * FROM categories")->fetchAll();
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
        $all = Database::table('categories')->get()->values();
        $first = Database::table('categories')->where('id','=',1)->first()->values();
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
       Categories::get()->values();
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
- use union
```php
    Categories::union_all(Categories::clone())->get()->values();
    /*
     * SELECT * FROM categories 
     * UNION ALL
     * SELECT * FROM categories
     * */
    Categories::union(Categories::clone())->get()->values();
    /*
     * SELECT * FROM categories 
     * UNION 
     * SELECT * FROM categories
     */
```

- use subquery
```php
    Categories::subQuery(Categories::clone(), 'sub')->get()->values();
    /*
     * SELECT * FROM (SELECT * FROM categories) as sub 
     */
    Categories::subQuery(Categories::select('id')->clone(), 'sub')->get()->values();
     /*
     * SELECT * FROM (SELECT id FROM categories) as sub 
     */
```

- use collection
- With collections you can use functions ``toArray``, ``toObject``, ``values``, ``value``, ``count``, ``map``, ``filter``, ``push``, ``add`` to manipulate the query builder
```php
    Categories::get()->values(); // get all value
    Categories::get()->toArray(); // get all value type array
    Categories::get()->toObject(); // get all value type object
    Categories::get()->value(); // get first value, use with map and filter functions
    Categories::get()->count(); // Count the amount of data
    Categories::get()->map(function ($item) {
       return $item->id;
    })->values(); // Map the data
    Categories::get()->filter(function ($item) {
       return $item->id === 1;
    })->values(); // filter data

    Categories::get()->map(function ($item) {
       return $item->id;
    })->value(); // Map the data and get one
    Categories::get()->filter(function ($item) {
       return $item->id === 1;
    })->value(); // filter data and get one
```

- use collection with array
```php
    $array = [
       ["id" => 1, "name" => "Name 1"],
       ["id" => 2, "name" => "Name 2"],
    ];
    $data = new \App\Core\Collection($array);
    $data->values(); // get all value
    $data->toArray(); // get all value type array
    $data->toObject(); // get all value type object
    $data->value(); // get first value, use with map and filter functions
    $data->count(); // Count the amount of data
    $data->map(function ($item) {
       return $item->id;
    })->values(); // Map the data and get all
    $data->filter(function ($item) {
       return $item->id === 1;
    })->values(); // filter data and get all
    $data->map(function ($item) {
       return $item->id;
    })->value(); // Map the data and get one
    $data->filter(function ($item) {
       return $item->id === 1;
    })->value(); // filter data and get one
```
### Use middleware
- The middleware will be the place to check whether the request goes forward to be processed or not. It will often be used to authenticate the user and many other things depending on how you write the code in the middleware.
- To create middleware you will create it in the middleware folder
- Folder `` middleware/{name}Middleware.php``
- Run command create middleware
```cmd
cd scripts
php middleware.php create:Test
```
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
### Use function
- Use ``convert_to_array``
- The ``convert_to_array`` function will convert object data to an array
```php
<?php 
     $data = new stdClass();
     $data->name = 'Long';
     $data->age = '22';
     $data = convert_to_array($data);   
      /* return
       * Array
        (
            [name] => Long
            [age] => 22
        )
       * */
?>
```

- Use ``convert_to_object``
- The ``convert_to_object`` function will convert array data to an object
```php
<?php 
      $data = array();
      $data['name'] = 'Long';
      $data['age'] = '22';
      $data = convert_to_object($data);
      /* return
       * stdClass Object
        (
            [name] => Long
            [age] => 22
        )
       * */
?>
```
### Use translate
- When you want to create a translation, you can create a file in the language folder and write the language conversion key in the file as shown below.
- Create files ``vi.php``
- You can change the language in the config/constant.php file
```php
define('LANGUAGE', 'vi');
```
```php
<?php

return [
    "home" => "Trang chủ",
    "login" => "Đăng nhập"
];
```
- Create files ``ja.php``
```php
<?php

return [
    "home" => "家",
    "login" => "サインイン"
];
```
- After creating and adding the key in the file you can convert the language based on the key with the function ``__()``, ``translate()``
```php
<?php
echo __('home');
echo translate('home');
```
- You can change the language with the 3rd parameter in the ``__()`` and ``translate()`` functions
```php
<?php
echo __('home', [], 'vi'); // print Trang chủ
echo __('home', [], 'ja'); // print 家
echo translate('home', [], 'vi'); // print Trang chủ
echo translate('home', [], 'ja'); // print 家
```
- Identifies the key in the translation file
```php
<?php

return [
    "number" => "Total: {{value}}"
];
```
```php
<?php

echo __('number', ['value' => 10]); // print Total: 10
```