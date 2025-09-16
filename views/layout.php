<?php /** layout */ ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>PHP Shorty</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="<?= ASSETS ?>/style.css" rel="stylesheet">
</head>
<body>
  <main class="container">
    <?php require $viewFile; ?>
  </main>
</body>
</html>
