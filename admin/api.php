<?php
require_once __DIR__ . '/config.php';
requireAuth();

$action = $_REQUEST['action'] ?? '';

// ============================================================
// GET handlers
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($action === 'get_item') {
        $menu = readMenu();
        $ci = (int)($_GET['ci'] ?? 0);
        $ii = (int)($_GET['ii'] ?? 0);
        jsonResponse($menu['categories'][$ci]['items'][$ii] ?? []);
    }
    if ($action === 'get_drink') {
        $menu = readMenu();
        $catId = $_GET['cat_id'] ?? '';
        $dci = (int)($_GET['dci'] ?? 0);
        $dii = (int)($_GET['dii'] ?? 0);
        foreach ($menu['categories'] as $cat) {
            if ($cat['id'] === $catId) {
                jsonResponse($cat['drink_categories'][$dci]['items'][$dii] ?? []);
            }
        }
        jsonResponse(['error' => 'Not found']);
    }
    jsonResponse(['error' => 'Unknown GET action']);
}

// ============================================================
// POST handlers
// ============================================================

// ---- Save regular menu item ----
if ($action === 'save_item') {
    $menu = readMenu();
    $ci   = (int)($_POST['ci'] ?? 0);
    $ii   = (int)($_POST['ii'] ?? -1);

    $item = [
        'id'          => trim($_POST['id'] ?? ''),
        'name'        => trim($_POST['name'] ?? ''),
        'price'       => (int)($_POST['price'] ?? 0),
        'weight'      => trim($_POST['weight'] ?? ''),
        'calories'    => trim($_POST['calories'] ?? ''),
        'protein'     => trim($_POST['protein'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'badge'       => trim($_POST['badge'] ?? ''),
        'badge_text'  => trim($_POST['badge_text'] ?? ''),
        'image'       => trim($_POST['image'] ?? ''),
        'visible'     => true,
    ];

    if (empty($item['name'])) jsonResponse(['success' => false, 'error' => 'Название обязательно']);
    if (empty($item['id'])) $item['id'] = generateId($item['name']);

    if ($ii >= 0) {
        $menu['categories'][$ci]['items'][$ii] = $item;
    } else {
        $menu['categories'][$ci]['items'][] = $item;
    }

    writeMenu($menu) ? jsonResponse(['success' => true]) : jsonResponse(['success' => false, 'error' => 'Не удалось записать файл']);
}

// ---- Delete item ----
if ($action === 'delete_item') {
    $menu = readMenu();
    $ci = (int)($_POST['ci'] ?? 0);
    $ii = (int)($_POST['ii'] ?? -1);
    if ($ii >= 0) {
        array_splice($menu['categories'][$ci]['items'], $ii, 1);
        writeMenu($menu) ? jsonResponse(['success' => true]) : jsonResponse(['success' => false, 'error' => 'Write error']);
    }
    jsonResponse(['success' => false, 'error' => 'Invalid index']);
}

// ---- Toggle item visibility ----
if ($action === 'toggle_visible') {
    $menu = readMenu();
    $ci = (int)($_POST['ci'] ?? 0);
    $ii = (int)($_POST['ii'] ?? -1);
    if ($ii >= 0) {
        $cur = $menu['categories'][$ci]['items'][$ii]['visible'] ?? true;
        $menu['categories'][$ci]['items'][$ii]['visible'] = !$cur;
        writeMenu($menu);
        jsonResponse(['success' => true, 'visible' => !$cur]);
    }
    jsonResponse(['success' => false]);
}

// ---- Save drink ----
if ($action === 'save_drink') {
    $menu   = readMenu();
    $catId  = trim($_POST['cat_id'] ?? '');
    $dci    = (int)($_POST['dci'] ?? 0);
    $dii    = (int)($_POST['dii'] ?? -1);

    $drink = [
        'id'      => generateId($_POST['name'] ?? 'drink'),
        'name'    => trim($_POST['name'] ?? ''),
        'volume'  => trim($_POST['volume'] ?? ''),
        'price'   => (int)($_POST['price'] ?? 0),
        'visible' => true,
    ];

    foreach ($menu['categories'] as &$cat) {
        if ($cat['id'] === $catId) {
            if ($dii >= 0) {
                $drink['id'] = $cat['drink_categories'][$dci]['items'][$dii]['id'] ?? $drink['id'];
                $cat['drink_categories'][$dci]['items'][$dii] = $drink;
            } else {
                $cat['drink_categories'][$dci]['items'][] = $drink;
            }
            break;
        }
    }

    writeMenu($menu) ? jsonResponse(['success' => true]) : jsonResponse(['success' => false, 'error' => 'Write error']);
}

// ---- Toggle drink visibility ----
if ($action === 'toggle_drink_visible') {
    $menu  = readMenu();
    $catId = trim($_POST['cat_id'] ?? '');
    $dci   = (int)($_POST['dci'] ?? 0);
    $dii   = (int)($_POST['dii'] ?? -1);

    foreach ($menu['categories'] as &$cat) {
        if ($cat['id'] === $catId && $dii >= 0) {
            $cur = $cat['drink_categories'][$dci]['items'][$dii]['visible'] ?? true;
            $cat['drink_categories'][$dci]['items'][$dii]['visible'] = !$cur;
            writeMenu($menu);
            jsonResponse(['success' => true, 'visible' => !$cur]);
        }
    }
    jsonResponse(['success' => false]);
}

// ---- Upload image ----
if ($action === 'upload_image') {
    if (empty($_FILES['photo'])) jsonResponse(['success' => false, 'error' => 'Файл не передан']);

    $file = $_FILES['photo'];
    if ($file['error'] !== UPLOAD_ERR_OK) jsonResponse(['success' => false, 'error' => 'Ошибка загрузки файла']);
    if ($file['size'] > MAX_IMG_SIZE) jsonResponse(['success' => false, 'error' => 'Файл слишком большой (макс 5 МБ)']);

    // Detect MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    if (!in_array($mime, ALLOWED_IMG_TYPES)) jsonResponse(['success' => false, 'error' => 'Неверный тип файла. Только JPG, PNG, WEBP']);

    $ext    = pathinfo($file['name'], PATHINFO_EXTENSION) ?: 'jpg';
    $ci     = (int)($_POST['ci'] ?? 0);
    $menu   = readMenu();
    $catId  = $menu['categories'][$ci]['id'] ?? 'items';

    $dir = UPLOADS_DIR . $catId . '/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);

    $filename = uniqid() . '.' . strtolower($ext);
    $dest     = $dir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $dest)) jsonResponse(['success' => false, 'error' => 'Не удалось сохранить файл']);

    $path = 'uploads/' . $catId . '/' . $filename;
    jsonResponse(['success' => true, 'path' => $path]);
}

jsonResponse(['success' => false, 'error' => 'Unknown action: ' . htmlspecialchars($action)]);
