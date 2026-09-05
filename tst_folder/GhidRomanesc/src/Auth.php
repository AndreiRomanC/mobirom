<?php
class Auth {
    public static function attempt(string $email, string $password): bool {
        $user = Database::fetchOne('SELECT * FROM users WHERE email = ? AND is_active = 1', [$email]);
        if (!$user || !password_verify($password, $user['password'])) return false;

        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_email']= $user['email'];
        $_SESSION['login_at']  = time();

        Database::update('users', ['last_login' => date('Y-m-d H:i:s')], 'id = ?', [$user['id']]);
        return true;
    }

    public static function logout(): void {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time()-42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }

    public static function check(): bool {
        if (empty($_SESSION['user_id'])) return false;
        if (time() - ($_SESSION['login_at'] ?? 0) > SESSION_LIFETIME) {
            self::logout();
            return false;
        }
        return true;
    }

    public static function requireAdmin(): void {
        if (!self::check()) {
            header('Location: /admin/login.php');
            exit;
        }
    }

    public static function requireRole(string ...$roles): void {
        self::requireAdmin();
        if (!in_array($_SESSION['user_role'] ?? '', $roles)) {
            header('Location: /admin/?eroare=acces-interzis');
            exit;
        }
    }

    public static function user(): ?array {
        if (!self::check()) return null;
        return [
            'id'    => $_SESSION['user_id'],
            'name'  => $_SESSION['user_name'],
            'role'  => $_SESSION['user_role'],
            'email' => $_SESSION['user_email'],
        ];
    }

    public static function can(string $action): bool {
        $role = $_SESSION['user_role'] ?? '';
        $permissions = [
            'administrator' => ['publish', 'edit', 'delete', 'review', 'ai_publish', 'manage_users', 'settings'],
            'editor'        => ['publish', 'edit', 'review', 'ai_publish'],
            'autor'         => ['edit'],
            'reviewer'      => ['review'],
            'contributor_ai'=> [],
        ];
        return in_array($action, $permissions[$role] ?? []);
    }

    public static function hashPassword(string $password): string {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }
}
