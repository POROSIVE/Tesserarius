<?php
declare(strict_types=1);
$profileTag = SessionManager::webSessionVerify();
$fetchProfile = DB::query(
  "SELECT profile.*, account.approval_state FROM profile INNER JOIN account ON profile.profile_tag = account.profile_tag
   WHERE account.approval_state = 'approved' AND profile.profile_tag = ? LIMIT 1", [$profileTag]
)->fetch_assoc();
if (!$fetchProfile) {
  msg_set("Cannot access logged account profile");
  redir_loc("/");
}
save_prev_loc('/settings');
if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["request"] ?? "") !== "") {
  $requests = trim((string) ($_POST["request"] ?? ""));
  $clicked = trim((string) ($_POST["submit"] ?? ""));
  if ($requests === "desc" && $clicked === "Change") {
    $newProfileDesc = trim((string) ($_POST["desc"] ?? ""));
    if ($newProfileDesc === (string) $fetchProfile["profile_desc"]) {
      msg_set("No change detected");
      redir_loc("/settings");
    }
    DB::query("UPDATE profile SET profile_desc = ? WHERE profile_tag = ?", [$newProfileDesc, $profileTag]);
    msg_set("Profile description changed");
    redir_loc("/settings");
  }
  if ($requests === "preferences") {
    $changedprefs = trim((string) ($_POST["changedprefs"] ?? ""));
    $preferences = json_decode((string) $fetchProfile["preferences"], true) ?: [];
    $private = (bool) ($preferences["private"] ?? false);
    $themes = (string) ($preferences["themes"] ?? "0");
    $showcased = (string) ($preferences["showcased"] ?? "");
    $allowInvites = (string) ($preferences["allow_invites"] ?? false);
    $newState = isset($_POST["private"]);
    $newThemes = (string) ($_POST["selectedThemes"] ?? $themes);
    $newShowcased = (string) ($_POST["selectedShowcased"] ?? $showcased);
    $newInvitePref = isset($_POST["allowinvites"]);
    $changes = 0;
    $changed = [];
    if ($newState !== $private) {
      $private = $newState;
      $changed["private"];
      $changes++;
    }
    if ($newThemes !== '' && $newThemes !== $themes) {
      $themes = $newThemes;
      $changed["themes"];
      $changes++;
    }
    if ($newShowcased !== '' && $newShowcased !== $showcased) {
      $showcased = $newShowcased;
      $changed["showcased"];
      $changes++;
    }
    if ($newInvitePref !== $allowInvites) {
      $allowInvites = $newInvitePref;
      $changed["invites"];
      $changes++;
    }
    if ($changes === 0) {
      msg_set("Preferences saved: " . $changed);
      redir_loc("/settings");
    }
    $changed = implode(", ", $changed);
    $updatedPreferences = json_encode([
      "private"       => $private,
      "themes"        => $themes,
      "showcased"     => $showcased,
      "allow_invites" => $allowInvites,
    ], JSON_UNESCAPED_SLASHES);
    DB::query("UPDATE profile SET preferences = ? WHERE profile_tag = ?", [$updatedPreferences, $profileTag]);
    msg_set("Preferences saved: " . $changed);
    redir_loc("/settings");
  }
}

$profilePic = $fetchProfile["pic"];
$profileName = $fetchProfile["profile_name"];
$profileDesc = $fetchProfile["profile_desc"];
$profileJoin = $fetchProfile["profile_join"];
$collection = json_decode((string) $fetchProfile["collection"], true) ?: [];
$badges = json_decode((string) $fetchProfile["badges"], true) ?: [];
$preferences = json_decode((string) $fetchProfile["preferences"], true) ?: [];
$private = (bool) ($preferences["private"] ?? false);
$themes = (string) ($preferences["themes"] ?? "0");
$showcased = (string) ($preferences["showcased"] ?? "");
$allowInvites = (string) ($preferences["allow_invites"] ?? false);

$meta['title'] = 'Settings | Tesserarius';
?>

<?php components('nav', ['base_path' => $base_path, 'active' => 'settings']); ?>
<div class="relative mx-auto w-full min-h-screen flex flex-col bg-default bg-blur z-[15]">
  <div id="settings" class="relative mx-auto my-3 p-4 w-full max-w-[900px] min-h-screen flex flex-col items-center gap-4 bg-default border-x border-[rgb(65,59,78)] rounded-xl shadow-md shadow-[#ffffffb7]">
    <div class="relative mt-[10px] w-full flex flex-col gap-[10px]">
      <div class="relative mx-auto w-[95%] px-4 py-1 flex items-center justify-between">
        <label>Profile Picture</label>
        <a class="relative ml-auto px-4 py-1 min-w-[10vw] text-center bg-default hover:text-[rgb(71,145,255)] hover:border-white border border-[#818181] rounded-[5px] cursor-pointer" onclick="dialogOpen('profilepic');">Change</a>
      </div>
      <div class="relative mx-auto w-[95%] px-4 py-1 flex items-center justify-between">
        <label>Profile Bio</label>
        <a class="relative ml-auto px-4 py-1 min-w-[10vw] text-center bg-default hover:text-[rgb(71,145,255)] hover:border-white border border-[#818181] rounded-[5px] cursor-pointer" onclick="dialogOpen('editdesc'); formLoad(this, 'descForm');" data-bioedits="<?= e($Bios) ?>">Edit</a>
      </div>
      <div class="relative mx-auto w-[95%] px-4 py-1 flex items-center justify-between">
        <label>Themes</label>
        <a class="relative ml-auto px-4 py-1 min-w-[10vw] text-center bg-default hover:text-[rgb(71,145,255)] hover:border-white border border-[#818181] rounded-[5px] cursor-pointer" onclick="dialogOpen('theme');">Change</a>
      </div>
      <div class="relative mx-auto w-[95%] px-4 py-1 flex items-center justify-between">
        <label>Preferences</label>
        <a class="relative ml-auto px-4 py-1 min-w-[10vw] text-center bg-default hover:text-[rgb(71,145,255)] hover:border-white border border-[#818181] rounded-[5px] cursor-pointer" onclick="dialogOpen('preference');">Open</a>
      </div>
      <div class="relative mx-auto w-[95%] px-4 py-1 flex items-center justify-between">
        <label>Invites</label>
        <a class="relative ml-auto px-4 py-1 min-w-[10vw] text-center bg-default hover:text-[rgb(71,145,255)] hover:border-white border border-[#818181] rounded-[5px] cursor-pointer" onclick="dialogOpen('invites');">Open</a>
      </div>
      <div class="relative mx-auto w-[95%] px-4 py-1 flex items-center justify-between">
        <label>Reset Password</label>
        <a class="relative ml-auto px-4 py-1 min-w-[10vw] text-center bg-default hover:text-[rgb(71,145,255)] hover:border-white border border-[#818181] rounded-[5px] cursor-pointer" href="<?= e($base_path) ?>/auth/reset">Reset</a>
      </div>
    </div>
    <form id="preference" class="relative mt-3 mx-auto py-3 w-[95%] hidden flex-col gap-3 bg-default rounded-md" action="<?= e($base_path) ?>/settings" method="post">
      <?= csrf_field() ?>
      <input class="hiddeninp" type="text" name="request" value="preferences" hidden readonly>
      <div class="relative mx-auto pt-[10px] px-[15px] w-full flex justify-between gap-[10px]">
        <label for="private">Private profile data</label>
        <input type="checkbox" name="private" id="private" <?= $private ? 'checked' : '' ?>>
      </div>
      <div class="relative mx-auto pt-[10px] px-[15px] w-full flex justify-between gap-[10px]">
        <label for="allowinvites">Allow invite?</label>
        <input type="checkbox" name="allowinvites" id="allowinvites" <?= $allowInvites === 'active' ? 'checked' : '' ?>>
      </div>
      <div class="relative mx-auto pt-[10px] px-[15px] w-full flex">
        <input class="relative ml-auto py-[5px] px-[15px] min-w-[10vw] text-[15px] font-bold text-black bg-[rgb(232,213,4)] rounded-[5px] cursor-pointer" type="submit" name="submit" value="Update Settings">
      </div>
    </form>
  </div>
</div>