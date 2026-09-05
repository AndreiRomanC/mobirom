<?php
declare(strict_types=1);

$secure_cookie = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
session_set_cookie_params([
    'httponly' => true,
    'secure' => $secure_cookie,
    'samesite' => 'Strict',
]);
session_start();

header('X-Robots-Tag: noindex, nofollow, noarchive, nosnippet', true);
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: no-referrer');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
header('X-Permitted-Cross-Domain-Policies: none');
header("Content-Security-Policy: default-src 'self'; base-uri 'none'; form-action 'self'; frame-ancestors 'none'; object-src 'none'; script-src 'self'; style-src 'self'");
header('Cache-Control: private, no-store, no-cache, must-revalidate');

const APP_NAME = 'Andivio Workspace';
const APP_PASSWORD = 'Andrei123@'; // Change after upload.
const MAX_UPLOAD_BYTES = 10737418240; // 10 GB
const UPLOAD_DIR = __DIR__ . '/uploads';
const DATA_DIR = __DIR__ . '/data';
const META_FILE = DATA_DIR . '/files.json';

if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
if (!is_dir(DATA_DIR)) mkdir(DATA_DIR, 0755, true);
if (!file_exists(META_FILE)) file_put_contents(META_FILE, "{}\n");

function e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function logged_in(): bool {
    return !empty($_SESSION['andivio_logged_in']);
}

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(24));
    }
    return $_SESSION['csrf'];
}

function verify_csrf(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = (string)($_POST['csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        if (!$token || !hash_equals((string)($_SESSION['csrf'] ?? ''), $token)) {
            http_response_code(403);
            exit('Invalid session token.');
        }
    }
}

function load_meta(): array {
    $json = @file_get_contents(META_FILE);
    $data = json_decode($json ?: '{}', true);
    return is_array($data) ? $data : [];
}

function save_meta(array $meta): void {
    file_put_contents(META_FILE, json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
}

function clean_filename(string $name): string {
    $name = trim($name);
    $name = preg_replace('/[^\pL\pN\.\-\_\s\(\)]/u', '_', $name);
    $name = preg_replace('/\s+/', ' ', $name);
    $name = ltrim($name, '.');
    return $name !== '' ? $name : 'file';
}

function clean_folder(string $folder): string {
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $folder)) {
        return date('Y-m-d');
    }
    return $folder;
}

function unique_path(string $dir, string $filename): string {
    $target = $dir . '/' . $filename;
    if (!file_exists($target)) return $target;

    $info = pathinfo($filename);
    $name = $info['filename'] ?? 'file';
    $ext = isset($info['extension']) ? '.' . $info['extension'] : '';

    for ($i = 2; $i <= 9999; $i++) {
        $candidate = $dir . '/' . $name . ' (' . $i . ')' . $ext;
        if (!file_exists($candidate)) return $candidate;
    }

    return $dir . '/' . $name . '-' . time() . $ext;
}

function relative_path(string $absolute): ?string {
    $base = realpath(UPLOAD_DIR);
    $real = realpath($absolute);
    if (!$base || !$real || strpos($real, $base) !== 0) return null;
    return ltrim(str_replace('\\', '/', substr($real, strlen($base))), '/');
}

function file_id(string $relative): string {
    return hash('sha256', $relative);
}

function absolute_from_relative(string $relative): string {
    $candidate = UPLOAD_DIR . '/' . str_replace(['../', '..\\'], '', $relative);
    $base = realpath(UPLOAD_DIR);
    $real = realpath($candidate);

    if (!$base || !$real || strpos($real, $base) !== 0 || !is_file($real)) {
        http_response_code(404);
        exit('File not found.');
    }

    return $real;
}

function human_size(int $bytes): string {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $value = (float)$bytes;
    $i = 0;
    while ($value >= 1024 && $i < count($units) - 1) {
        $value /= 1024;
        $i++;
    }
    return ($i === 0 ? (string)$bytes : number_format($value, 2)) . ' ' . $units[$i];
}

function icon_label(string $name): string {
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    return match ($ext) {
        'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg' => 'IMG',
        'pdf' => 'PDF',
        'zip', 'rar', '7z' => 'ZIP',
        'doc', 'docx' => 'DOC',
        'xls', 'xlsx', 'csv' => 'XLS',
        'ppt', 'pptx' => 'PPT',
        'mp4', 'mov', 'mkv', 'avi' => 'VID',
        'mp3', 'wav', 'flac' => 'AUD',
        'txt', 'md', 'json' => 'TXT',
        default => strtoupper(substr($ext ?: 'FILE', 0, 4)),
    };
}

function scan_files(): array {
    $meta = load_meta();
    $files = [];

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(UPLOAD_DIR, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        if (!$file->isFile()) continue;

        $relative = relative_path($file->getPathname());
        if (!$relative) continue;

        $id = file_id($relative);
        $folder = dirname($relative);
        if ($folder === '.') $folder = 'No date';

        $files[] = [
            'id' => $id,
            'name' => basename($file->getPathname()),
            'relative' => $relative,
            'folder' => $folder,
            'size' => $file->getSize(),
            'uploaded_at' => (int)($meta[$id]['uploaded_at'] ?? $file->getMTime()),
            'downloads' => (int)($meta[$id]['downloads'] ?? 0),
        ];
    }

    usort($files, fn($a, $b) => $b['uploaded_at'] <=> $a['uploaded_at']);
    return $files;
}

function find_file(string $id): ?array {
    foreach (scan_files() as $file) {
        if (hash_equals($file['id'], $id)) return $file;
    }
    return null;
}

function grouped(array $files): array {
    $groups = [];
    foreach ($files as $file) {
        $groups[$file['folder']][] = $file;
    }
    krsort($groups);
    return $groups;
}

function total_bytes(array $files): int {
    $total = 0;
    foreach ($files as $file) $total += (int)$file['size'];
    return $total;
}

function delete_file(array $file): bool {
    $path = absolute_from_relative($file['relative']);
    if (!@unlink($path)) return false;

    $meta = load_meta();
    unset($meta[$file['id']]);
    save_meta($meta);

    $dir = dirname($path);
    if (is_dir($dir) && realpath($dir) !== realpath(UPLOAD_DIR) && count(scandir($dir)) === 2) {
        @rmdir($dir);
    }

    return true;
}

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

function redirect_flash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    header('Location: index.php');
    exit;
}

$error = '';
$notice = '';

if (!empty($_SESSION['flash']) && is_array($_SESSION['flash'])) {
    if (($_SESSION['flash']['type'] ?? '') === 'error') {
        $error = (string)$_SESSION['flash']['message'];
    } else {
        $notice = (string)$_SESSION['flash']['message'];
    }
    unset($_SESSION['flash']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'login') {
    if (hash_equals(APP_PASSWORD, (string)($_POST['password'] ?? ''))) {
        $_SESSION['andivio_logged_in'] = true;
        csrf_token();
        header('Location: index.php');
        exit;
    }
    $error = 'Parola nu este corectă.';
}

if (!logged_in()) {
?>
<!doctype html>
<html lang="ro">
<head>
    <meta charset="utf-8">
    <meta name="robots" content="noindex,nofollow,noarchive,nosnippet">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e(APP_NAME) ?></title>
    <link rel="stylesheet" href="assets/app.css">
</head>
<body class="login-screen">
    <main class="login-card">
        <div class="logo">A</div>
        <h1><?= e(APP_NAME) ?></h1>
        <p>Spațiu privat pentru materiale și livrări.</p>

        <?php if ($error): ?>
            <div class="notice error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" class="login-form">
            <input type="hidden" name="action" value="login">
            <label for="password">Parolă</label>
            <input id="password" name="password" type="password" autocomplete="current-password" autofocus required>
            <button type="submit">Intră</button>
        </form>
    </main>
</body>
</html>
<?php
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST) && empty($_FILES) && (int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(413);
    echo json_encode([
        'ok' => false,
        'errors' => ['Fișierul depășește limita serverului (post_max_size).'],
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

verify_csrf();

if (($_GET['action'] ?? '') === 'download') {
    $file = find_file((string)($_GET['id'] ?? ''));
    if (!$file) {
        http_response_code(404);
        exit('File not found.');
    }

    $path = absolute_from_relative($file['relative']);
    $meta = load_meta();
    $id = $file['id'];
    $meta[$id]['uploaded_at'] = $file['uploaded_at'];
    $meta[$id]['downloads'] = (int)($meta[$id]['downloads'] ?? 0) + 1;
    save_meta($meta);

    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . addslashes($file['name']) . '"');
    header('Content-Length: ' . filesize($path));
    header('Cache-Control: private, no-store, no-cache, must-revalidate');
    readfile($path);
    exit;
}

if (($_GET['action'] ?? '') === 'download-all') {
    $files = scan_files();
    if (!$files) {
        http_response_code(404);
        exit('Nu există fișiere de descărcat.');
    }

    $temporary = tempnam(sys_get_temp_dir(), 'andivio-');
    $archive = new ZipArchive();
    if (!$temporary || $archive->open($temporary, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        if ($temporary) @unlink($temporary);
        http_response_code(500);
        exit('Arhiva nu a putut fi creată.');
    }

    foreach ($files as $file) {
        $path = absolute_from_relative($file['relative']);
        $archive->addFile($path, $file['relative']);
    }
    $archive->close();

    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . APP_NAME . '-files.zip"');
    header('Content-Length: ' . filesize($temporary));
    header('Cache-Control: private, no-store, no-cache, must-revalidate');
    readfile($temporary);
    @unlink($temporary);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'upload') {
    header('Content-Type: application/json; charset=utf-8');

    $folder = clean_folder((string)($_POST['folder'] ?? date('Y-m-d')));
    $targetDir = UPLOAD_DIR . '/' . $folder;
    if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);

    $meta = load_meta();
    $result = ['ok' => true, 'uploaded' => [], 'errors' => []];

    if (empty($_FILES['files'])) {
        $result['ok'] = false;
        $result['errors'][] = 'Nu am primit fișiere.';
        echo json_encode($result);
        exit;
    }

    $names = $_FILES['files']['name'];
    $tmp = $_FILES['files']['tmp_name'];
    $errors = $_FILES['files']['error'];
    $sizes = $_FILES['files']['size'];

    if (!is_array($names)) {
        $names = [$names];
        $tmp = [$tmp];
        $errors = [$errors];
        $sizes = [$sizes];
    }

    foreach ($names as $i => $name) {
        if (($errors[$i] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $result['errors'][] = $name . ': upload eșuat.';
            continue;
        }

        $size = (int)($sizes[$i] ?? 0);
        if ($size <= 0) {
            $result['errors'][] = $name . ': fișier gol.';
            continue;
        }

        if ($size > MAX_UPLOAD_BYTES) {
            $result['errors'][] = $name . ': depășește limita de 10 GB.';
            continue;
        }

        $safe = clean_filename((string)$name);
        $target = unique_path($targetDir, $safe);

        if (!move_uploaded_file((string)$tmp[$i], $target)) {
            $result['errors'][] = $name . ': nu a putut fi salvat.';
            continue;
        }

        $relative = relative_path($target);
        if ($relative) {
            $id = file_id($relative);
            $meta[$id] = [
                'uploaded_at' => time(),
                'downloads' => 0,
            ];
            $result['uploaded'][] = basename($target);
        }
    }

    save_meta($meta);

    if (!$result['uploaded']) {
        $result['ok'] = false;
    }

    echo json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $file = find_file((string)($_POST['id'] ?? ''));
    if ($file) {
        if (delete_file($file)) {
            redirect_flash('success', 'Fișierul a fost șters.');
        }
        redirect_flash('error', 'Fișierul nu a putut fi șters.');
    }
    redirect_flash('error', 'Fișierul nu a fost găsit.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete-selected') {
    $selected_ids = $_POST['selected_ids'] ?? [];
    $selected_ids = is_array($selected_ids) ? array_unique(array_map('strval', $selected_ids)) : [];
    $deleted = 0;

    foreach ($selected_ids as $selected_id) {
        $file = find_file($selected_id);
        if ($file && delete_file($file)) $deleted++;
    }

    redirect_flash(
        $deleted ? 'success' : 'error',
        $deleted
            ? $deleted . ' fișiere selectate au fost șterse.'
            : 'Selectează cel puțin un fișier.'
    );
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete-all') {
    $files = scan_files();
    $deleted = 0;
    foreach ($files as $file) {
        if (delete_file($file)) $deleted++;
    }

    if ($deleted === count($files)) {
        redirect_flash('success', $deleted ? $deleted . ' fișiere au fost șterse.' : 'Nu există fișiere de șters.');
    }
    redirect_flash('error', 'Au fost șterse ' . $deleted . ' din ' . count($files) . ' fișiere.');
}

$q = trim((string)($_GET['q'] ?? ''));
$files = scan_files();

if ($q !== '') {
    $files = array_values(array_filter($files, function ($file) use ($q) {
        return stripos($file['name'], $q) !== false || stripos($file['folder'], $q) !== false;
    }));
}

$groups = grouped($files);
$total = count($files);
$storage = total_bytes($files);
$today = date('Y-m-d');
?>
<!doctype html>
<html lang="ro">
<head>
    <meta charset="utf-8">
    <meta name="robots" content="noindex,nofollow,noarchive,nosnippet">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e(APP_NAME) ?></title>
    <link rel="stylesheet" href="assets/app.css">
</head>
<body>
    <div class="app">
        <header class="header">
            <div class="identity">
                <div class="logo">A</div>
                <div>
                    <h1><?= e(APP_NAME) ?></h1>
                    <p><?= e($total . ' fișiere · ' . human_size($storage)) ?></p>
                </div>
            </div>

            <div class="header-actions">
                <form method="get" class="search">
                    <input name="q" value="<?= e($q) ?>" type="search" placeholder="Caută rapid...">
                </form>
                <?php if ($total): ?>
                    <a href="?action=download-all" class="ghost">Download all</a>
                    <form id="bulkDeleteForm" method="post" class="bulk-delete-form" data-count="<?= (int)$total ?>">
                        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                        <input type="hidden" name="action" value="delete-selected">
                        <button type="submit" class="ghost danger-action" disabled>Delete selected</button>
                    </form>
                    <form method="post" class="confirm-delete-all" data-count="<?= (int)$total ?>">
                        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                        <input type="hidden" name="action" value="delete-all">
                        <button type="submit" class="ghost danger-action">Delete all</button>
                    </form>
                <?php endif; ?>
                <a href="?logout=1" class="ghost">Logout</a>
            </div>
        </header>

        <?php if ($notice): ?>
            <div class="notice success"><?= e($notice) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="notice error"><?= e($error) ?></div>
        <?php endif; ?>

        <section class="upload-card">
            <div>
                <h2>Adaugă fișiere</h2>
                <p>Drag & drop sau selectare manuală. Salvare automată în folderul zilei.</p>
            </div>

            <form id="uploadForm" class="upload-form" enctype="multipart/form-data">
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="action" value="upload">
                <input type="hidden" name="folder" value="<?= e($today) ?>">
                <input id="fileInput" name="files[]" type="file" multiple>

                <label for="fileInput" id="dropzone" class="dropzone">
                    <span class="drop-icon">+</span>
                    <strong>Trage fișierele aici</strong>
                    <em>sau apasă pentru upload · max. 10 GB / fișier</em>
                </label>

                <div id="queue" class="queue" aria-live="polite"></div>

                <div class="upload-actions">
                    <button id="uploadButton" type="submit">Încarcă</button>
                    <span id="uploadStatus">Folder: <?= e($today) ?></span>
                </div>
            </form>
        </section>

        <main class="content">
            <?php if (!$files): ?>
                <section class="empty">
                    <div class="logo">A</div>
                    <h2>Niciun fișier încă.</h2>
                    <p>Adaugă fișiere prin drag & drop, iar ele vor apărea automat pe ziua curentă.</p>
                </section>
            <?php endif; ?>

            <?php foreach ($groups as $folder => $items): ?>
                <section class="folder">
                    <div class="folder-title">
                        <h2><?= e($folder) ?></h2>
                        <span><?= count($items) ?> fișiere · <?= e(human_size(total_bytes($items))) ?></span>
                    </div>

                    <div class="files">
                        <?php foreach ($items as $file): ?>
                            <article class="file">
                                <input class="file-select" type="checkbox" name="selected_ids[]" value="<?= e($file['id']) ?>" form="bulkDeleteForm" aria-label="Selectează <?= e($file['name']) ?>">
                                <div class="type"><?= e(icon_label($file['name'])) ?></div>
                                <div class="file-main">
                                    <strong><?= e($file['name']) ?></strong>
                                    <span><?= e(human_size((int)$file['size'])) ?> · <?= e(date('d M Y, H:i', (int)$file['uploaded_at'])) ?> · <?= e((string)$file['downloads']) ?> descărcări</span>
                                </div>
                                <div class="file-actions">
                                    <a href="?action=download&id=<?= e($file['id']) ?>" class="download">Download</a>
                                    <form method="post" class="confirm-delete-file">
                                        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= e($file['id']) ?>">
                                        <button type="submit" class="delete">Șterge</button>
                                    </form>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endforeach; ?>
        </main>
    </div>

<script src="assets/app.js"></script>
</body>
</html>
