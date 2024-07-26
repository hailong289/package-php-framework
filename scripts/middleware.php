<?php
namespace Scripts;
class MiddlewareScript extends \Hola\Core\Command
{
    protected $command = 'create:middleware';
    protected $command_description = 'Create a new middleware';
    protected $arguments = [
        'name_middleware'
    ];
    protected $options = [];


    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $name_middleware = $this->getArgument('name_middleware');
        if (strpos($name_middleware, 'Middleware') === false) $name_middleware = $name_middleware . 'Middleware';
        $concurrentDirectory = __DIR__ROOT . "/middleware/$name_middleware.php";
        if (!file_exists($concurrentDirectory)) {
            if (!is_dir(__DIR__ROOT . "/middleware")) {
                mkdir(__DIR__ROOT . "/middleware");
            }
            file_put_contents($concurrentDirectory, '<?php
namespace Hola\Middleware;
use Hola\Core\Request;

class ' . $name_middleware . ' {
     public function handle(Request $request){
         return $request->next();
     }
}
', FILE_APPEND);
            if (!file_exists($concurrentDirectory)) {
                $this->output()->text(sprintf('Directory "%s" was not created', $concurrentDirectory));
                return;
            }
            $this->output()->text("$name_middleware create successfully".PHP_EOL);
        } else {
            $this->output()->text("$name_middleware already exist".PHP_EOL);
        }
    }
}
