<?php
declare(strict_types=1);
if (isset($_SESSION['profile_tag'])) {
  redir_loc('/');
}
// It's better to be on the same page for login post type check
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim((string) ($_POST['username'] ?? ''));
  $password = (string) ($_POST['password'] ?? '');
  if ($username === '' || $password === '') {
    msg_set('Missing credentials, please try again');
    redir_loc('/auth/login');
  }
  $fetch_user = DB::query('SELECT profile_tag, password, approval_state FROM account WHERE username = ? LIMIT 1', [$username])->fetch_assoc();
  if (!$fetch_user) {
    msg_set("Invalid username");
    redir_loc('/auth/login');
  }
  if ($fetch_user['approval_state'] !== 'approved') {
    msg_set("The account is currently inaccessible");
    redir_loc('/auth/login');
  }
  $storedPass = (string) $fetch_user['password'];
  if (!password_verify($password, $storedPass)) {
    msg_set("Invalid password");
    redir_loc('/auth/login');
  }

  $previous_location = get_prev_loc('');
  if (!isset($_POST['session_persisted'])) {
    $_SESSION['profile_tag'] = (string) $fetch_user['profile_tag'];
    msg_set('Login SUCCESS');
    redir_loc('/' . $previous_location);
  }
}


$meta['body_class'] = 'w-full h-screen flex flex-row bg-def-1 overflow-hidden';
$meta['title'] = 'Login | Tesserarius';
?>
<form class="m-auto px-6 py-3 min-w-[400px] w-[40vw] h-full portrait:h-max flex flex-col items-center justify-center gap-2 bg-slate-600 portrait:rounded-md z-[5]" action="<?= e($base_path) ?>/auth/login" method="post">
  <?= csrf_field() ?>
  <h1 class="mx-auto text-center text-xl font-extrabold">LOGIN</h1>
  
  <div class="mx-auto w-[88%] flex flex-col">
    <label for="username">Username</label>
    <input type="text" class="inptxt border-b border-gray-400" id="username" name="username" placeholder="Account username..." autocomplete="off" tabindex="1" required>
  </div>
  <div class="mx-auto w-[88%] flex flex-col">
    <label for="password">Password</label>
    <input type="password" class="inptxt border-b border-gray-400" id="password" name="password" placeholder="Input the correct password..." minlenght="8" autocomplete="off" tabindex="2" required>
  </div>
  <div class="mx-auto w-[88%] flex items-center gap-2">
    <input type="checkbox" class="cursor-pointer" id="session_persisted" name="session_persisted" placeholder="Input the correct password..." minlenght="8" autocomplete="off" tabindex="3">
    <label for="session_persisted" class="cursor-pointer">Keep me signed in</label>
  </div>
  <div class="mx-auto w-[88%] flex flex-col">
    <button type="submit" class="p-2 text-lg font-bold bg-yellow-500 hover:bg-yellow-400 rounded-md cursor-pointer" tabindex="4">LOGIN</button>
  </div>
  <div class="mx-auto mt-2 w-[88%] flex flex-col gap-1">
    <p class="text-center">Don't have an account? <a href="<?= e($base_path) ?>/auth/register" class="text-[var(--accent)] hover:text-[var(--text)] underline" tabindex="5">Register here</a></p>
    <p class="text-center">Having problem logging in? <a href="<?= e($base_path) ?>/docs" class="text-[var(--accent)] hover:text-[var(--text)] underline" tabindex="5">Check documentation</a></p>
  </div>
</form>
<div class="relative hidden w-[60vw] h-full md:block text-transparent z-[5]">.</div>