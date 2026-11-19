<?php
declare(strict_types=1);
$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
$token = trim((string) ($_GET["token"] ?? ""));
if (!$id || $token === "") {
  msg_set("Invalid activation link");
  redir_loc("/");
}
$user = DB::query(
    "SELECT id, approval_state, activation_expires FROM account WHERE id = ? AND activation_token_hash = ? LIMIT 1",
    [(int) $id, $token]
)->fetch_assoc();
if (!$user) {
    msg_set("Account is already activated or token is invalid");
    redir_loc("/auth/login");
}
if (empty($user["activation_expires"]) || strtotime((string) $user["activation_expires"]) < time()) {
    msg_set("Activation link expired");
    redir_loc("/auth/login");
}
DB::query(
    "UPDATE account SET approval_state = 'approved', activation_token_hash = NULL, activation_expires = NULL, activated_at = NOW() WHERE id = ?",
    [$id]
);
msg_set("Activation succeeded! You may now sign in.");
redir_loc("/auth/login");
