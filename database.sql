-- GENESIS-ULTRA v2 Schema

CREATE TABLE IF NOT EXISTS research_sessions (
    id TEXT PRIMARY KEY,
    topic TEXT,
    status TEXT DEFAULT 'running',
    started_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    completed_at DATETIME
);

CREATE TABLE IF NOT EXISTS research_queries (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    session_id TEXT NOT NULL,
    source TEXT NOT NULL,
    query_term TEXT NOT NULL,
    url TEXT NOT NULL,
    status TEXT DEFAULT 'pending',
    http_code INTEGER,
    response_body TEXT,
    parsed_count INTEGER DEFAULT 0,
    duration_ms INTEGER,
    executed_at DATETIME,
    FOREIGN KEY (session_id) REFERENCES research_sessions(id)
);

CREATE TABLE IF NOT EXISTS research_findings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    session_id TEXT NOT NULL,
    source TEXT NOT NULL,
    title TEXT,
    abstract TEXT,
    url TEXT,
    year TEXT,
    extra TEXT,
    FOREIGN KEY (session_id) REFERENCES research_sessions(id)
);

CREATE TABLE IF NOT EXISTS articles (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    session_id TEXT,
    topic TEXT NOT NULL,
    title TEXT NOT NULL,
    summary TEXT,
    content TEXT,
    sources_used TEXT,
    total_sources INTEGER DEFAULT 0,
    total_findings INTEGER DEFAULT 0,
    word_count INTEGER DEFAULT 0,
    status TEXT DEFAULT 'published',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES research_sessions(id)
);

CREATE INDEX IF NOT EXISTS idx_queries_session ON research_queries(session_id);
CREATE INDEX IF NOT EXISTS idx_findings_session ON research_findings(session_id);
CREATE INDEX IF NOT EXISTS idx_articles_created ON articles(created_at DESC);
