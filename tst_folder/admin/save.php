<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$allowed = ['home','about','services','program','contact','footer'];
$page    = $_POST['page'] ?? '';
if (!in_array($page, $allowed, true)) {
    header('Location: ' . BASE_PATH . '/admin/index.php');
    exit;
}

$existing = load_content($page);
$incoming = $_POST['content'] ?? [];

// Merge: only update keys that already exist (don't allow new keys via POST)
$updated = $existing;
foreach ($existing as $key => $val) {
    if (!is_string($val)) continue;
    if (str_ends_with($key, '_image')) continue; // handled separately
    if (isset($incoming[$key])) {
        $updated[$key] = trim($incoming[$key]);
    }
}

// Handle image fields
$upload_dir = ROOT_DIR . '/assets/img/pages/';
foreach ($existing as $key => $val) {
    if (!is_string($val) || !str_ends_with($key, '_image')) continue;

    // Clear checkbox
    if (!empty($_POST['clear_image'][$key])) {
        if ($val && file_exists($upload_dir . $val)) {
            @unlink($upload_dir . $val);
        }
        $updated[$key] = '';
        continue;
    }

    // File upload
    if (isset($_FILES['img']['name'][$key]) && $_FILES['img']['error'][$key] === UPLOAD_ERR_OK) {
        $tmp  = $_FILES['img']['tmp_name'][$key];
        $size = $_FILES['img']['size'][$key];

        $fi   = new finfo(FILEINFO_MIME_TYPE);
        $mime = $fi->file($tmp);

        $allowed_mime = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        if (isset($allowed_mime[$mime]) && $size <= 5 * 1024 * 1024) {
            $ext      = $allowed_mime[$mime];
            $filename = $page . '_' . $key . '_' . time() . '.' . $ext;

            if (move_uploaded_file($tmp, $upload_dir . $filename)) {
                // Remove old file
                if ($val && file_exists($upload_dir . $val)) {
                    @unlink($upload_dir . $val);
                }
                $updated[$key] = $filename;
            }
        }
    }
}

// Handle visibility toggles submitted from edit page
if (isset($_POST['visibility'])) {
    $vis_file    = CONTENT_DIR . '/visibility.json';
    $vis_current = file_exists($vis_file) ? (json_decode(file_get_contents($vis_file), true) ?? []) : [];

    // Section ids that belong to this page (same keys sent via POST)
    $vis_keys_in_form = array_keys($_POST['visibility'] + []);
    // Also collect keys that were in form but unchecked (not in POST)
    // We detect them by reading the page_sections map replicated here
    static $sec_ids = [
        'home'     => ['home_features','home_subjects','home_about','home_testimonials','home_cta',
                       'home_feat1','home_feat2','home_feat3','home_feat4',
                       'home_test1','home_test2','home_test3',
                       'home_about_p1','home_about_p2','home_cred1','home_cred2','home_cred3'],
        'about'    => ['about_method',
                       'about_method1','about_method2','about_method3','about_method4',
                       'about_bio_p1','about_bio_p2','about_bio_p3',
                       'about_stat1','about_stat2','about_stat3'],
        'services' => ['services_how','services_cta',
                       'services_how1','services_how2','services_how3'],
        'program'  => ['program_prices','program_enroll',
                       'program_step1','program_step2','program_step3','program_step4'],
        'contact'  => ['contact_address','contact_phone','contact_email','contact_hours','contact_map'],
        'footer'   => [],
    ];
    $page_vis_keys = $sec_ids[$page] ?? [];
    foreach ($page_vis_keys as $vk) {
        $vis_current[$vk] = isset($_POST['visibility'][$vk]);
    }
    file_put_contents($vis_file, json_encode($vis_current, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

if (save_content($page, $updated)) {
    header('Location: ' . BASE_PATH . '/admin/edit.php?page=' . urlencode($page) . '&saved=1');
} else {
    header('Location: ' . BASE_PATH . '/admin/edit.php?page=' . urlencode($page) . '&err=1');
}
exit;
