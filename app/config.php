<?php
namespace App;

if (!defined('BASE_PATH')) define('BASE_PATH', __DIR__ . '/..');
if (!defined('ASSETS'))    define('ASSETS', '/assets');
if (!defined('STORAGE'))   define('STORAGE', BASE_PATH . '/storage');

@mkdir(STORAGE, 0777, true);

/** @return \PDO */
function db(): \PDO {
    static $pdo = null;
    if ($pdo) return $pdo;
    $pdo = new \PDO('sqlite:' . STORAGE . '/shorty.sqlite');
    $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS links (
          id TEXT PRIMARY KEY,
          code TEXT UNIQUE NOT NULL,
          url  TEXT NOT NULL,
          clicks INTEGER NOT NULL DEFAULT 0,
          created_at TEXT NOT NULL DEFAULT (datetime('now'))
        );
    ");
    return $pdo;
}

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
}
function csrf_ok(): bool {
    return isset($_POST['_csrf']) && hash_equals($_SESSION['csrf'] ?? '', $_POST['_csrf']);
}

function flash(string $type, string $msg): void { $_SESSION['flash'][] = compact('type','msg'); }
function flashes(): array { $f = $_SESSION['flash'] ?? []; unset($_SESSION['flash']); return $f; }

function render(string $view, array $data = []): void {
    extract($data);
    $viewFile = BASE_PATH . '/views/' . $view . '.php';
    require BASE_PATH . '/views/layout.php';
}
function redirect(string $to = '/'): void { header('Location:' . $to); exit; }
