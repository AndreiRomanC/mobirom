<?php

function load_content(string $page): array {
    $file = CONTENT_DIR . '/' . $page . '.json';
    if (!file_exists($file)) return [];
    $data = json_decode(file_get_contents($file), true);
    return is_array($data) ? $data : [];
}

function save_content(string $page, array $data): bool {
    $file = CONTENT_DIR . '/' . $page . '.json';
    return file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false;
}

function e(?string $s): string {
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}

function url(string $path = ''): string {
    return BASE_PATH . '/' . ltrim($path, '/');
}

function asset(string $path): string {
    return BASE_PATH . '/assets/' . $path;
}

function c(array $data, string $key, string $fallback = ''): string {
    return $data[$key] ?? $fallback;
}

function is_admin(): bool {
    return !empty($_SESSION['acm_admin']);
}

function require_admin(): void {
    if (!is_admin()) {
        header('Location: ' . url('admin/login.php'));
        exit;
    }
}

function visible(string $key): bool {
    static $v = null;
    if ($v === null) $v = load_content('visibility');
    return ($v[$key] ?? true) !== false;
}

function save_submission(array $data): bool {
    $file = CONTENT_DIR . '/submissions.json';
    $all  = [];
    if (file_exists($file)) {
        $all = json_decode(file_get_contents($file), true) ?: [];
    }
    if (!is_array($all)) $all = [];
    $data['date'] = date('Y-m-d H:i:s');
    $all[]        = $data;
    $result = file_put_contents(
        $file,
        json_encode($all, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        LOCK_EX
    );
    return $result !== false;
}
