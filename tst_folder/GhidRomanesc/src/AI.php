<?php
class AI {
    private static string $apiUrl = 'https://api.anthropic.com/v1/messages';
    private static string $model  = ANTHROPIC_MODEL;

    private static function call(string $prompt, int $maxTokens = 4000): string {
        $apiKey = ANTHROPIC_API_KEY;
        if (empty($apiKey)) {
            throw new Exception('Cheia API Anthropic nu este configurată. Mergi la Admin → Setări → AI.');
        }

        $payload = json_encode([
            'model'      => self::$model,
            'max_tokens' => $maxTokens,
            'messages'   => [['role' => 'user', 'content' => $prompt]],
        ], JSON_UNESCAPED_UNICODE);

        $ch = curl_init(self::$apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'x-api-key: ' . $apiKey,
                'anthropic-version: 2023-06-01',
            ],
            CURLOPT_TIMEOUT        => 120,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response  = curl_exec($ch);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        unset($ch); // curl_close() deprecat în PHP 8.4+

        if ($curlError) {
            throw new Exception('Eroare cURL: ' . $curlError);
        }

        $data = json_decode($response, true);

        if ($httpCode !== 200) {
            $errMsg = $data['error']['message'] ?? 'Eroare API necunoscută';
            throw new Exception("Eroare API ($httpCode): $errMsg");
        }

        return $data['content'][0]['text'] ?? '';
    }

    public static function generateArticle(array $params): array {
        $prompt = self::buildArticlePrompt($params);
        $text   = self::call($prompt, 6000);

        return [
            'content' => $text,
            'params'  => $params,
            'model'   => self::$model,
            'generated_at' => date('Y-m-d H:i:s'),
        ];
    }

    public static function generateIdeas(string $trendsInput, string $customPrompt = ''): string {
        if (empty($customPrompt)) {
            $customPrompt = self::getDefaultIdeasPrompt();
        }
        $prompt = str_replace('[TRENDURI]', $trendsInput, $customPrompt);
        return self::call($prompt, 4000);
    }

    public static function checkRisk(string $content): array {
        $prompt = "Ești reviewer editorial pentru GhidRomânesc.ro.

Analizează textul de mai jos și determină nivelul de risc editorial:
- VERDE: tutoriale digitale, explicații generale, modele emailuri, checklist-uri simple
- GALBEN: acte, instituții, proceduri administrative, consulate
- ROȘU: taxe, ANAF, pensii, legislație, sănătate, investiții, sfaturi personalizate

Text de analizat:
---
" . mb_substr($content, 0, 3000) . "
---

Răspunde STRICT în format JSON:
{
  \"risk\": \"verde|galben|rosu\",
  \"reason\": \"Motiv scurt\",
  \"has_legal_advice\": true/false,
  \"has_financial_advice\": true/false,
  \"has_medical_advice\": true/false,
  \"has_guarantees\": true/false,
  \"needs_disclaimer\": true/false,
  \"has_structure\": true/false,
  \"quality_score\": 0-100,
  \"issues\": [\"problemă 1\", \"problemă 2\"],
  \"recommendation\": \"publish|pending|blocked\"
}";

        $response = self::call($prompt, 500);

        // Extrage JSON din răspuns
        if (preg_match('/\{.*\}/s', $response, $matches)) {
            $data = json_decode($matches[0], true);
            if ($data) return $data;
        }

        return [
            'risk' => 'galben',
            'reason' => 'Nu s-a putut analiza automat',
            'recommendation' => 'pending',
            'quality_score' => 0,
        ];
    }

    public static function generateSeo(string $title, string $content): array {
        $prompt = "Generează SEO pentru un articol de pe GhidRomânesc.ro.

Titlu: $title
Conținut (primele 500 cuvinte):
" . mb_substr(strip_tags($content), 0, 1500) . "

Răspunde STRICT în format JSON:
{
  \"meta_title\": \"...\",
  \"meta_description\": \"...\",
  \"slug\": \"...\",
  \"focus_keyword\": \"...\",
  \"secondary_keywords\": [\"...\", \"...\"],
  \"og_title\": \"...\",
  \"og_description\": \"...\"
}

Reguli:
- meta_title: max 60 caractere, include cuvântul-cheie
- meta_description: 140-160 caractere, acționabilă
- slug: fără diacritice, cu cratimă, max 70 caractere";

        $response = self::call($prompt, 600);
        if (preg_match('/\{.*\}/s', $response, $matches)) {
            $data = json_decode($matches[0], true);
            if ($data) return $data;
        }
        return [];
    }

    public static function rewriteSimple(string $text): string {
        $prompt = "Rescrie textul următor în limba română, simplu și clar, pe înțelesul oricui.
Folosește propoziții scurte, evită jargonul juridic/tehnic, păstrează informațiile esențiale.
Textul trebuie să rămână corect din punct de vedere al informațiilor.

Text original:
---
$text
---

Versiune simplificată:";

        return self::call($prompt, 2000);
    }

    public static function proposeInternalLinks(string $title, string $_content = ''): array {
        $existingArticles = Database::fetchAll(
            "SELECT a.title, a.slug, c.slug AS category_slug
             FROM articles a LEFT JOIN categories c ON a.category_id=c.id
             WHERE a.status='published' LIMIT 50"
        );
        $articleList = implode("\n", array_map(
            fn($a) => "- {$a['title']} (/{$a['category_slug']}/{$a['slug']}/)",
            $existingArticles
        ));

        $prompt = "Articol curent: $title

Lista articolelor existente pe GhidRomânesc.ro:
$articleList

Propune maxim 5 linkuri interne relevante pentru articolul curent.
Răspunde STRICT în format JSON:
[
  {\"title\": \"...\", \"url\": \"...\", \"context\": \"De ce este relevant\"},
  ...
]";

        $response = self::call($prompt, 600);
        if (preg_match('/\[.*\]/s', $response, $matches)) {
            $data = json_decode($matches[0], true);
            if ($data) return $data;
        }
        return [];
    }

    private static function buildArticlePrompt(array $p): string {
        // Obține promptul editabil din baza de date
        $customPrompt = Database::fetchColumn(
            'SELECT value FROM settings WHERE key_name = "prompt_articol"'
        );

        $basePrompt = $customPrompt ?: self::getDefaultArticlePrompt();

        return strtr($basePrompt, [
            '[SUBIECT]'       => $p['subject'] ?? '',
            '[CATEGORIE]'     => $p['category'] ?? '',
            '[TIP_ARTICOL]'   => $p['type'] ?? 'ghid complet',
            '[PUBLIC]'        => $p['audience'] ?? 'toți românii',
            '[RISC]'          => $p['risk'] ?? 'galben',
            '[KEYWORD]'       => $p['keyword'] ?? '',
            '[SURSE]'         => $p['sources'] ?? 'surse oficiale relevante',
            '[TON]'           => $p['tone'] ?? 'simplu și prietenos',
            '[LUNGIME]'       => $p['length'] ?? 'complet',
        ]);
    }

    public static function getDefaultArticlePrompt(): string {
        return 'Ești asistent editorial pentru GhidRomânesc.ro.

Scrie un articol în limba română, clar, practic și ușor de înțeles.

Subiect: [SUBIECT]
Categorie: [CATEGORIE]
Tip articol: [TIP_ARTICOL]
Public țintă: [PUBLIC]
Nivel de risc: [RISC]
Cuvânt-cheie principal: [KEYWORD]
Ton: [TON]
Lungime: [LUNGIME]
Surse oficiale de verificat: [SURSE]

Reguli stricte:
- Scrie pentru oameni, nu pentru motoare de căutare.
- Nu inventa informații. Dacă nu ești sigur, marchează clar: «de verificat din sursă oficială».
- Nu oferi consultanță juridică, fiscală, financiară sau medicală personalizată.
- Nu folosi promisiuni: «garantat», «sigur vei obține», «obligatoriu» fără sursă oficială.
- Folosește formulări prudente: «în general», «de obicei», «conform sursei oficiale», «verifică înainte de depunere».
- Pentru articole cu risc galben sau roșu, adaugă disclaimer.
- Include surse oficiale la final.
- Include data la care articolul trebuie reverificat.

Structură obligatorie:
1. Titlu SEO clar
2. Răspuns scurt în primele 3 fraze
3. Casetă «Pe scurt» (3-5 puncte cheie)
4. Cine are nevoie de acest ghid
5. Ce trebuie să știi înainte
6. Pașii de urmat (numerotați)
7. Cât durează (dacă este cazul)
8. Cât costă (dacă este cazul)
9. Greșeli frecvente
10. Întrebări frecvente (3-5 întrebări)
11. Surse oficiale / surse de verificat
12. Disclaimer (dacă este cazul)
13. Meta title (max 60 caractere)
14. Meta description (140-160 caractere)
15. Slug URL (fără diacritice, cu cratimă)
16. Propuneri linkuri interne (2-3 articole)
17. Nivel de risc final: verde / galben / roșu
18. Recomandare status: publish / pending review / blocked';
    }

    public static function getDefaultIdeasPrompt(): string {
        return 'Ești asistent editorial pentru GhidRomânesc.ro.

Analizează lista de trenduri, căutări, întrebări și subiecte introduse.

Trenduri și subiecte de analizat:
[TRENDURI]

Generează 20 de idei de articole utile pentru românii din România și diaspora.

Pentru fiecare idee oferă:
1. Titlu articol
2. Categorie (acte-institutii / digital-ai / diaspora / bani-taxe / joburi-viata / modele-checklist)
3. Cuvânt-cheie principal
4. Intenția utilizatorului
5. Tip articol recomandat (ghid complet / ghid rapid / checklist / FAQ / model email / tutorial / actualizare)
6. Nivel de risc: verde / galben / roșu
7. Dificultate SEO estimată: mică / medie / mare
8. De ce ar căuta cineva asta
9. Surse oficiale care trebuie verificate
10. Recomandare: publicare automată / pending review / blocat

Prioritizează subiectele evergreen, practice și utile.
Evită clickbait-ul, știrile politice, scandalurile și subiectele toxice.
Alege subiecte care rezolvă o problemă concretă.';
    }
}
