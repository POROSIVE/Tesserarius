<?php
declare(strict_types=1);
$get_msg = msg_get();
?>
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
  <!-- look who's back, it's the good ol' but new alerter -->
  <div id="alertcard">
      <p id="alertcontent"></p>
      <div id="borderanimate"></div>
  </div>
  <script src="<?= script_asset('/alert.js') ?>"></script>
  <?php if ($get_msg !== ''): ?>
  <script>
    document.addEventListener("DOMContentLoaded", function() {
      alerter(<?= json_encode($flashMsg, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>);
    })
  </script>
  <?php endif;?>
</body>
</html>