<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/AI.php';

header('Content-Type: application/json; charset=utf-8');

// Autentificare obligatorie pentru AI
if (!Auth::check()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Autentificare necesară.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

try {
    switch ($action) {

        case 'article':
            $result = AI::generateArticle([
                'subject'  => $input['subject']  ?? '',
                'category' => $input['category'] ?? '',
                'type'     => $input['type']     ?? 'ghid complet',
                'audience' => $input['audience'] ?? 'toți românii',
                'risk'     => $input['risk']     ?? 'galben',
                'keyword'  => $input['keyword']  ?? '',
                'sources'  => $input['sources']  ?? '',
                'tone'     => $input['tone']     ?? 'simplu și prietenos',
                'length'   => $input['length']   ?? 'complet',
            ]);

            // Extrage meta din conținut (pattern simplu)
            $content  = $result['content'];
            $metaTitle = '';
            $metaDesc  = '';
            $slug      = '';

            if (preg_match('/Meta title[:\s]+([^\n]+)/i', $content, $m))
                $metaTitle = trim(strip_tags($m[1]));
            if (preg_match('/Meta description[:\s]+([^\n]+)/i', $content, $m))
                $metaDesc = trim(strip_tags($m[1]));
            if (preg_match('/Slug URL[:\s]+([^\n]+)/i', $content, $m))
                $slug = trim(strip_tags(str_replace(['/', 'https:', 'ghidromanesc.ro'], '', $m[1])));

            echo json_encode([
                'success'          => true,
                'content'          => $content,
                'meta_title'       => mb_substr($metaTitle, 0, 70),
                'meta_description' => mb_substr($metaDesc, 0, 170),
                'slug'             => $slug ? slug($slug) : slug($input['subject'] ?? ''),
            ], JSON_UNESCAPED_UNICODE);
            break;

        case 'ideas':
            $content = AI::generateIdeas($input['trends'] ?? '');
            echo json_encode(['success' => true, 'content' => $content], JSON_UNESCAPED_UNICODE);
            break;

        case 'risk':
            $result = AI::checkRisk($input['content'] ?? '');
            echo json_encode(['success' => true, 'result' => $result], JSON_UNESCAPED_UNICODE);
            break;

        case 'seo':
            $result = AI::generateSeo($input['title'] ?? '', $input['content'] ?? '');
            echo json_encode(['success' => true, 'result' => $result], JSON_UNESCAPED_UNICODE);
            break;

        case 'rewrite':
            $content = AI::rewriteSimple($input['text'] ?? '');
            echo json_encode(['success' => true, 'content' => $content], JSON_UNESCAPED_UNICODE);
            break;

        case 'internal_links':
            $links = AI::proposeInternalLinks($input['title'] ?? '', $input['content'] ?? '');
            echo json_encode(['success' => true, 'links' => $links], JSON_UNESCAPED_UNICODE);
            break;

        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Acțiune necunoscută.']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
