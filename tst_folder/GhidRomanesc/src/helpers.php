<?php
function e(mixed $val): string {
    return htmlspecialchars((string)$val, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function slug(string $text): string {
    $text = mb_strtolower($text, 'UTF-8');
    $diacritics = ['ă'=>'a','â'=>'a','î'=>'i','ș'=>'s','ț'=>'t','ş'=>'s','ţ'=>'t',
                   'Ă'=>'a','Â'=>'a','Î'=>'i','Ș'=>'s','Ț'=>'t','é'=>'e','è'=>'e',
                   'ê'=>'e','ë'=>'e','à'=>'a','á'=>'a','ü'=>'u','ö'=>'o'];
    $text = strtr($text, $diacritics);
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', trim($text));
    return trim($text, '-');
}

function truncate(string $text, int $len = 160, string $suffix = '…'): string {
    $text = strip_tags($text);
    if (mb_strlen($text) <= $len) return $text;
    return mb_substr($text, 0, $len) . $suffix;
}

function timeAgo(string $datetime): string {
    $diff = time() - strtotime($datetime);
    if ($diff < 60)     return 'acum câteva secunde';
    if ($diff < 3600)   return 'acum ' . round($diff/60) . ' minute';
    if ($diff < 86400)  return 'acum ' . round($diff/3600) . ' ore';
    if ($diff < 604800) return 'acum ' . round($diff/86400) . ' zile';
    return date('d.m.Y', strtotime($datetime));
}

function formatDate(string $datetime, string $format = 'd.m.Y'): string {
    return date($format, strtotime($datetime));
}

function redirect(string $url, int $code = 302): never {
    header("Location: $url", true, $code);
    exit;
}

function jsonResponse(mixed $data, int $code = 200): never {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function currentUrl(): string {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    return $scheme . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

function currentPath(): string {
    return strtok($_SERVER['REQUEST_URI'], '?');
}

function isActivePath(string $path): bool {
    return strpos(currentPath(), $path) === 0;
}

function csrf(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf(): void {
    $token = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        jsonResponse(['error' => 'Token CSRF invalid'], 403);
    }
}

function sanitize(mixed $val): string {
    return trim(strip_tags((string)$val));
}

function riskBadge(string $risk): string {
    return match($risk) {
        'verde' => '<span class="badge badge-green">Verde</span>',
        'galben'=> '<span class="badge badge-yellow">Galben</span>',
        'rosu'  => '<span class="badge badge-red">Roșu</span>',
        default => '<span class="badge badge-gray">Necunoscut</span>',
    };
}

function statusLabel(string $status): string {
    return match($status) {
        'published'    => 'Publicat',
        'draft'        => 'Draft',
        'pending'      => 'În verificare',
        'scheduled'    => 'Programat',
        'blocked'      => 'Blocat',
        'needs_update' => 'Necesită actualizare',
        default        => ucfirst($status),
    };
}

function statusBadge(string $status): string {
    $cls = match($status) {
        'published'    => 'badge-green',
        'draft'        => 'badge-gray',
        'pending'      => 'badge-yellow',
        'scheduled'    => 'badge-blue',
        'blocked'      => 'badge-red',
        'needs_update' => 'badge-orange',
        default        => 'badge-gray',
    };
    return '<span class="badge ' . $cls . '">' . statusLabel($status) . '</span>';
}

function categoryIcon(string $slug): string {
    $icons = [
        'acte-institutii'   => '📋',
        'digital-ai'        => '💻',
        'diaspora'          => '🌍',
        'bani-taxe'         => '💰',
        'joburi-viata'      => '💼',
        'modele-checklist'  => '✅',
        'actualizari'       => '🔔',
    ];
    $emoji = $icons[$slug] ?? '📄';
    return '<span class="category-icon-inline" aria-hidden="true">' . $emoji . '</span>';
}

function autoRisk(string $category, string $_type = ''): string {
    $red = ['bani-taxe'];
    $yellow = ['acte-institutii', 'diaspora'];
    if (in_array($category, $red)) return 'rosu';
    if (in_array($category, $yellow)) return 'galben';
    return 'verde';
}

function buildArticleUrl(string $category, string $slug): string {
    return "/{$category}/{$slug}/";
}
