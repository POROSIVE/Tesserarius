<?php
declare(strict_types=1);
$profileTag = SessionManager::webSessionVerify();
$opened_id = trim((string) ($params["id"] ?? $_GET["id"] ?? "self"));
if ($opened_id === "" || $opened_id === null) {
  msg_set("Empty id");
  redir_loc("/");
}
$fetchProfile = DB::query(
  "SELECT profile.*, account.approval_state FROM profile INNER JOIN account ON profile.profile_tag = account.profile_tag
   WHERE account.approval_state = 'approved' AND profile.profile_tag = ? LIMIT 1", [$profileTag]
)->fetch_assoc();
if (!$fetchProfile) {
  msg_set("Cannot access profile data");
  redir_loc("/");
}
$profilePic = $fetchProfile["pic"];
$profileName = $fetchProfile["profile_name"];
$profileDesc = $fetchProfile["profile_desc"];
$profileJoin = $fetchProfile["profile_join"];

$meta['title'] = 'Profile '. $profileName .' | Tesserarius';
?>

<?php components('nav', ['base_path' => $base_path, 'active' => '']); ?>
<div class="relative my-3 p-4 w-[92%] min-h-screen flex flex-col items-center gap-4 bg-blur rounded-xl z-[2]">
  <div class="relative p-4 w-full min-h-[35vh] flex flex-col md:flex-row items-center gap-4 bg-slate-500 rounded-xl z-[3]">
    <div class="relative my-auto w-[140px] md:w-[20%] aspect-square flex items-center justify-center z-[4]">
      <?php if ($profilePic === "" || $profilePic === "empty"): ?>
      <img src="<?= public_sls('/icon/person.svg') ?>" alt="<?= e($profileName) ?>" class="relative m-auto aspect-auto h-[80%] flex items-center justify-center bg-half-white object-cover rounded-md z-[4]">
      <?php else: ?>
      <img src="<?= public_sls('/profile/'. $profileTag .'/'. $profilePic) ?>" alt="<?= e($profileName) ?>" class="relative m-auto aspect-auto h-[80%] flex items-center justify-center bg-half-white object-cover rounded-md z-[4]">
      <?php endif; ?>
    </div>
    <div class="relative my-auto p-4 w-full md:w-[50%] h-[80%] flex flex-col z-[4]">
      <h1 class="mt-auto w-full text-3xl font-bold"><?= e($profileName) ?></h1>
      <p class="text-sm"><?= e($profileJoin) ?></p>
      <p class="relative mt-1 py-2 w-full min-h-[12vh] max-h-[20vh] text-sm overflow-y-auto"><?= e($profileDesc) ?></p>
    </div>
    <div class="relative my-auto p-4 w-[30%] h-[80%] flex flex-col gap-2 z-[4]">
      <div class="relative mx-auto w-[95%] py-3 px-4 flex gap-2 bg-default shadow-black border border-[rgb(65,59,78)] rounded-md hover:border-white overflow-hidden z-[5]">
        <div class="my-auto flex flex-col">
          <h2 class="relative text-sm">Badges</h2>
        </div>
        <a href="<?= e($base_path) ?>/settings" class="link-cover">.</a>
      </div>
    </div>
  </div>
</div>