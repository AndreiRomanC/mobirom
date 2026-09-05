/* GhidRomânesc — JavaScript principal */

document.addEventListener('DOMContentLoaded', () => {

  // ─── Nav mobile ─────────────────────────────────────────
  const navToggle = document.querySelector('.nav-toggle');
  const siteNav   = document.querySelector('.site-nav');
  if (navToggle && siteNav) {
    navToggle.addEventListener('click', () => {
      siteNav.classList.toggle('open');
      const open = siteNav.classList.contains('open');
      navToggle.setAttribute('aria-expanded', open);
    });
    document.addEventListener('click', (e) => {
      if (!navToggle.contains(e.target) && !siteNav.contains(e.target)) {
        siteNav.classList.remove('open');
      }
    });
  }

  // ─── Hero search — tag clicks ────────────────────────────
  document.querySelectorAll('.hero-tag').forEach(tag => {
    tag.addEventListener('click', () => {
      const input = document.querySelector('.hero-search-input');
      if (input) { input.value = tag.textContent.trim(); input.focus(); }
    });
  });

  // ─── Hero search submit ──────────────────────────────────
  const heroForm = document.querySelector('.hero-search');
  if (heroForm) {
    heroForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const q = heroForm.querySelector('.hero-search-input')?.value?.trim();
      if (q) window.location.href = `/cauta/?q=${encodeURIComponent(q)}`;
    });
  }

  // ─── Header search ───────────────────────────────────────
  const headerForm = document.querySelector('.header-search');
  if (headerForm) {
    headerForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const q = headerForm.querySelector('.header-search-input')?.value?.trim();
      if (q) window.location.href = `/cauta/?q=${encodeURIComponent(q)}`;
    });
  }

  // ─── Newsletter ──────────────────────────────────────────
  const newsletterForm = document.querySelector('.newsletter-form');
  if (newsletterForm) {
    newsletterForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const email = newsletterForm.querySelector('input[type="email"]')?.value?.trim();
      const btn   = newsletterForm.querySelector('button');
      if (!email) return;

      btn.disabled = true;
      btn.textContent = 'Se trimite...';

      try {
        const res = await fetch('/api/newsletter.php', {
          method: 'POST',
          headers: {'Content-Type': 'application/json'},
          body: JSON.stringify({ email }),
        });
        const data = await res.json();
        if (data.success) {
          newsletterForm.innerHTML = '<p style="color:rgba(255,255,255,.9);font-size:1.05rem">✓ Mulțumim! Verifică emailul pentru confirmare.</p>';
        } else {
          btn.disabled = false;
          btn.textContent = 'Abonează-te';
          alert(data.message || 'A apărut o eroare. Încearcă din nou.');
        }
      } catch {
        btn.disabled = false;
        btn.textContent = 'Abonează-te';
      }
    });
  }

  // ─── Acțiuni articol ────────────────────────────────────
  document.querySelectorAll('.share-whatsapp').forEach(btn => {
    btn.addEventListener('click', () => {
      const url   = encodeURIComponent(window.location.href);
      const title = encodeURIComponent(document.title);
      window.open(`https://wa.me/?text=${title}%20${url}`, '_blank');
    });
  });

  document.querySelectorAll('.print-page').forEach(btn => {
    btn.addEventListener('click', () => window.print());
  });

  document.querySelectorAll('.save-article').forEach(btn => {
    btn.addEventListener('click', () => {
      const saved = JSON.parse(localStorage.getItem('saved_articles') || '[]');
      const url   = window.location.href;
      const title = document.title;
      if (!saved.find(a => a.url === url)) {
        saved.push({ url, title, savedAt: Date.now() });
        localStorage.setItem('saved_articles', JSON.stringify(saved));
        btn.textContent = '✓ Salvat';
        btn.style.borderColor = '#16a34a';
      } else {
        alert('Acest ghid este deja salvat.');
      }
    });
  });

  // ─── Lazy loading imagini ────────────────────────────────
  if ('IntersectionObserver' in window) {
    const imgs = document.querySelectorAll('img[data-src]');
    const io = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const img = entry.target;
          img.src = img.dataset.src;
          img.removeAttribute('data-src');
          io.unobserve(img);
        }
      });
    });
    imgs.forEach(img => io.observe(img));
  }

  // ─── Smooth scroll pentru ancorele interne ───────────────
  document.querySelectorAll('a[href^="#"]').forEach(a => {
    a.addEventListener('click', (e) => {
      const target = document.querySelector(a.getAttribute('href'));
      if (target) {
        e.preventDefault();
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  });

  // ─── Animare la scroll ───────────────────────────────────
  if ('IntersectionObserver' in window) {
    const animated = document.querySelectorAll('.article-card, .category-card, .stat-card');
    const anim = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('fade-in');
          anim.unobserve(entry.target);
        }
      });
    }, { threshold: 0.1 });
    animated.forEach(el => anim.observe(el));
  }

});
