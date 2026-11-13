<?php
declare(strict_types=1);
$profile_tag = auth_required();
save_prev_loc('/profile');

$meta['title'] = 'Profile | Tesserarius';
?>

<?php components('nav', ['base_path' => $base_path, 'active' => 'profile']); ?>
<div class="m-auto p-4 w-1/3 min-h-1/3 flex flex-col items-center justify-center bg-slate-500 rounded-xl">
  <h1 class="text-2xl">ULTRA SECRET PROFILE PAGE</h1>
  <p class="text-xl">for test currently, nothing here really</p>
</div>