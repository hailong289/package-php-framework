<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Oops! Something is wrong </title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@longdh2/hola-framework@1.0.0/main.css">
</head>
<body class="hl-page-error">
<div class="error-container">
    <div class="error-code"><?= $code ?? 500 ?></div>
    <div class="error-summary"><?= $message ?? '' ?></div>
    <div class="error-location">
        <?php if (isset($file)): ?>
            <strong>File: <strong><?= $file ?? '' ?> </strong> </strong>
        <?php endif; ?>
        <?php if (isset($line)): ?>
            <strong>Line: <strong> <?= $line ?? '' ?> </strong> </strong>
        <?php endif; ?>
    </div>
    <?php if (isset($trace)): ?>
        <div class="stack-trace">
            Trace: <?= $trace ?? '' ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>

