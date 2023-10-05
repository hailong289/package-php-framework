<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?=$code ?? 0?></title>

    <!-- Google font -->
    <link href="https://fonts.googleapis.com/css?family=Quicksand:700" rel="stylesheet">
    <!-- Custom stlylesheet -->
    <link type="text/css" rel="stylesheet" href="<?=URL_PATH.'public/'?>css/style.css" />
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
</head>

<body>

<div id="notfound">
    <div class="notfound">
        <div class="notfound-bg">
            <div></div>
            <div></div>
            <div></div>
        </div>
        <h1><?=$code ?? 0?> </h1>
        <h2><?=$message ?? ''?></h2>
        <div class="notfound-social">
            <p>Line: <?=$line ?? 1?></p>
            <p>File: <?=$file ?? ''?></p>
        </div>
    </div>
</div>

</body>

</html>
