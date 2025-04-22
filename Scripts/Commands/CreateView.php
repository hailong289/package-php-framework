<?php

namespace Hola\Scripts\Commands;

use Hola\Core\Command;

class CreateView extends Command {
    protected $command = 'create:view';
    protected $command_description = 'Create a new view';
    protected $arguments = ['arg_view'];
    protected $options = [];

    public function handle()
    {
        $viewName = $this->getArgument('arg_view');
        $concurrentDirectory = __DIR__ROOT . "/App/Views/$viewName.view.php";
        if (file_exists($concurrentDirectory)) {
            $this->output()->text("View $viewName already exist");
            return false;
        }
        file_put_contents($concurrentDirectory, $this->rawContent(), FILE_APPEND);
        if (!file_exists($concurrentDirectory)) {
            $this->output()->text(sprintf('Directory "%s" was not created', $concurrentDirectory));
        }
        $this->output()->text("View $viewName create successfully");
    }

    public function getFullDirController($classView)
    {
        $folder = explode('/', $classView);
        if (count($folder) > 1) {
            unset($folder[count($folder) - 1]);
            $folder = implode('/', $folder);
            if (!file_exists(__DIR__ROOT . '/App/Views/' . $folder)) {
                if (!mkdir($concurrentDirectory = __DIR__ROOT . '/App/Views/' . $folder, 0777, true) && !is_dir($concurrentDirectory)) {
                    $this->output()->text(sprintf('Directory "%s" was not created', $concurrentDirectory));
                    return;
                }
            }
        } else {
            $folder = $classView;
        }
    }

    public function rawContent()
    {
        return  (<<<HTML
<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    view
</body>
</html>
HTML);
    }
}