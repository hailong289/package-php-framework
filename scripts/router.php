<?php
namespace Scripts;
use Hola\Core\Router;

class RouterScript extends \Hola\Core\Command
{
    protected $command = 'router:list';
    protected $command_description = 'a list of routes';
    protected $arguments = [];
    protected $options = [];

    public function handle()
    {
        require_once __DIR__ROOT.'/router/index.php';
        $list = Router::list();
        foreach ($list as $item) {
            if($item['method'] === 'GET') {
                $color = 'green';
            } elseif($item['method'] === 'POST') {
                $color = 'yellow';
            } elseif ($item['method'] === 'PUT') {
                $color = 'cyan';
            } elseif ($item['method'] === 'PATCH') {
                $color = '#FF1493';
            } elseif ($item['method'] === 'DELETE') {
                $color = 'red';
            } elseif ($item['method'] === 'OPTIONS') {
                $color = '#9400D3';
            } elseif ($item['method'] === 'HEAD') {
                $color = '#008B8B';
            } else {
                $color = 'green';
            }
            $path = $item['path'];
            $method = $item['method'];
            $actions = $item['action'];
            $controller = $actions[0];
            $method_function = $actions[1];
            $this->output()->writeln("<fg=$color>$method</>   $path <fg=$color>==================</> > $controller->$method_function()");
        }
    }
}
