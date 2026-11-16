<?php
declare(strict_types=1);
if (session_status() !== PHP_SESSION_ACTIVE) {
  $isHTTPS = (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off") || ($_SERVER["SERVER_PORT"] ?? null) == 443 || (strtolower((string) ($_SERVER["HTTP_X_FORWARDED_PROTO"] ?? "")) === "https");
  session_start([
    "name" => "TSR_SESSID",
    "use_strict_mode" => 1,
    "use_only_cookies" => 1,
    "cookie_httponly" => true,
    "cookie_secure" => $isHTTPS,
    "cookie_samesite" => "Lax",
    "sid_length" => 48,
    "sid_bits_per_character" => 6,
  ]);
}
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: SAMEORIGIN");
header("Referrer-Policy: strict-origin-when-cross-origin");
// the .env loader
function tsr_env(?string $key = null, mixed $default = null): mixed {
  static $env = null;
  if ($env == null) {
    $envFile = __DIR__ . "/.env";
    $env = is_file($envFile) ? (parse_ini_file($envFile) ?: []) : [];
  }
  if ($key == null) {
    return $env;
  }
  return $env[$key] ?? $default;
}

// auto detecting base path
$detectedBase = trim((string) tsr_env("BASE_PATH", ""));
if ($detectedBase !== "") {
  $base_path = "/" . trim($detectedBase, "/");
} else {
  $script_path = str_replace("\\", "/", dirname($_SERVER["SCRIPT_NAME"] ?? "/index.php"));
  $base_path = ($script_path === "/" || $script_path === ".") ? "" : rtrim($script_path, "/");
}

// scoped database wrapper
final class DB {
  private static ?\mysqli $conn = null;
  public static function connect(): \mysqli {
    if (self::$conn !== null) {
      return self::$conn;
    }
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    try {
      self::$conn = new \mysqli(
        (string) tsr_env("DB_HOST", "localhost"),
        (string) tsr_env("DB_USER", "root"),
        (string) tsr_env("DB_PASS", ""),
        (string) tsr_env("DB_NAME", "tesserarius")
      );
      self::$conn->set_charset("utf8mb4");
      return self::$conn;
    } catch (\Throwable $e) {
      error_log("Failed db connection: " . $e->getMessage());
      http_response_code(500);
      exit("Internal server error.");
    }
  }
  // query helper
  public static function query(string $sql, array $params = []): \mysqli_result|bool {
    return self::connect()->execute_query($sql, $params);
  }
}
// escapes CSRF 
function e(?string $value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
}
function csrf_token(): string {
  if (empty($_SESSION["_csrf"])) {
     $_SESSION["_csrf"] = bin2hex(random_bytes(32));
  }
  return $_SESSION["_csrf"];
}
function csrf_field(): string {
  return "<input type='hidden' name='_csrf' value='" . e(csrf_token()) . "'>";
}
function csrf_verify(): void {
  $method = $_SERVER["REQUEST_METHOD"] ?? "GET";
  if (in_array($method, ["POST", "PUT", "PATCH", "DELETE"], true)) {
    $token = $_POST["_csrf"] ?? $_SERVER["HTTP_X_CSRF_TOKEN"] ?? "";
    if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
      http_response_code(403);
      exit("Invalid CSRF token");
    }
  }
}
// components
function components(string $name, array $props = []): void {
  if (!preg_match("/^[a-zA-Z0-9_\/-]+$/", $name)) {
    throw new \InvalidArgumentException("Invalid component name.");
  }
  $baseDir = realpath(__DIR__ . "/components");
  $target  = realpath(__DIR__ . "/components/" . $name . ".php");
  if (!$baseDir || !$target || !str_starts_with($target, $baseDir . DIRECTORY_SEPARATOR)) {
    throw new \RuntimeException("Component not found: {$name}");
  }
  (static function (string $__file, array $__props): void {
    extract($__props, EXTR_SKIP);
    require $__file;
  })($target, $props);
}

// the equivalent of alerter on cgcc
function msg_set(string $message): void {
  $_SESSION["temp_msg"] = $message;
}
function msg_get(): string {
  $temp_msg = (string) ($_SESSION["temp_msg"] ?? "");
  $_SESSION["temp_msg"] = "";
  return $temp_msg;
}

// navigator & redirects, supposedly for cleaner and safer way of doing it instead of header+exit.
function redir_loc(string $path): never {
  global $base_path;
  if (str_starts_with($path, "http://") || str_starts_with($path, "https://")) {
    header("Location: " . $path);
    exit;
  }
  $clean = "/" . ltrim($path, "/");
  header("Location: " . $base_path . $clean);
  exit;
} 

function save_prev_loc(string $path): void {
  $_SESSION["prev_loc"] = ltrim($path, "/");
}
function get_prev_loc(string $default = ""): string {
  $loc = (string) ($_SESSION["prev_loc"] ?? $default);
  if ($loc === "" || str_contains($loc, "\r") || str_contains($loc, "\n") || str_contains($loc, "://") || str_contains($loc, "..")) {
    return $default;
    }
  return ltrim($loc, "/");
}

function get_current_time(int $offsetSeconds = 0): string {
  return date("Y-m-d");
}
function get_unix(?string $rawDate, bool $eod = false): int {
  $val = trim((string) $rawDate);
  if ($val === "" || in_array(strtolower($val), ["unset", "notset", "0000-00-00 00:00:00"], true)) {
    return 0;
  }
  if (ctype_digit($val)) {
    return (int) ($val);
  }
  $utc = new \DateTimeZone("UTC");
  $formats = [
    "Y-m-d H:i:s" => false, "Y-m-d H:i" => false, "Y/m/d H:i:s" => false, "Y/m/d H:i" => false,
    "Y-m-d"       => true,  "Y/m/d"     => true,  "d/m/Y H:i:s" => false, "d/m/Y H:i" => false,
    "d-m-Y H:i:s" => false, "d-m-Y H:i" => false, "d/m/Y"       => true,  "d-m-Y"     => true,
  ];
  foreach ($formats as $format => $isDateOnly) {
    $dt = \DateTimeImmutable::creteFromFormat("!" . $format, $val, $utc);
    if ($dt !== false & $dt->format($format) === $val) {
      return ($isDateOnly && $endOfDayIfDateOnly ? $dt->setTime(23, 59, 59) : $dt)->getTimestamp();
    }
  }
  return strtotime($val) ?: 0;
}
function reformat_date(?string $rawDate, string $format = "d-m-Y H:i:s", string $fallback = "Never"): string {
  $ts = get_unix($rawDate);
  return $ts > 0 ? date($format, $ts) : $fallback;
}

// session manager class
final class SessionManager {
  public const MAX_SESSIONS             = 3;
  public const SESSION_TTL              = 15 * 86400;
  public const SESSIONS_EXTEND_THRESHOLD= 7 * 86400;
  public const SESSIONS_EXTENDS_COOLDOWN= 60;
  public const REVERIFY_INTERVALS       = 60;
  public const HEARTBEAT_COOLDOWN       = 60;
  public const ONLINE_WINDOW            = 120;

  public static function setCookie(string $token, int $expiration): void {
    global $base_path;
    $isHTTPS = (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off") || ($_SERVER["SERVER_PORT"] ?? null) == 443 || (strtolower((string) ($_SERVER["HTTP_X_FORWARDED_PROTO"] ?? "")) === "https");
    $cookiePath = ($base_path !== "") ? $base_path . "/" : "/";
    setcookie("session_token", $token, [
      "expires"  => $expiration,
      "path"     => $cookiePath,
      "secure"   => $isHTTPS,
      "httponly" => true,
      "samesite" => "Lax",
    ]);
  }
  public static function clearCookie(): void {
    global $base_path;
    $cookiePath = ($base_path !== "") ? $base_path . "/" : "/";
    unset($_SESSION["profile_tag"], $_SESSION["_session_token"], $_SESSION["_session_verified_at"], $_SESSION["org_session_token"], $_SESSION["org_id"], $_SESSION["org_role"], $_SESSION["reset_password_required"]);
    unset($_COOKIE["session_token"], $_COOKIE["_session_token"], $_COOKIE["_session_verified_at"]);
    setcookie("session_token", "", [
      "expires"  => 1,
      "path"     => $cookiePath,
      "httponly" => true,
      "samesite" => "Lax",
    ]);
    if ($cookiePath !== "/") {
      setcookie("session_token", "", [
        "expires"  => 1,
        "path"     => $cookiePath,
        "httponly" => true,
        "samesite" => "Lax",
      ]);
    }
  }

  public static function purgeExpired(?string $profile_tag = null): void {
    $sqlNow = get_current_time();
    $tsNow = time();
    if ($profile_tag !== null) {
      return;
    }
    DB::query("DELETE FROM session_logs WHERE expiration_date LIKE \x27____-__-__%\x27 AND expiration_date <= ?", [$sqlNow]);
    DB::query("DELETE FROM org_session_logs WHERE expiration_date LIKE \x27____-__-__%\x27 AND expiration_date <= ?", [$sqlNow]);
  }
  public static function getSessionCount(string $profile_tag): int {
    self::purgeExpired($profile_tag);
    $fetch_count = DB::query("SELECT COUNT(*) AS total FROM session_logs WHERE profile_tag = ? AND expiration_date > ?", [$profile_tag, get_current_time()])->fetch_assoc();
    return (int) ($fetch_count["total"] ?? 0);
  }

  public static function createSession(
    string $profile_tag,
    string $osid = "Browser",
    string $clientType = "web",
    ?string $ipAddress = null,
    int $ttl = self::SESSION_TTL,
    bool $setCookie = false
  ): array {
    if (self::getSessionCount($profile_tag) >= self::MAX_SESSIONS) {
      return [
        "ok"      => false,
        "token"   => "",
        "expires_at"    => "",
        "expires_unix"  => "",
        "token"   => "Your account exceed maximum active sessions allowed(" . self::MAX_SESSIONS . ")",
      ];
    }
    $token      = bin2hex(random_bytes(32));
    $unixNow    = time();
    $unixExpires= $unixNow + $ttl;
    $sqlNow     = date("Y-m-d H:i:s", $unixNow);
    $sqlExpires = date("Y-m-d H:i:s", $unixExpires);
    DB::query(
      "INSERT INTO session_logs (profile_tag, session_token, osid, logged_address, client_type, expiration_date, last_logged, is_running_clts_software, last_logged_clts) VALUES (?, ?, ?, ?, ?, ?, ?, 0, \x27None\x27)",
      [
        $profile_tag,
        $token,
        $ipAddress ?? "0.0.0.0",
        mb_substr(trim($osid) ?: "unknown", 0, 100),
        mb_substr(trim($clientType) ?: "unknown", 0, 200),
        $sqlExpires,
        $sqlNow,
      ]
    );
    if ($setCookie) {
      self::setCookie($token, $unixExpires);
      $_SESSION["_session_token"]     = $token;
      $_SESSION["_session_verified_at"]     = $unixNow;
    }
    return ["ok" => true, "token" => $token, "expires_at" => $sqlExpires, "expires_unix" => $unixExpires, "error" => null];
  }

  // the web session, handling internally
  public static function webSessionDestroy(): void {
    $profileTag = $_SESSION["profile_tag"] ?? null;
    $cookieToken = $_COOKIE["session_token"] ?? ($_SESSION["_session_token"] ?? null);
    if (!empty($cookieToken) && !empty($profileTag)) {
      DB::query("DELETE FROM session_logs WHERE session_token = ? AND profile_tag = ?", [(string) $cookieToken, (string) $profileTag]);
    }
    if (!empty($_SESSION["org_session_token"])) {
      DB::query("DELETE FROM org_session_logs WHERE org_session_token = ? ", [(string) $_SESSION["org_session_token"]]);
    }
    self::clearCookie();
  }
  public static function webSessionVerify(): ?string {
    $unixNow = time();
    $cookieToken = trim((string) ($_COOKIE["session_token"] ?? ""));
    $profileTag = trim((string) ($_SESSION["profile_tag"] ?? ""));
    if ($profileTag !== "") {
      if (($unixNow - (int) ($_SESSION["_session_verified_at"] ?? 0)) < self::REVERIFY_INTERVALS) {
        return $profileTag;
      }
      $fetchUser = DB::query("SELECT profile_tag FROM account WHERE profile_tag = ? AND approval_state = \x27approved\x27 LIMIT 1", [$profileTag])->fetch_assoc();
      if (!$fetchUser) {
        self::webSessionDestroy();
        msg_set("Cannot find logged account.");
        return null;
      }
      $session_token = (string) ($_SESSION["session_token"] ?? $cookieToken);
      if ($session_token !== "") {
        $fetchSession = DB::query("SELECT id, expiration_date, last_logged FROM session_logs WHERE session_token = ? AND profile_tag = ?", [$session_token, $profileTag])->fetch_assoc();
        if (!$fetchSession) {
          self::webSessionDestroy();
          msg_set("Session does not exists anymore, please login again");
          return null;
        }
        $unixExpires = get_unix((string) $fetchSession["expiration_date"], true);
        if ($unixExpires <= $unixNow) {
          DB::query("DELETE FROM session_logs WHERE id = ?", [(int) $fetchSession["id"]]);
          self::webSessionDestroy();
          msg_set("Session expired, please login again");
          return null;
        }
        self::extendsSession((int) ($fetchSession["id"]), $session_token, $unixExpires, (string) $fetchSession["last_logged"], true);
      }
      $_SESSION["_session_verified_at"] = $unixNow;
      return $profileTag;
    }
    // cookie check if there's actually cookie
    if ($cookieToken === "") {
      return null;
    }
    $fetchCookieSession = DB::query("SELECT id, profile_tag, expiration_date, last_logged FROM session_logs WHERE session_token = ? LIMIT 1", [$cookieToken])->fetch_assoc();
    if (!$fetchCookieSession) {
      self::clearCookie();
      msg_set("Session does not exists");
      return null;
    }
    $unixExpires = get_unix((string) $fetchCookieSession["expiration_date"], true);
    if ($unixExpires <= $unixNow) {
      DB::query("DELETE FROM session_logs WHERE id = ?", [(int) $fetchCookieSession["id"]]);
      self::clearCookie();
      msg_set("Session expired");
      return null;
    }
    $fetchCookieUser = DB::query("SELECT profile_tag FROM account WHERE profile_tag = ? AND approval_state = \x27approved\x27 LIMIT 1", [$fetchCookieSession["profile_tag"]])->fetch_assoc();
    if (!$fetchCookieUser) {
      DB::query("DELETE FROM session_logs WHERE id = ?", [(int) $fetchCookieSession["id"]]);
      self::clearCookie();
      msg_set("WHO. ARE. YOU.");
      return null;
    }
    session_regenerate_id(true);
    self::extendsSession((int) ($fetchCookieSession["id"]), $cookieToken, $unixExpires, (string) $fetchCookieSession["last_logged"], true);
    $_SESSION["profile_tag"] = (string) $fetchCookieUser["profile_tag"];
    $_SESSION["profile_tag"] = $cookieToken;
    $_SESSION["_session_verified_at"] = $unixNow;
    return (string) $_SESSION["profile_tag"];
  }

  public static function auth_required(string $redirect = "auth/login"): string {
    $profile_tag = self::webSessionVerify();
    if ($profile_tag === null) {
      msg_set("login to access");
      redir_loc($redirect);
    }
    return $profile_tag;
  }

  private static function extendsSession(int $sesslogId, string $token, int $unixExpires, string $rawLastLogged, bool $refreshCookie = false): void {
    $unixNow = time();
    $lastUnix = get_unix($rawLastLogged);
    $sqlNow = date("Y-m-d H:i:s", $unixNow);
    if (($unixExpires - $unixNow) < self::SESSIONS_EXTEND_THRESHOLD) {
      $newUnixExpires = $unixNow + self::SESSION_TTL;
      DB::query("UPDATE session_logs SET expiration_date = ?, last_logged = ? WHERE id = ?", [date("Y-m-d H:i:s", $newUnixExpires), $sqlNow, $sesslogId]);
      if ($refreshCookie) {
        self::setCookie($token, $newUnixExpires);
      }
      return;
    }
    if (!preg_match("/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/", $rawLastLogged) || ($unixExpires - $unixNow) >= self::SESSIONS_EXTENDS_COOLDOWN) {
      DB::query("UPDATE session_logs SET expiration_date = ?, last_logged = ? WHERE id = ?", [date("Y-m-d H:i:s", $unixExpires), $sqlNow, $sesslogId]);
    }
  }
}

// temporary for test, made for the page requiring authenticated visitor
// function auth_required(string $redirect = "auth/login"): string {
//   if (!empty($_SESSION["profile_tag"])) {
//     return (string) $_SESSION["profile_tag"];
//   }
//   $profile_tag = $_SESSION["profile_tag"];
//   if ($profile_tag === null) {
//     msg_set("login to access");
//     redir_loc($redirect);
//   }
//   return $profile_tag;
// }

?>