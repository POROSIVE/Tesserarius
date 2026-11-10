<?php
declare(strict_types=1);
$activeSection = $active ?? '';
?>
<div class="relative px-[15px] py-1 w-full flex items-center gap-3 bg-[#ffffff1f] z-50">
  <div class="relative p-[10px] py-2 flex flex-col items-center justify-center rounded-[5px] hover:bg-white/15">
      <h2 class="text-lg text-center font-bold <?= $activeSection === 'home' ? 'text-[rgb(255,164,61)]' : 'text-white' ?>">TSR</h2>
      <a href="<?= e($base_path) ?>" class="link-cover">.</a>
  </div>
  <div class="relative w-[65%] flex flex-wrap items-center gap-[0.8vw]">
    <div class="relative px-[10px] py-2 flex flex-col items-center justify-center rounded-[5px] hover:bg-white/15">
        <h2 class="text-lg text-center font-bold <?= $activeSection === 'library' ? 'text-[rgb(255,164,61)]' : 'text-white' ?>">Browse</h2>
        <a href="<?= e($base_path) ?>/library" class="link-cover">.</a>
    </div>
    <div class="relative px-[10px] py-2 flex flex-col items-center justify-center rounded-[5px] hover:bg-white/15">
        <h2 class="text-lg text-center font-bold <?= $activeSection === 'docs' ? 'text-[rgb(255,164,61)]' : 'text-white' ?>">docs</h2>
        <a href="<?= e($base_path) ?>/docs" class="link-cover">.</a>
    </div>
  </div>
</div>