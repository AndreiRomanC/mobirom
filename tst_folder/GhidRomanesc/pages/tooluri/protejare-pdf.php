<?php
// ── Encriptare PDF RC4-128 server-side ───────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['pdf'])) {
    header('Content-Type: application/json; charset=utf-8');

    $file = $_FILES['pdf'];
    $pass = trim($_POST['password'] ?? '');

    if ($file['error'] !== UPLOAD_ERR_OK)      { echo json_encode(['error' => 'Eroare upload fișier.']); exit; }
    if ($file['type'] !== 'application/pdf' && !str_ends_with(strtolower($file['name']), '.pdf'))
                                                { echo json_encode(['error' => 'Fișierul trebuie să fie PDF.']); exit; }
    if (strlen($pass) < 1)                     { echo json_encode(['error' => 'Parola nu poate fi goală.']); exit; }
    if ($file['size'] > 50 * 1024 * 1024)      { echo json_encode(['error' => 'Fișierul este prea mare (max 50 MB).']); exit; }

    $pdf = file_get_contents($file['tmp_name']);
    if (!$pdf || substr($pdf, 0, 4) !== '%PDF') { echo json_encode(['error' => 'Fișierul nu este un PDF valid.']); exit; }

    // Verifică dacă e deja protejat
    if (preg_match('/\/Encrypt\b/', $pdf)) {
        echo json_encode(['error' => 'Fișierul este deja protejat cu parolă.']); exit;
    }

    try {
        $encrypted = PdfProtect::encrypt($pdf, $pass);
        echo json_encode(['pdf' => base64_encode($encrypted)]);
    } catch (Throwable $e) {
        echo json_encode(['error' => 'Eroare la procesare: ' . $e->getMessage()]);
    }
    exit;
}

// ── Clasa de encriptare PDF RC4-128 ──────────────────────────────────────────
class PdfProtect {
    // Padding standard conform specificatiei PDF (Anexa C)
    private const PAD = "\x28\xBF\x4E\x5E\x4E\x75\x8A\x41\x64\x00\x4E\x56\xFF\xFA\x01\x08\x2E\x2E\x00\xB6\xD0\x68\x3E\x80\x2F\x0C\xA9\xFE\x64\x53\x69\x7A";

    public static function encrypt(string $pdf, string $userPwd, string $ownerPwd = ''): string {
        if (!$ownerPwd) $ownerPwd = $userPwd;
        $P = -3904; // permisiuni: print=DA, copiere=NU, modificare=NU

        // File ID
        if (preg_match('/\/ID\s*\[\s*<([0-9a-fA-F]{16,32})>/si', $pdf, $m))
            $fileId = hex2bin(substr($m[1], 0, 32));
        else
            $fileId = random_bytes(16);

        $O   = self::computeO($userPwd, $ownerPwd);
        $key = self::computeKey($userPwd, $O, $P, $fileId);
        $U   = self::computeU($key);

        // Encriptează toate obiectele indirecte
        $result = preg_replace_callback(
            '/(\d+)\s+(\d+)\s+obj(.*?)endobj/s',
            function ($m) use ($key) {
                [, $objN, $genN, $body] = $m;
                $objKey = self::objKey($key, (int)$objN, (int)$genN);

                // Encriptează stream-uri
                $body = preg_replace_callback(
                    '/\bstream\r?\n(.*?)\r?\nendstream/s',
                    fn($s) => "stream\n" . self::rc4($objKey, $s[1]) . "\nendstream",
                    $body
                );

                // Encriptează string-uri literale (...) — evită cele din stream
                $body = self::encryptStrings($body, $objKey);

                return "$objN $genN obj$body" . 'endobj';
            },
            $pdf
        );

        return self::injectEncryptDict($result, $O, $U, $P, $fileId);
    }

    // ── Algoritmi PDF (ISO 32000-1 §7.6) ─────────────────────────────────────

    private static function padPwd(string $p): string {
        return substr(substr($p, 0, 32) . self::PAD, 0, 32);
    }

    private static function computeO(string $u, string $o): string {
        $k = md5(self::padPwd($o), true);
        for ($i = 0; $i < 50; $i++) $k = md5($k, true);
        $k = substr($k, 0, 16);
        $R = self::rc4($k, self::padPwd($u));
        for ($i = 1; $i <= 19; $i++) {
            $xk = ''; for ($j = 0; $j < 16; $j++) $xk .= chr(ord($k[$j]) ^ $i);
            $R = self::rc4($xk, $R);
        }
        return $R;
    }

    private static function computeKey(string $u, string $O, int $P, string $id): string {
        $k = md5(self::padPwd($u) . $O . pack('V', $P) . $id, true);
        for ($i = 0; $i < 50; $i++) $k = md5($k, true);
        return substr($k, 0, 16);
    }

    private static function computeU(string $key): string {
        $R = self::rc4($key, self::PAD);
        for ($i = 1; $i <= 19; $i++) {
            $xk = ''; for ($j = 0; $j < 16; $j++) $xk .= chr(ord($key[$j]) ^ $i);
            $R = self::rc4($xk, $R);
        }
        return str_pad($R, 32, "\x00");
    }

    private static function objKey(string $fk, int $n, int $g): string {
        return substr(md5($fk . substr(pack('V', $n), 0, 3) . substr(pack('V', $g), 0, 2), true), 0, min(16, strlen($fk) + 5));
    }

    private static function rc4(string $key, string $data): string {
        $S = range(0, 255);
        $j = 0; $kl = strlen($key);
        for ($i = 0; $i < 256; $i++) {
            $j = ($j + $S[$i] + ord($key[$i % $kl])) & 0xFF;
            [$S[$i], $S[$j]] = [$S[$j], $S[$i]];
        }
        $i = $j = 0; $out = '';
        foreach (str_split($data) as $c) {
            $i = ($i + 1) & 0xFF;
            $j = ($j + $S[$i]) & 0xFF;
            [$S[$i], $S[$j]] = [$S[$j], $S[$i]];
            $out .= chr(ord($c) ^ $S[($S[$i] + $S[$j]) & 0xFF]);
        }
        return $out;
    }

    // Encriptează string-urile literale ( ... ) din corpul unui obiect PDF
    private static function encryptStrings(string $body, string $objKey): string {
        $out = ''; $i = 0; $n = strlen($body);
        while ($i < $n) {
            if ($body[$i] === '(' && ($i === 0 || $body[$i-1] !== '\\')) {
                // Colectează string cu paranteze nested
                $depth = 1; $j = $i + 1; $raw = '';
                while ($j < $n && $depth > 0) {
                    if ($body[$j] === '\\' && $j + 1 < $n) { $raw .= $body[$j] . $body[$j+1]; $j += 2; continue; }
                    if ($body[$j] === '(') $depth++;
                    if ($body[$j] === ')') $depth--;
                    if ($depth > 0) $raw .= $body[$j];
                    $j++;
                }
                // Decodează escape-urile, encriptează, re-encodează
                $decoded = stripcslashes($raw);
                $enc     = self::rc4($objKey, $decoded);
                $escaped = str_replace(['\\','(',')'], ['\\\\','\\(','\\)'], $enc);
                $out .= '(' . $escaped . ')';
                $i = $j;
            } else {
                $out .= $body[$i++];
            }
        }
        return $out;
    }

    // Adaugă dicționarul /Encrypt și reconstruiește xref corect
    private static function injectEncryptDict(string $pdf, string $O, string $U, int $P, string $id): string {
        $oHex  = strtoupper(bin2hex($O));
        $uHex  = strtoupper(bin2hex($U));
        $idHex = strtoupper(bin2hex($id));

        // Extrage intrările din trailer original (/Root, /Info, /Size etc.)
        $origTrailer = '';
        if (preg_match('/trailer\s*<<(.*?)>>/s', $pdf, $tm)) {
            $t = $tm[1];
            $t = preg_replace('/\/Encrypt\s+\d+\s+\d+\s+R\s*/', '', $t);
            $t = preg_replace('/\/ID\s*\[[^\]]*\]\s*/', '', $t);
            $t = preg_replace('/\/Size\s+\d+\s*/', '', $t);
            $origTrailer = trim($t);
        }

        // Numărul maxim de obiect existent
        preg_match_all('/^(\d+)\s+\d+\s+obj/m', $pdf, $m);
        $maxObj = !empty($m[1]) ? max(array_map('intval', $m[1])) + 1 : 100;

        // Taie xref/trailer original — păstrăm doar corpul PDF
        foreach (["\nxref", "\nstartxref"] as $marker) {
            $pos = strrpos($pdf, $marker);
            if ($pos !== false) { $pdf = substr($pdf, 0, $pos); break; }
        }
        $pdf = rtrim($pdf) . "\n";

        // Adaugă obiectul Encrypt la final
        $pdf .= "$maxObj 0 obj\n<< /Filter /Standard /V 2 /R 3 /Length 128 /P $P /O <$oHex> /U <$uHex> >>\nendobj\n";

        // Reconstruiește xref scanând pozițiile reale ale obiectelor
        $xrefStart = strlen($pdf);
        $size = $maxObj + 1;

        preg_match_all('/(?:^|\n)(\d+) (\d+) obj\b/', $pdf, $objM, PREG_OFFSET_CAPTURE);
        $offsets = [];
        foreach ($objM[0] as $i => $match) {
            $n   = (int)$objM[1][$i][0];
            $g   = (int)$objM[2][$i][0];
            $pos = $match[1];
            if ($pos > 0 && $pdf[$pos] === "\n") $pos++; // sari newline-ul de prefix
            $offsets[$n] = ['g' => $g, 'off' => $pos];
        }

        $xref  = "xref\n0 $size\n";
        $xref .= "0000000000 65535 f \n";
        for ($i = 1; $i < $size; $i++) {
            if (isset($offsets[$i])) {
                $xref .= str_pad($offsets[$i]['off'], 10, '0', STR_PAD_LEFT)
                       . ' ' . str_pad($offsets[$i]['g'], 5, '0', STR_PAD_LEFT)
                       . " n \n";
            } else {
                $xref .= "0000000000 65535 f \n";
            }
        }

        $trailer = "trailer\n<< /Size $size /Encrypt $maxObj 0 R /ID [<$idHex> <$idHex>] $origTrailer >>\n"
                 . "startxref\n$xrefStart\n%%EOF\n";

        return $pdf . $xref . $trailer;
    }
}

// ── Configurare pagină ────────────────────────────────────────────────────────
$pageTitle = 'Protejează PDF cu parolă — gratuit în browser — GhidRomânesc';
$metaDescription = 'Adaugă parolă la orice fișier PDF gratuit. Procesare securizată pe server, fișierul nu este stocat. RC4 128-bit.';
$canonicalUrl = SITE_DOMAIN . '/tooluri/protejare-pdf/';
require __DIR__ . '/../../templates/header.php';
?>
<div class="page-header"><div class="container">
  <div style="font-size:.85rem;color:rgba(255,255,255,.7);margin-bottom:.5rem">
    <a href="/tooluri/" style="color:inherit">← Toate toolurile</a>
  </div>
  <h1 class="page-title">🔒 Protejează PDF cu parolă</h1>
  <p class="page-subtitle">Adaugă parolă de deschidere la orice PDF. RC4 128-bit, procesare pe server.</p>
</div></div>

<div class="container-sm" style="padding-bottom:4rem">
<div style="background:#fff;border:1.5px solid var(--border);border-radius:16px;padding:2rem;margin-bottom:1.5rem">

  <div id="drop-zone"
    style="border:2.5px dashed #cbd5e1;border-radius:12px;padding:2.5rem;text-align:center;cursor:pointer;margin-bottom:1.5rem;transition:border-color .15s"
    onclick="document.getElementById('pdf-input').click()"
    ondragover="event.preventDefault();this.style.borderColor='#457b9d'"
    ondragleave="this.style.borderColor='#cbd5e1'"
    ondrop="handleDrop(event)">
    <div style="font-size:2.5rem;margin-bottom:.5rem">📄</div>
    <div style="font-weight:700;color:var(--blue-dark)">Selectează sau trage PDF-ul aici</div>
    <div style="font-size:.82rem;color:var(--text-muted);margin-top:.25rem">Un singur fișier PDF · max 50 MB</div>
    <input type="file" id="pdf-input" accept=".pdf,application/pdf" style="display:none">
  </div>

  <div id="form-section" style="display:none">
    <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:.75rem 1rem;margin-bottom:1.25rem;font-size:.875rem;display:flex;align-items:center;gap:.5rem">
      📄 <strong id="file-name"></strong>
      <button onclick="resetForm()" style="margin-left:auto;border:none;background:none;cursor:pointer;color:var(--text-muted);font-size:.8rem">✕ Schimbă</button>
    </div>

    <div class="form-group" style="margin-bottom:1rem">
      <label style="font-weight:600;color:var(--blue-dark)">Parolă de deschidere</label>
      <div style="display:flex;gap:.5rem;margin-top:.35rem">
        <input type="password" id="pass1" class="form-control" placeholder="Introdu parola" style="flex:1">
        <button type="button" onclick="togglePass()" style="background:none;border:1px solid var(--border);border-radius:8px;padding:.35rem .75rem;cursor:pointer;font-size:.9rem" title="Arată/ascunde">👁️</button>
      </div>
    </div>

    <div class="form-group" style="margin-bottom:1.25rem">
      <label style="font-weight:600;color:var(--blue-dark)">Confirmă parola</label>
      <input type="password" id="pass2" class="form-control" placeholder="Repetă parola" style="margin-top:.35rem">
    </div>

    <button onclick="protectPDF()" id="btn-protect" class="btn btn-primary" style="width:100%">🔒 Protejează și descarcă</button>
    <div id="status" style="margin-top:1rem;font-size:.875rem;text-align:center;min-height:1.5em"></div>
  </div>
</div>

<div style="background:#f8fafc;border-radius:12px;padding:1.25rem 1.5rem;font-size:.85rem;color:var(--text-muted)">
  <strong style="color:var(--blue-dark)">Cum funcționează:</strong> Fișierul se încarcă pe server, este encriptat cu RC4 128-bit și îți este returnat imediat. <strong>Nu se salvează pe disc</strong> — procesarea are loc exclusiv în memorie.
</div>
</div>

<script>
var selectedFile = null;

function handleFile(file) {
  if (!file || !file.name.toLowerCase().endsWith('.pdf')) {
    alert('Selectează un fișier PDF.');
    return;
  }
  selectedFile = file;
  document.getElementById('file-name').textContent = file.name + ' (' + (file.size / 1024).toFixed(0) + ' KB)';
  document.getElementById('form-section').style.display = '';
  document.getElementById('drop-zone').style.display = 'none';
}

document.getElementById('pdf-input').addEventListener('change', function(e) {
  handleFile(e.target.files[0]);
});

function handleDrop(e) {
  e.preventDefault();
  document.getElementById('drop-zone').style.borderColor = '#cbd5e1';
  handleFile(e.dataTransfer.files[0]);
}

function resetForm() {
  selectedFile = null;
  document.getElementById('pdf-input').value = '';
  document.getElementById('form-section').style.display = 'none';
  document.getElementById('drop-zone').style.display = '';
  document.getElementById('pass1').value = '';
  document.getElementById('pass2').value = '';
  setStatus('', '');
}

function togglePass() {
  ['pass1','pass2'].forEach(function(id) {
    var el = document.getElementById(id);
    el.type = el.type === 'password' ? 'text' : 'password';
  });
}

async function protectPDF() {
  var p1 = document.getElementById('pass1').value;
  var p2 = document.getElementById('pass2').value;

  if (!p1)       return setStatus('⚠️ Introdu o parolă.', '#b45309');
  if (p1 !== p2) return setStatus('⚠️ Parolele nu coincid.', '#b45309');
  if (!selectedFile) return;

  var btn = document.getElementById('btn-protect');
  btn.disabled = true;
  btn.textContent = '⏳ Se procesează...';
  setStatus('Se trimite și se encriptează...', '#457b9d');

  try {
    var fd = new FormData();
    fd.append('pdf', selectedFile, selectedFile.name);
    fd.append('password', p1);

    var res = await fetch('/tooluri/protejare-pdf/', { method: 'POST', body: fd });
    var json = await res.json();

    if (json.error) {
      setStatus('✕ ' + json.error, '#dc2626');
      return;
    }

    // Descarcă PDF-ul protejat
    var bytes = Uint8Array.from(atob(json.pdf), function(c) { return c.charCodeAt(0); });
    var blob  = new Blob([bytes], { type: 'application/pdf' });
    var a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = selectedFile.name.replace(/\.pdf$/i, '') + '-protejat.pdf';
    a.click();
    URL.revokeObjectURL(a.href);

    setStatus('✓ PDF protejat cu parolă! Fișierul a fost descărcat.', '#16a34a');

  } catch(e) {
    setStatus('✕ Eroare de conexiune: ' + e.message, '#dc2626');
  } finally {
    btn.disabled = false;
    btn.textContent = '🔒 Protejează și descarcă';
  }
}

function setStatus(msg, color) {
  var s = document.getElementById('status');
  s.textContent = msg;
  s.style.color = color;
}
</script>

<?php require __DIR__ . '/../../templates/footer.php'; ?>
