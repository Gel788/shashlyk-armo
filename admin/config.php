<?php
/* ============================================
   ЗОЛОТАЯ РЫБКА — Admin Panel Config
   ============================================ */

// Учётные данные администратора
// Для смены пароля: php -r "echo password_hash('НовыйПароль', PASSWORD_DEFAULT);"
define('ADMIN_USER', 'admin');
define('ADMIN_PASS_HASH', '$2y$12$Lq/uZnLQcmGFei6t/aVsveSrHpA9LbuKNFwhxUJdLm.wY4da/3T6W'); // Gold2026!

// Пути к файлам
define('MENU_FILE',    __DIR__ . '/../menu-data.json');
define('UPLOADS_DIR',  __DIR__ . '/../uploads/');
define('UPLOADS_URL',  '../uploads/');

// Разрешённые типы изображений
define('ALLOWED_IMG_TYPES', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
define('MAX_IMG_SIZE', 5 * 1024 * 1024); // 5MB

// Сессия
session_name('zr_admin');
session_start();

function isLoggedIn(): bool {
    return !empty($_SESSION['zr_admin_auth']);
}

function requireAuth(): void {
    if (!isLoggedIn()) {
        header('Location: index.php');
        exit;
    }
}

function readMenu(): array {
    if (!file_exists(MENU_FILE)) return ['categories' => []];
    $json = file_get_contents(MENU_FILE);
    return json_decode($json, true) ?? ['categories' => []];
}

function writeMenu(array $data): bool {
    $data['updated'] = date('c');
    $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    return file_put_contents(MENU_FILE, $json, LOCK_EX) !== false;
}

function generateId(string $name): string {
    $id = mb_strtolower($name);
    $id = preg_replace('/[^a-z0-9а-яёa-z]/u', '-', $id);
    $id = preg_replace('/-+/', '-', $id);
    return trim($id, '-') . '-' . substr(md5($name . time()), 0, 6);
}

function jsonResponse(array $data): void {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}
