<?php
/**
 * @var string $lang
 * @var Url $url
 */

use Framework\Foundation\Url;
use function Framework\Http\{url_path, url_for};

?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <link rel="stylesheet" href="<?= url_path('style.css') ?>">
</head>
<body>
<p>Collect: <?= url_for('archive', year: '2025', month: '05', day: '25') ?></p>
<p>Collect: <?= url_for('archive', year: '2025', month: '05') ?></p>
<p>Collect: <?= url_for('archive', year: '2025') ?></p>
<p>Collect: <?= url_for('archive', year: '2025', month: '05') ?></p>
<p>Collect: <?= url_for('archive', year: '2025', month: '05', day: '25') ?></p>
<p>Url: <?= $url->scheme() . '://' . $url->host() . $url->path() ?></p>
<p>Query: <?= $url->query() ?>.</p>
<p>Timer: <?= microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'] ?>.</p>
</body>
</html>