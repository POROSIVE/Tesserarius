<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="<?= e($base_path) ?>/global.css">
  <title><?= e($meta['title'] ?? 'Tesserarius') ?></title>
</head>
<body class="w-full min-h-screen overflow-x-hidden">
  <?= $children;?>
</body>
</html>