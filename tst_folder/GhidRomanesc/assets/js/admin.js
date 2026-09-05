/* GhidRomânesc Admin — JavaScript */

document.addEventListener('DOMContentLoaded', () => {

  // ─── Sidebar mobile ──────────────────────────────────────
  const sidebarToggle = document.getElementById('sidebar-toggle');
  const adminOverlay  = document.getElementById('admin-overlay');
  const adminSidebar  = document.querySelector('.admin-sidebar');
  if (sidebarToggle && adminSidebar) {
    sidebarToggle.addEventListener('click', () => {
      const open = adminSidebar.classList.toggle('open');
      adminOverlay?.classList.toggle('visible', open);
    });
    adminOverlay?.addEventListener('click', () => {
      adminSidebar.classList.remove('open');
      adminOverlay.classList.remove('visible');
    });
    document.addEventListener('keydown', e => {
      if (e.key === 'Escape') {
        adminSidebar.classList.remove('open');
        adminOverlay?.classList.remove('visible');
      }
    });
  }

  // ─── AI Studio tabs ──────────────────────────────────────
  document.querySelectorAll('.ai-tab').forEach(tab => {
    tab.addEventListener('click', () => {
      const target = tab.dataset.tab;
      document.querySelectorAll('.ai-tab').forEach(t => t.classList.remove('active'));
      document.querySelectorAll('.ai-tab-content').forEach(c => c.classList.remove('active'));
      tab.classList.add('active');
      document.getElementById('tab-' + target)?.classList.add('active');
    });
  });

  // ─── AI Generare articol ─────────────────────────────────
  const generateBtn = document.getElementById('btn-generate-article');
  if (generateBtn) {
    generateBtn.addEventListener('click', async () => {
      const form   = document.getElementById('ai-article-form');
      const output = document.getElementById('ai-output-article');
      if (!form || !output) return;

      const data = Object.fromEntries(new FormData(form).entries());
      if (!data.subject) { alert('Completează subiectul articolului.'); return; }

      generateBtn.disabled = true;
      generateBtn.textContent = 'Se generează...';
      output.innerHTML = '<div class="ai-loading"><div class="spinner"></div><p>Claude generează articolul... poate dura 30-60 secunde.</p></div>';

      try {
        const res  = await fetch('/api/ai-generate.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': window.csrfToken || '' },
          body: JSON.stringify({ action: 'article', ...data }),
        });
        const json = await res.json();

        if (json.success) {
          output.innerHTML = '<pre style="white-space:pre-wrap;font-size:.85rem;line-height:1.7">' + escapeHtml(json.content) + '</pre>';
          // Populate form fields if extracted
          if (json.meta_title)       document.getElementById('meta_title')?.value && (document.getElementById('meta_title').value = json.meta_title);
          if (json.meta_description) document.getElementById('meta_description') && (document.getElementById('meta_description').value = json.meta_description);
          if (json.slug)             document.getElementById('slug') && (document.getElementById('slug').value = json.slug);

          document.getElementById('btn-save-draft')?.removeAttribute('disabled');
          window.aiGeneratedContent = json.content;
        } else {
          output.innerHTML = '<p style="color:#dc2626;padding:1rem">Eroare: ' + escapeHtml(json.error || 'Necunoscută') + '</p>';
        }
      } catch (err) {
        output.innerHTML = '<p style="color:#dc2626;padding:1rem">Eroare conexiune: ' + escapeHtml(err.message) + '</p>';
      }

      generateBtn.disabled = false;
      generateBtn.textContent = 'Generează articol';
    });
  }

  // ─── AI Generare idei din trenduri ──────────────────────
  const ideasBtn = document.getElementById('btn-generate-ideas');
  if (ideasBtn) {
    ideasBtn.addEventListener('click', async () => {
      const input  = document.getElementById('trends-input');
      const output = document.getElementById('ai-output-ideas');
      if (!input || !output) return;

      const trends = input.value.trim();
      if (!trends) { alert('Introdu trenduri sau subiecte de analizat.'); return; }

      ideasBtn.disabled = true;
      ideasBtn.textContent = 'Se generează...';
      output.innerHTML = '<div class="ai-loading"><div class="spinner"></div><p>Claude analizează trendurile...</p></div>';

      try {
        const res  = await fetch('/api/ai-generate.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': window.csrfToken || '' },
          body: JSON.stringify({ action: 'ideas', trends }),
        });
        const json = await res.json();
        if (json.success) {
          output.innerHTML = '<pre style="white-space:pre-wrap;font-size:.85rem;line-height:1.7">' + escapeHtml(json.content) + '</pre>';
        } else {
          output.innerHTML = '<p style="color:#dc2626;padding:1rem">Eroare: ' + escapeHtml(json.error || '') + '</p>';
        }
      } catch (err) {
        output.innerHTML = '<p style="color:#dc2626;padding:1rem">Eroare: ' + escapeHtml(err.message) + '</p>';
      }

      ideasBtn.disabled = false;
      ideasBtn.textContent = 'Generează idei';
    });
  }

  // ─── AI Verificare risc ──────────────────────────────────
  const riskBtn = document.getElementById('btn-check-risk');
  if (riskBtn) {
    riskBtn.addEventListener('click', async () => {
      const content = document.getElementById('article-content')?.value || window.aiGeneratedContent || '';
      const output  = document.getElementById('risk-output');
      if (!content || !output) return;

      riskBtn.disabled = true;
      riskBtn.textContent = 'Se verifică...';
      output.innerHTML = '<div class="spinner"></div>';

      try {
        const res  = await fetch('/api/ai-generate.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': window.csrfToken || '' },
          body: JSON.stringify({ action: 'risk', content }),
        });
        const json = await res.json();
        if (json.success && json.result) {
          const r = json.result;
          const riskColor = { verde: '#16a34a', galben: '#b45309', rosu: '#dc2626' }[r.risk] || '#374151';
          output.innerHTML = `
            <div style="border-left:4px solid ${riskColor};padding:.75rem 1rem;background:#f9fafb;border-radius:0 8px 8px 0">
              <div style="font-weight:700;color:${riskColor};font-size:1.1rem;margin-bottom:.5rem">
                Risc: ${r.risk?.toUpperCase() || '—'} | Scor calitate: ${r.quality_score || 0}/100
              </div>
              <div style="font-size:.875rem;color:#374151;margin-bottom:.5rem">${escapeHtml(r.reason || '')}</div>
              ${r.issues?.length ? '<div style="font-size:.8rem;color:#dc2626">Probleme: ' + r.issues.map(escapeHtml).join(', ') + '</div>' : ''}
              <div style="font-size:.8rem;color:#16a34a;margin-top:.35rem">Recomandare: <strong>${escapeHtml(r.recommendation || '')}</strong></div>
            </div>`;
          // Auto-set risk level in form
          const riskInput = document.querySelector(`input[name="risk_level"][value="${r.risk}"]`);
          if (riskInput) {
            riskInput.checked = true;
            updateRiskSelector();
          }
        } else {
          output.innerHTML = '<p style="color:#dc2626">Eroare verificare.</p>';
        }
      } catch (err) {
        output.innerHTML = '<p style="color:#dc2626">Eroare: ' + escapeHtml(err.message) + '</p>';
      }

      riskBtn.disabled = false;
      riskBtn.textContent = 'Verifică riscul';
    });
  }

  // ─── Risk selector visual ────────────────────────────────
  function updateRiskSelector() {
    document.querySelectorAll('.risk-option').forEach(opt => {
      const inp = opt.querySelector('input[type="radio"]');
      opt.classList.toggle('selected', inp?.checked);
    });
  }
  document.querySelectorAll('.risk-option input').forEach(inp => {
    inp.addEventListener('change', updateRiskSelector);
  });
  updateRiskSelector();

  // ─── Generare SEO ────────────────────────────────────────
  const seoBtn = document.getElementById('btn-generate-seo');
  if (seoBtn) {
    seoBtn.addEventListener('click', async () => {
      const title   = document.getElementById('article-title')?.value || '';
      const content = document.getElementById('article-content')?.value || '';
      if (!title) { alert('Completează titlul articolului.'); return; }

      seoBtn.disabled = true;
      seoBtn.textContent = 'Se generează...';

      try {
        const res  = await fetch('/api/ai-generate.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': window.csrfToken || '' },
          body: JSON.stringify({ action: 'seo', title, content }),
        });
        const json = await res.json();
        if (json.success && json.result) {
          const r = json.result;
          if (r.meta_title)       { const el = document.getElementById('meta_title');       if (el) el.value = r.meta_title; }
          if (r.meta_description) { const el = document.getElementById('meta_description'); if (el) el.value = r.meta_description; }
          if (r.slug)             { const el = document.getElementById('slug');              if (el) el.value = r.slug; }
          if (r.focus_keyword)    { const el = document.getElementById('focus_keyword');     if (el) el.value = r.focus_keyword; }
          showAdminNotice('SEO generat cu succes!', 'success');
        }
      } catch {}
      seoBtn.disabled = false;
      seoBtn.textContent = 'Generează SEO';
    });
  }

  // ─── Confirm delete ──────────────────────────────────────
  document.querySelectorAll('[data-confirm]').forEach(btn => {
    btn.addEventListener('click', (e) => {
      if (!confirm(btn.dataset.confirm || 'Ești sigur?')) e.preventDefault();
    });
  });

  // ─── Auto-slug din titlu ─────────────────────────────────
  const titleInput = document.getElementById('article-title');
  const slugInput  = document.getElementById('slug');
  if (titleInput && slugInput) {
    titleInput.addEventListener('input', () => {
      if (!slugInput.dataset.manual) {
        slugInput.value = romanianSlug(titleInput.value);
      }
    });
    slugInput.addEventListener('input', () => {
      slugInput.dataset.manual = '1';
    });
  }

  // ─── Char count pentru meta ──────────────────────────────
  ['meta_title', 'meta_description'].forEach(id => {
    const el = document.getElementById(id);
    if (!el) return;
    const counter = document.getElementById(id + '_count');
    if (!counter) return;
    const max = id === 'meta_title' ? 60 : 160;
    function updateCount() {
      const len = el.value.length;
      counter.textContent = len + '/' + max;
      counter.style.color = len > max ? '#dc2626' : (len > max * .9 ? '#b45309' : '#6b7280');
    }
    el.addEventListener('input', updateCount);
    updateCount();
  });

  // ─── Helpers ─────────────────────────────────────────────
  function escapeHtml(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }

  function romanianSlug(text) {
    const map = {'ă':'a','â':'a','î':'i','ș':'s','ț':'t','ş':'s','ţ':'t','é':'e','è':'e'};
    return text.toLowerCase()
      .replace(/[ăâîșțşţéè]/g, c => map[c] || c)
      .replace(/[^a-z0-9\s-]/g, '')
      .replace(/[\s-]+/g, '-')
      .replace(/^-+|-+$/g, '');
  }

  function showAdminNotice(msg, type = 'info') {
    const notice = document.createElement('div');
    notice.className = 'admin-notice ' + type;
    notice.textContent = msg;
    document.querySelector('.admin-content')?.prepend(notice);
    setTimeout(() => notice.remove(), 4000);
  }

  window.showAdminNotice = showAdminNotice;

});
