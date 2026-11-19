<?php
declare(strict_types=1);
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailException;
require_once __DIR__ . "/../../../src/PHPMailer/Exception.php";
require_once __DIR__ . "/../../../src/PHPMailer/PHPMailer.php";
require_once __DIR__ . "/../../../src/PHPMailer/SMTP.php";

if (isset($_SESSION["profile_tag"])) {
  redir_loc("/");
}
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $email     = trim((string) ($_POST["email"] ?? ""));
  $username  = trim((string) ($_POST["username"] ?? ""));
  $password  = (string) ($_POST["password"] ?? "");
  if ($email === "" || $username === "" || $password === "") {
    msg_set("Missing credentials, please try again");
    redir_loc("/auth/register");
  }
  if (mb_strlen($username) > 255 || !preg_match("/^[A-Za-z0-9_.-]+$/", $username)) {
    msg_set("Invalid username");
    redir_loc("/auth/register");
  }
  if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 500) {
    msg_set("Invalid email address");
    redir_loc("/auth/register");
  }
  if (mb_strlen($password) < 8) {
    msg_set("Password too short");
    redir_loc("/auth/register");
  }

  $fetch_existing = DB::query("SELECT username, email FROM account where username = ? OR email = ? LIMIT 1", [$username, $email])->fetch_assoc();
  if ($fetch_existing) {
    if ($fetch_existing["username"] === $username) {
      msg_set("Username taken");
      redir_loc("/auth/register");
    }
    if ($fetch_existing["email"] === $email) {
      msg_set("Account with this email already exist");
      redir_loc("/auth/register");
    }
  }

  $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
  $newProfileTag = $username . bin2hex(random_bytes(8));
  $registeredDate = date("d-m-Y");
  $ownedSoftware = [];
  $ownedBadges = [];
  $preferences = [
    "private"  => false, 
    "themes"  => "0",
  ];
  $eOwnSoft = json_encode($ownedSoftware, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
  $eOwnBadges = json_encode($ownedBadges, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
  $ePref = json_encode($preferences, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
  $autoApprove = strtolower((string) tsr_env("AUTO_APPROVE_USERS", "true")) !== "false";
  $approvalState = $autoApprove ? "approved" : "pending";
  $activationToken = bin2hex(random_bytes(32));
  $hashedActivationToken = $autoApprove ? null : hash("sha256", $activationToken);
  $activationExpiration = $autoApprove ? null : date("Y-m-d H:i:s", time() + 3600);
  try {
    DB::beginTransaction();
    DB::query("INSERT INTO account (profile_tag, username, password, email, approval_state, activation_token_hash, activation_expires) VALUES (?, ?, ?, ?, ?, ?, ?)", [$newProfileTag, $username, $hashedPassword, $email, $approvalState, $hashedActivationToken, $activationExpiration]);
    $account_id = DB::lastId();
    DB::query("INSERT INTO profile (profile_tag, profile_name, profile_desc, profile_join, collection, badges, preferences) VALUES (?, ?, ?, NOW(), ?, ?, ?)", [$newProfileTag, $username, "", $eOwnSoft, $eOwnBadges, $ePref]);
    if (!$autoApprove) {
      $isHTTPS = (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off") || ($_SERVER["SERVER_PORT"] ?? null) == 443 || (strtolower((string) ($_SERVER["HTTP_X_FORWARDED_PROTO"] ?? "")) === "https");
      $scheme = $isHTTPS ? "https" : "http";
      $host = $_SERVER["HTTPS_HOST"] ?? "localhost";
      $activationUrl = $scheme . "://" . $host . $base_path . "/auth/activate?id=" . $account_id . "&token=" . urlencode($activationToken);
      $mail = new PHPMailer(true);
      $mail->isSMTP();
      $mail->Host     = (string) tsr_env("SMTP_HOST", "smtp.example.com");
      $mail->SMTPAuth = true;
      $mail->Username = (string) tsr_env("SMTP_USER", "");
      $mail->Password = (string) tsr_env("SMTP_PASS", "");
      $mail->Port     = (string) tsr_env("SMTP_PORT", 587);
      $mail->SMTPSecure = ($mail->Port === 465) ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
      $mail->Charset  = "UTF-8";
      $mail->setFrom((string) tsr_env("SMTP_FROM", (string) tsr_env("SMTP_USER", "no-reply@localhost")), (string) tsr_env("SMTP_FROM_NAME", "Tesserarius"));
      $mail->addAddress($email, $username);
      $mail->isHtml(true);
      $mail->Subject  = "Activate your account Tesserarius Account"; 
      $mail->Body     = "Hello <b>" . e($username) . "</b>,<br><br>Click the link below to activate your account (valid for 1 hour):<br><a href='" . e($activationUrl) . "'>Activate Account</a>";
      $mail->AltBody  = "Hello " . $username . ",\n\nOpen this link to activate your account (valid for 1 hour):\n" . $activationUrl;
      $mail->send();
    }
    DB::commit();
    if ($autoApprove) {
      msg_set("Account Registered! You Login immidiately");
    } else {
      msg_set("Account created! Please check the your email to activate it");
    }
    redir_loc("auth/login");
  } catch (\Throwable $e) {
    DB::rollback();
    error_log("Error when registrating account: " . $e->getMessage());
    msg_set("Failed to register account");
    redir_loc("/auth/register");
  }
}

$meta["body_class"] = "w-full h-screen flex flex-row bg-def-1 overflow-hidden";
$meta["title"] = "Register | Tesserarius";
?>
<form class="m-auto px-6 py-3 min-w-[400px] w-[40vw] h-full portrait:h-max flex flex-col items-center justify-center gap-2 bg-slate-600 portrait:rounded-md z-[5]" action="<?= e($base_path) ?>/auth/register" method="post" onsubmit="return checkEmail()">
  <?= csrf_field() ?>
  <h1 class="mx-auto text-center text-xl font-extrabold">REGISTER <?= $base_path; ?></h1>
  
  <div class="mx-auto w-[88%] flex flex-col">
    <label for="email">Email</label>
    <input type="text" class="inptxt border-b border-gray-400" id="email" name="email" placeholder="Write a valid email..." minlenght="5" autocomplete="off" tabindex="1" required>
  </div>
  <div class="mx-auto w-[88%] flex flex-col">
    <label for="username">Username</label>
    <input type="text" class="inptxt border-b border-gray-400" id="username" name="username" placeholder="Account username..." autocomplete="off" tabindex="2" required>
  </div>
  <div class="mx-auto w-[88%] flex flex-col">
    <label for="password">Password</label>
    <input type="password" class="inptxt border-b border-gray-400" id="password" name="password" placeholder="Input the correct password..." minlenght="8" autocomplete="off" tabindex="3" required>
  </div>
  <div class="mx-auto w-[88%] flex flex-col">
    <button type="submit" class="p-2 text-lg font-bold bg-yellow-500 hover:bg-yellow-400 rounded-md cursor-pointer" tabindex="5">REGISTER</button>
  </div>
  <div class="mx-auto mt-2 w-[88%] flex flex-col gap-1">
    <p class="text-center">Already have an account? <a href="<?= e($base_path) ?>/auth/login" class="text-[var(--accent)] hover:text-[var(--text)] underline" tabindex="6">Login</a></p>
    <p class="text-center">Having problem registering? <a href="<?= e($base_path) ?>/docs" class="text-[var(--accent)] hover:text-[var(--text)] underline" tabindex="7">Check documentation</a></p>
  </div>
</form>
<div class="relative hidden w-[60vw] h-full md:block text-transparent z-[5]">.</div>

<script>
  function checkEmail() {
    const email = document.getElementById("email");
    if (!email || email.value.indexOf("@") === -1) {
      alerter("Invalid email!");
      return false;
    }
    return true;
  }
</script>