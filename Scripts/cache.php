<?php
namespace Hola\Scripts;
class CacheScript extends \Hola\Core\Command
{
    protected $command = 'clear:cache';
    protected $command_description = 'clear cache';
    protected $arguments = [];
    protected $options = [];

    public function handle()
    {
        $cache = rglob(__DIR__ROOT.'/storage/cache/*.cache');
        if (!empty($cache)) {
            foreach($cache as $item){
                if(file_exists($item)){
                    unlink($item);
                }
            }
        }
        $this->output()->text("Clear cache successfully");
    }
}