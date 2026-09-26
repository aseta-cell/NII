<?php
// ===========================================
// Journal Management System
// config.php
//
// Real server settings (DB password, domain, prices, bank details)
// go into config.local.php — copy config.local.example.php and edit it.
// config.local.php is not stored in git.
// ===========================================

$settings = [
    // Database
    "db_host" => "localhost",
    "db_name" => "journal",
    "db_user" => "root",
    "db_pass" => "",

    // Site
    "site_name" => "Academia Institute",
    "site_url"  => "http://localhost/NII",
    "debug"     => true,

    // Registration access codes for staff roles
    "reviewer_code" => "CHANGE-ME-REVIEWER",
    "editor_code"   => "CHANGE-ME-EDITOR",

    // Journals: page file => journal name (used for submissions, archive filter, likes, comments)
    "journals" => [
        "ai.php"           => "International Journal of Artificial Intelligence",
        "architecture.php" => "International Journal of Architecture",
        "economics.php"    => "International Journal of Economics",
        "education.php"    => "International Journal of Education",
        "energy.php"       => "International Journal of Energy Research",
        "engineering.php"  => "International Journal of Engineering & Applied Physics",
        "environment.php"  => "International Journal of Environmental Science",
        "medicine.php"     => "International Journal of Medicine",
    ],

    // Article Processing Charge plans (same as the pricing table on about.html).
    // "amount" => null means "contact us" — the plan is shown but cannot be paid online.
    "currency" => "USD",
    "plans" => [
        "author"        => ["name" => "Author",        "amount" => 149,  "link" => ""],
        "professional"  => ["name" => "Professional",  "amount" => 499,  "link" => ""],
        "institutional" => ["name" => "Institutional", "amount" => null, "link" => "institutional.html"],
    ],

    // Patent applications
    "patent_fee" => 99,          // in "currency"; free for special-access accounts
    "patent_prefix" => "NII-PAT", // application number: NII-PAT-2026-00001
    "patent_types" => [
        "invention"         => ["kz" => "Өнертабыс",             "ru" => "Изобретение",            "en" => "Invention"],
        "utility_model"     => ["kz" => "Пайдалы модель",        "ru" => "Полезная модель",        "en" => "Utility model"],
        "industrial_design" => ["kz" => "Өнеркәсіптік үлгі",     "ru" => "Промышленный образец",   "en" => "Industrial design"],
    ],

    // Special (family) access: whoever enters this password gets everything free.
    // Only the bcrypt hash is stored here, never the password itself.
    // To change the password: php -r 'echo password_hash("NEW-PASSWORD", PASSWORD_DEFAULT);'
    // and put the result into config.local.php as "free_access_hash".
    "free_access_hash" => '$2y$12$J2VcUAsWmBzHJPknmMFAkOc1cZoC5R8ooUMjulpn/fhUu.B5yDjJi',

    // Shown to the author on the payment page (bank transfer / Kaspi)
    "payment_instructions" => "Kaspi (Kaspi Gold / перевод по номеру): +7 771 473 18 52\nЛюбой банк Казахстана (Halyk, Freedom, Jusan, Forte и др.) — перевод по номеру телефона: +7 771 473 18 52\nСумма в долларах оплачивается в тенге по курсу на день оплаты.\nВ комментарии к переводу укажите номер платежа / заявки, затем загрузите чек ниже.\n\nKaspi / кез келген банк — телефон нөмірі бойынша аудару: +7 771 473 18 52\nPay by Kaspi or any Kazakhstan bank transfer to phone number +7 771 473 18 52 (in KZT at the current rate), then upload the receipt.",
];

if (file_exists(__DIR__ . "/config.local.php")) {
    $settings = array_replace($settings, require __DIR__ . "/config.local.php");
}

if ($settings["debug"]) {
    ini_set("display_errors", "1");
    error_reporting(E_ALL);
} else {
    ini_set("display_errors", "0");
}

// Database connection
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli(
        $settings["db_host"],
        $settings["db_user"],
        $settings["db_pass"],
        $settings["db_name"]
    );
    $conn->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    die($settings["debug"]
        ? "Database connection failed: " . $e->getMessage()
        : "Database connection failed. Please try again later.");
}

// Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Website settings
$site_name = $settings["site_name"];
$site_url = rtrim($settings["site_url"], "/");

// Upload folder (relative to the site root)
$upload_dir = "uploads/";

date_default_timezone_set("Asia/Almaty");


// ===========================================
// Helpers
// ===========================================

function e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

function redirect($url)
{
    header("Location: " . $url);
    exit();
}

function require_login()
{
    if (!isset($_SESSION["user_id"])) {
        redirect("signin.php");
    }
}

// Loads the current user from the DB and checks the role.
function require_role($role)
{
    global $conn;

    require_login();

    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION["user_id"]);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if (!$user || $user["role"] !== $role) {
        http_response_code(403);
        die("Access denied");
    }

    return $user;
}

// Sends the user back to the page they came from (same site only).
function redirect_back($fallback, $anchor = "")
{
    $referer = $_SERVER["HTTP_REFERER"] ?? "";
    $host = parse_url($referer, PHP_URL_HOST);
    $current_host = explode(":", $_SERVER["HTTP_HOST"] ?? "")[0];

    if ($referer !== "" && $host === $current_host) {
        $url = strtok($referer, "#");
    } else {
        $url = $fallback;
    }

    redirect($url . $anchor);
}

// Saves an uploaded file into uploads/ and returns the stored file name,
// or null when nothing valid was uploaded.
function save_upload($field, array $allowed_ext, $max_mb = 20)
{
    global $upload_dir;

    if (!isset($_FILES[$field]) || $_FILES[$field]["error"] !== UPLOAD_ERR_OK) {
        return null;
    }

    $file = $_FILES[$field];
    $ext = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed_ext, true) || $file["size"] > $max_mb * 1024 * 1024) {
        return null;
    }

    $dir = __DIR__ . "/" . $upload_dir;

    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    // Uploaded files must never run as code (Apache; nginx is set up in DEPLOY.md)
    if (!file_exists($dir . ".htaccess")) {
        file_put_contents($dir . ".htaccess", implode("\n", [
            '<FilesMatch "\\.(php|phtml|phar|php\\d|pl|py|cgi|sh)$">',
            "    Require all denied",
            "</FilesMatch>",
            "Options -Indexes",
            "",
        ]));
    }

    $name = date("YmdHis") . "_" . bin2hex(random_bytes(6)) . "." . $ext;

    if (!move_uploaded_file($file["tmp_name"], $dir . $name)) {
        return null;
    }

    return $name;
}

function upload_url($filename)
{
    global $upload_dir;

    return $upload_dir . rawurlencode((string)$filename);
}

function money($amount)
{
    global $settings;

    return number_format((float)$amount, 2) . " " . $settings["currency"];
}

// True when the entered code is the special (family) access password.
function is_free_access_code($code)
{
    global $settings;

    return $code !== "" && password_verify($code, $settings["free_access_hash"]);
}

// Gives the user free access: all fees are waived.
function grant_free_access($user_id)
{
    global $conn;

    $stmt = $conn->prepare("UPDATE users SET free_access = 1 WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
}

// Closes any open payment and marks the article as paid with a free (0) payment.
function waive_fee($submission_id, $user_id)
{
    global $conn, $settings;

    $stmt = $conn->prepare("
        UPDATE payments SET status = 'Rejected'
        WHERE submission_id = ? AND status IN ('Pending', 'Awaiting Confirmation')
    ");
    $stmt->bind_param("i", $submission_id);
    $stmt->execute();

    $currency = $settings["currency"];

    $stmt = $conn->prepare("
        INSERT INTO payments (user_id, submission_id, plan, amount, currency, method, status, paid_at)
        VALUES (?, ?, 'free', 0, ?, 'Special access', 'Paid', NOW())
    ");
    $stmt->bind_param("iis", $user_id, $submission_id, $currency);
    $stmt->execute();

    $stmt = $conn->prepare("UPDATE submissions SET status = 'Paid' WHERE id = ?");
    $stmt->bind_param("i", $submission_id);
    $stmt->execute();
}
