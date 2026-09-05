-- GhidRomânesc — Schema SQLite
-- Compatibil cu SQLite 3.x (disponibil pe orice hosting PHP modern)

PRAGMA journal_mode=WAL;
PRAGMA foreign_keys=ON;
PRAGMA encoding="UTF-8";

-- ─── Utilizatori ──────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS users (
  id         INTEGER PRIMARY KEY AUTOINCREMENT,
  name       TEXT NOT NULL,
  email      TEXT NOT NULL UNIQUE,
  password   TEXT NOT NULL,
  role       TEXT NOT NULL DEFAULT 'autor',
  avatar     TEXT,
  bio        TEXT,
  is_active  INTEGER NOT NULL DEFAULT 1,
  last_login TEXT,
  created_at TEXT NOT NULL DEFAULT (datetime('now','localtime')),
  updated_at TEXT NOT NULL DEFAULT (datetime('now','localtime'))
);

-- ─── Categorii ────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS categories (
  id          INTEGER PRIMARY KEY AUTOINCREMENT,
  slug        TEXT NOT NULL UNIQUE,
  name        TEXT NOT NULL,
  description TEXT,
  icon        TEXT,
  color       TEXT,
  sort_order  INTEGER DEFAULT 0,
  created_at  TEXT NOT NULL DEFAULT (datetime('now','localtime'))
);

-- ─── Articole ─────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS articles (
  id               INTEGER PRIMARY KEY AUTOINCREMENT,
  category_id      INTEGER NOT NULL,
  author_id        INTEGER,
  reviewer_id      INTEGER,

  title            TEXT NOT NULL,
  slug             TEXT NOT NULL,
  excerpt          TEXT,
  content          TEXT,
  article_type     TEXT DEFAULT 'ghid_complet',

  meta_title       TEXT,
  meta_description TEXT,
  og_image         TEXT,
  focus_keyword    TEXT,
  tags             TEXT,

  status           TEXT NOT NULL DEFAULT 'draft',
  risk_level       TEXT NOT NULL DEFAULT 'galben',
  published_at     TEXT,
  scheduled_at     TEXT,

  verified_by      INTEGER,
  verified_at      TEXT,
  review_date      TEXT,
  last_checked_at  TEXT,
  needs_disclaimer INTEGER DEFAULT 0,

  sources          TEXT,
  internal_links   TEXT,

  ai_generated     INTEGER DEFAULT 0,
  ai_model         TEXT,
  ai_prompt_used   TEXT,
  ai_check_result  TEXT,

  views            INTEGER DEFAULT 0,
  shares           INTEGER DEFAULT 0,

  created_at       TEXT NOT NULL DEFAULT (datetime('now','localtime')),
  updated_at       TEXT NOT NULL DEFAULT (datetime('now','localtime')),

  FOREIGN KEY (category_id) REFERENCES categories(id),
  FOREIGN KEY (author_id)   REFERENCES users(id)
);

CREATE INDEX IF NOT EXISTS idx_articles_status       ON articles(status);
CREATE INDEX IF NOT EXISTS idx_articles_category     ON articles(category_id);
CREATE INDEX IF NOT EXISTS idx_articles_slug         ON articles(slug);
CREATE INDEX IF NOT EXISTS idx_articles_published_at ON articles(published_at);
CREATE INDEX IF NOT EXISTS idx_articles_views        ON articles(views DESC);
CREATE INDEX IF NOT EXISTS idx_articles_review_date  ON articles(review_date);

-- ─── Surse articole ───────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS article_sources (
  id           INTEGER PRIMARY KEY AUTOINCREMENT,
  article_id   INTEGER NOT NULL,
  url          TEXT,
  title        TEXT,
  institution  TEXT,
  accessed_at  TEXT,
  trust_level  TEXT DEFAULT 'oficial',
  notes        TEXT,
  FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE
);

-- ─── Newsletter ───────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS newsletter (
  id               INTEGER PRIMARY KEY AUTOINCREMENT,
  email            TEXT NOT NULL UNIQUE,
  name             TEXT,
  is_active        INTEGER DEFAULT 1,
  confirm_token    TEXT,
  confirmed_at     TEXT,
  unsubscribed_at  TEXT,
  ip               TEXT,
  created_at       TEXT NOT NULL DEFAULT (datetime('now','localtime'))
);

-- ─── Căutări interne ──────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS search_queries (
  id            INTEGER PRIMARY KEY AUTOINCREMENT,
  query         TEXT NOT NULL UNIQUE,
  count         INTEGER DEFAULT 1,
  last_searched TEXT DEFAULT (datetime('now','localtime'))
);

-- ─── Raportări erori ──────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS error_reports (
  id          INTEGER PRIMARY KEY AUTOINCREMENT,
  article_id  INTEGER,
  article_url TEXT,
  description TEXT NOT NULL,
  email       TEXT,
  status      TEXT DEFAULT 'new',
  ip          TEXT,
  created_at  TEXT NOT NULL DEFAULT (datetime('now','localtime')),
  FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE SET NULL
);

-- ─── Idei subiecte ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS topic_suggestions (
  id          INTEGER PRIMARY KEY AUTOINCREMENT,
  subject     TEXT NOT NULL,
  description TEXT,
  email       TEXT,
  status      TEXT DEFAULT 'new',
  ip          TEXT,
  created_at  TEXT NOT NULL DEFAULT (datetime('now','localtime'))
);

-- ─── Calendar editorial ───────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS editorial_calendar (
  id           INTEGER PRIMARY KEY AUTOINCREMENT,
  title        TEXT NOT NULL,
  category_id  INTEGER,
  article_id   INTEGER,
  assigned_to  INTEGER,
  status       TEXT DEFAULT 'idee',
  target_date  TEXT,
  notes        TEXT,
  created_at   TEXT NOT NULL DEFAULT (datetime('now','localtime')),
  updated_at   TEXT NOT NULL DEFAULT (datetime('now','localtime')),
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
  FOREIGN KEY (article_id)  REFERENCES articles(id)   ON DELETE SET NULL,
  FOREIGN KEY (assigned_to) REFERENCES users(id)      ON DELETE SET NULL
);

-- ─── Idei trenduri ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS trend_ideas (
  id             INTEGER PRIMARY KEY AUTOINCREMENT,
  title          TEXT NOT NULL,
  category       TEXT,
  keyword        TEXT,
  user_intent    TEXT,
  article_type   TEXT,
  risk_level     TEXT DEFAULT 'galben',
  seo_difficulty TEXT DEFAULT 'medie',
  why_search     TEXT,
  sources        TEXT,
  recommendation TEXT DEFAULT 'pending',
  status         TEXT DEFAULT 'idee',
  generated_at   TEXT DEFAULT (datetime('now','localtime'))
);

CREATE INDEX IF NOT EXISTS idx_trend_status ON trend_ideas(status);

-- ─── Setări ───────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS settings (
  id         INTEGER PRIMARY KEY AUTOINCREMENT,
  key_name   TEXT NOT NULL UNIQUE,
  value      TEXT,
  updated_at TEXT NOT NULL DEFAULT (datetime('now','localtime'))
);

-- ─── Sesiuni admin ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS admin_sessions (
  id         INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id    INTEGER NOT NULL,
  ip         TEXT,
  user_agent TEXT,
  logged_at  TEXT NOT NULL DEFAULT (datetime('now','localtime')),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
