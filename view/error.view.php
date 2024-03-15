<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= $code ?? 500 ?> Error Page </title>
    <style>
        * {
            font-family: sans-serif;
            color: rgba(0,0,0,0.75);
        }
        body {
            margin: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            height: 100vh;
            padding: 0px 30px;
            background: #ddd;
        }

        .wrapper {
            max-width: 960px;
            width: 100%;
            margin: 30px auto;
            transform: scale(0.8);
        }
        .landing-page {
            max-width: 960px;
            height: 475px;
            margin: 0;
            box-shadow: 0px 0px 8px 1px #ccc;
            background: #fafafa;
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        h1 {
            font-size: 48px;
            margin: 0;
        }
        p {
            font-size: 16px;
            width: 35%;
            margin-bottom: 5px;
            text-align: center;
        }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="landing-page">
        <div style="text-align:center;" class="icon__download"></div>
        <h1> <?= $code ?? 500 ?></h1>
        <p>Message: <?= $message ?? '' ?></p>
        <p>Line: <?= $line ?? '' ?></p>
        <p>File:  <?= $file ?? '' ?></p>
        <p>Trace:  <?= $trace ?? '' ?></p>
    </div>
</div>
</body>
</html>

