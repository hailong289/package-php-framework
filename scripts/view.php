<?php
$folder = explode('/', $name_view);
if (count($folder) > 1) {
    unset($folder[count($folder) - 1]);
    $folder = implode('/', $folder);
    if (!file_exists(__DIR__ROOT .'/App/Views/'.$folder)) {
        if (!mkdir($concurrentDirectory = __DIR__ROOT . '/App/Views/'.$folder, 0777, true) && !is_dir($concurrentDirectory)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
            exit();
        }
    }
} else {
    $folder = $name_view;
}
$concurrentDirectory = __DIR__ROOT . "/App/Views/$name_view.view.php";
if (!file_exists($concurrentDirectory)) {
    file_put_contents($concurrentDirectory,(<<<HTML
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
HTML), FILE_APPEND);
    if (!file_exists($concurrentDirectory)) {
        throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
    }
    echo "View $name_view create successfully";
} else {
    echo "View $name_view already exist";
}