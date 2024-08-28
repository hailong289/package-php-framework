<?php
namespace Hola\Scripts;
class CacheScript extends \Hola\Core\Command
{
    protected $command = 'clear:cache';
    protected $command_description = 'clear cache';
    protected $arguments = ["?type"];
    protected $options = [];

    public function handle()
    {
        $type = $this->getArgument('type');
        switch ($type) {
            case 'router':
                $item = __DIR__ROOT.'/storage/cache/router.cache';
                if(file_exists($item)){ 
                    unlink($item);
                }
                break;
            case 'config':
                $item = __DIR__ROOT.'/storage/cache/config.cache';
                if(file_exists($item)){
                    unlink($item);
                }
                break;
            case 'view':
                $item = __DIR__ROOT.'/storage/render/views';
                if(is_dir($item)){
                    $cache = rglob("$item/*");
                    foreach($cache as $v){
                        if(file_exists($v)){
                            unlink($v);
                        }
                    }
                }
                break;
            default:
                $cache = rglob(__DIR__ROOT.'/storage/cache/*.cache');
                if (!empty($cache)) {
                    foreach($cache as $item){
                        if(file_exists($item)){
                            unlink($item);
                        }
                    }
                }
                break;
        }
        $this->output()->text("Clear cache successfully");
    }
}