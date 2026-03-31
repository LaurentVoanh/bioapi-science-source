<?php require_once __DIR__ . '/config.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>GENESIS-ULTRA v<?= APP_VERSION ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&family=Lora:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
<style>
/* ─── VARIABLES ──────────────────────────────────────────── */
:root{
  --bg:   #080c12;
  --bg2:  #0d1320;
  --bg3:  #121a28;
  --bg4:  #18223a;
  --line: #1c2d46;
  --line2:#243850;
  --c:    #00e5ff;
  --c2:   #00b4cc;
  --g:    #2eff99;
  --r:    #ff3e55;
  --y:    #ffcc00;
  --txt:  #c0d4e8;
  --txt2: #5a7a99;
  --txt3: #2e4a66;
  --mono: 'Space Mono', monospace;
  --body: 'Lora', Georgia, serif;
}
*{box-sizing:border-box;margin:0;padding:0}
html{font-size:14px}
body{font-family:var(--mono);background:var(--bg);color:var(--txt);min-height:100vh;overflow-x:hidden}

/* ─── SCAN LINES ─────────────────────────────────────────── */
body::after{
  content:'';position:fixed;inset:0;
  background:repeating-linear-gradient(0deg,transparent,transparent 3px,rgba(0,0,0,.04) 3px,rgba(0,0,0,.04) 4px);
  pointer-events:none;z-index:9999
}

/* ─── LAYOUT ─────────────────────────────────────────────── */
.wrap{display:grid;grid-template-columns:400px 1fr;grid-template-rows:48px 1fr;min-height:100vh}
@media(max-width:860px){.wrap{grid-template-columns:1fr}}

/* ─── TOP BAR ────────────────────────────────────────────── */
.topbar{
  grid-column:1/-1;
  background:var(--bg2);
  border-bottom:1px solid var(--line);
  padding:0 1.4rem;
  display:flex;align-items:center;gap:1.2rem;
}
.logo{font-weight:700;font-size:.95rem;letter-spacing:.12em;color:var(--c);text-shadow:0 0 18px rgba(0,229,255,.35)}
.logo-sep{color:var(--txt3)}
.topbar-info{font-size:.65rem;color:var(--txt2);display:flex;gap:1.4rem}
.topbar-info b{color:var(--c2)}

/* ─── LEFT ───────────────────────────────────────────────── */
.left{
  background:var(--bg2);
  border-right:1px solid var(--line);
  display:flex;flex-direction:column;
  overflow:hidden;min-height:0
}

/* ─── LAUNCH AREA ────────────────────────────────────────── */
.launch{padding:1rem 1.2rem;border-bottom:1px solid var(--line)}
#btnLaunch{
  width:100%;padding:.75rem;
  font-family:var(--mono);font-size:.8rem;font-weight:700;letter-spacing:.12em;
  text-transform:uppercase;cursor:pointer;
  background:transparent;color:var(--c);
  border:1px solid var(--c);
  position:relative;overflow:hidden;transition:color .25s
}
#btnLaunch::before{
  content:'';position:absolute;inset:0;
  background:var(--c);transform:scaleX(0);transform-origin:left;
  transition:transform .3s ease;z-index:0
}
#btnLaunch:hover::before{transform:scaleX(1)}
#btnLaunch:hover{color:var(--bg)}
#btnLaunch span{position:relative;z-index:1}
#btnLaunch:disabled{opacity:.35;cursor:not-allowed}
#btnLaunch:disabled::before{display:none}
#btnLaunch:disabled:hover{color:var(--c)}

.pbar-wrap{margin-top:.7rem}
.pbar-top{display:flex;justify-content:space-between;font-size:.6rem;color:var(--txt2);margin-bottom:.25rem}
.pbar{height:2px;background:var(--line);overflow:hidden}
.pbar-fill{height:100%;background:linear-gradient(90deg,var(--c2),var(--c),var(--g));width:0%;transition:width .45s;box-shadow:0 0 6px rgba(0,229,255,.5)}
.phase{font-size:.62rem;color:var(--txt3);margin-top:.3rem;min-height:1.1em;font-style:italic}

/* ─── SOURCE GRID ────────────────────────────────────────── */
.src-wrap{
  padding:.7rem 1.2rem .4rem;
  border-bottom:1px solid var(--line);
  flex-shrink:0
}
.src-label{font-size:.58rem;color:var(--txt3);letter-spacing:.08em;text-transform:uppercase;margin-bottom:.4rem}
.src-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:2px}
.sc{
  height:18px;font-size:.5rem;
  display:flex;align-items:center;justify-content:center;
  border:1px solid var(--line);border-radius:1px;
  color:var(--txt3);background:var(--bg3);
  overflow:hidden;white-space:nowrap;padding:0 1px;
  transition:all .25s;cursor:default
}
.sc.run{border-color:var(--y);color:var(--y);background:rgba(255,204,0,.05);animation:blink .7s infinite}
.sc.ok {border-color:var(--g);color:var(--g);background:rgba(46,255,153,.05)}
.sc.err{border-color:var(--r);color:var(--r);background:rgba(255,62,85,.05)}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.4}}

/* ─── TERMINAL ───────────────────────────────────────────── */
.term{
  flex:1;min-height:0;overflow-y:auto;
  padding:.6rem 1.2rem;
  font-size:.68rem;line-height:1.85;
  background:#05080e
}
.term::-webkit-scrollbar{width:3px}
.term::-webkit-scrollbar-thumb{background:var(--line2)}

.tl{display:flex;gap:.4rem;border-bottom:1px solid rgba(28,45,70,.4);padding:.05rem 0}
.tl-ts{color:var(--txt3);flex-shrink:0;width:50px}
.tl-step{color:var(--c2);flex-shrink:0;min-width:76px;overflow:hidden}
.tl-msg{color:var(--txt)}
.tl.ok   .tl-msg{color:var(--g)}
.tl.err  .tl-msg{color:var(--r)}
.tl.info .tl-msg{color:var(--txt2)}
.tl.big  .tl-msg{color:var(--y);font-weight:700}

/* ─── RIGHT ──────────────────────────────────────────────── */
.right{display:flex;flex-direction:column;overflow:hidden;min-height:0}
.right-head{
  padding:.6rem 1.4rem;
  border-bottom:1px solid var(--line);
  background:var(--bg2);
  display:flex;align-items:center;gap:.8rem
}
.right-head h2{font-size:.85rem;font-weight:400;color:var(--txt2);font-style:italic}
#artBadge{
  margin-left:auto;font-size:.6rem;
  padding:.12rem .45rem;
  border:1px solid var(--line2);color:var(--c2);
  background:rgba(0,229,255,.05);border-radius:1px
}

.art-list{flex:1;min-height:0;overflow-y:auto;padding:1rem 1.4rem;display:flex;flex-direction:column;gap:.5rem}
.art-list::-webkit-scrollbar{width:3px}
.art-list::-webkit-scrollbar-thumb{background:var(--line2)}

.empty{
  display:flex;flex-direction:column;align-items:center;justify-content:center;
  height:100%;gap:.8rem;color:var(--txt3);font-size:.75rem;text-align:center
}
.empty-ico{font-size:2.5rem;opacity:.2}

/* ─── ARTICLE CARD ───────────────────────────────────────── */
.acard{
  background:var(--bg3);
  border:1px solid var(--line);border-left:2px solid var(--line2);
  padding:.8rem 1rem;cursor:pointer;
  transition:border-left-color .2s,background .2s
}
.acard:hover{border-left-color:var(--c);background:rgba(0,229,255,.02)}
.acard-title{
  font-family:var(--body);font-size:.95rem;font-weight:600;
  color:#ddeeff;line-height:1.35;margin-bottom:.35rem
}
.acard-meta{font-size:.6rem;color:var(--txt3);display:flex;gap:.9rem;margin-bottom:.35rem;flex-wrap:wrap}
.acard-meta .chip{color:var(--c2);border:1px solid var(--line2);padding:.05rem .3rem}
.acard-sum{font-size:.72rem;color:var(--txt2);line-height:1.5;
  display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}

/* ─── MODAL ──────────────────────────────────────────────── */
#modal{
  position:fixed;inset:0;
  background:rgba(0,0,0,.82);
  backdrop-filter:blur(4px);
  display:none;align-items:flex-start;justify-content:center;
  z-index:500;padding:2rem 1rem;overflow-y:auto
}
#modal.open{display:flex}
.mbox{
  background:var(--bg2);
  border:1px solid var(--line2);
  width:100%;max-width:920px;margin:auto;
}
.mhead{
  padding:1rem 1.4rem;border-bottom:1px solid var(--line);
  display:flex;align-items:flex-start;gap:1rem
}
.mtitle{
  font-family:var(--body);font-size:1.25rem;font-weight:600;
  color:#eef4ff;line-height:1.35;flex:1
}
.mbtn-close{
  background:none;border:1px solid var(--line2);
  color:var(--txt2);width:26px;height:26px;
  font-family:var(--mono);cursor:pointer;font-size:.75rem;flex-shrink:0;margin-top:2px
}
.mbtn-close:hover{border-color:var(--r);color:var(--r)}

.mstats{
  display:flex;flex-wrap:wrap;gap:1rem;
  padding:.55rem 1.4rem;
  background:rgba(0,229,255,.02);
  border-bottom:1px solid var(--line);
  font-size:.64rem;color:var(--txt2)
}
.mstats b{color:var(--c)}

.mbody{
  padding:1.4rem;max-height:68vh;overflow-y:auto;
  line-height:1.85
}
.mbody::-webkit-scrollbar{width:3px}
.mbody::-webkit-scrollbar-thumb{background:var(--line2)}

/* ─── MARKDOWN ───────────────────────────────────────────── */
.md h2{
  font-family:var(--body);font-size:1.1rem;font-weight:600;
  color:var(--c);border-bottom:1px solid var(--line);
  padding-bottom:.3rem;margin:1.4rem 0 .6rem;letter-spacing:.01em
}
.md h3{font-family:var(--body);font-size:.95rem;color:var(--y);margin:1.1rem 0 .4rem;font-weight:600}
.md h4{font-size:.85rem;color:var(--txt);margin:.9rem 0 .3rem;font-weight:700}
.md p{
  font-family:var(--body);font-size:.88rem;font-weight:400;
  color:var(--txt);margin-bottom:.85rem;line-height:1.85
}
.md strong{color:#ddeeff;font-weight:600}
.md em{color:var(--txt2)}
.md ul,.md ol{margin:.4rem 0 .9rem 1.4rem}
.md li{font-family:var(--body);font-size:.86rem;color:var(--txt);margin-bottom:.25rem;line-height:1.7}
.md code{
  font-family:var(--mono);font-size:.78em;
  background:rgba(0,229,255,.06);border:1px solid var(--line);
  padding:.1rem .35rem;border-radius:1px;color:var(--c2)
}
.md blockquote{
  border-left:2px solid var(--c2);padding:.4rem .9rem;margin:.7rem 0;
  background:rgba(0,229,255,.03);color:var(--txt2);
  font-family:var(--body);font-style:italic
}

.msrc{
  display:flex;flex-wrap:wrap;gap:.25rem;
  padding:.7rem 1.4rem;border-top:1px solid var(--line);
  background:var(--bg)
}
.msrc-pill{
  font-size:.56rem;padding:.12rem .4rem;
  border:1px solid var(--line2);color:var(--txt3);
  background:var(--bg3);border-radius:1px
}

/* ─── TOAST ──────────────────────────────────────────────── */
.toast{
  position:fixed;bottom:1.2rem;right:1.2rem;
  font-size:.72rem;padding:.6rem 1rem;
  background:var(--bg3);border:1px solid var(--g);color:var(--g);
  border-radius:1px;z-index:9998;
  animation:tIn .2s ease
}
.toast.err{border-color:var(--r);color:var(--r)}
@keyframes tIn{from{transform:translateY(6px);opacity:0}to{transform:translateY(0);opacity:1}}
</style>
</head>
<body>
<div class="wrap">

<!-- TOP BAR -->
<header class="topbar">
  <span class="logo">GENESIS-ULTRA</span>
  <span class="logo-sep">|</span>
  <div class="topbar-info">
    <span>v<?= APP_VERSION ?></span>
    <span>Sources: <b><?= count(SOURCES) ?></b></span>
    <span>Modèle: <b>Mistral-Large</b></span>
  </div>
</header>

<!-- LEFT PANEL -->
<aside class="left">

  <!-- LAUNCH -->
  <div class="launch">
    <button id="btnLaunch" onclick="run()"><span>⬡ LANCER LA RECHERCHE</span></button>
    <div class="pbar-wrap">
      <div class="pbar-top">
        <span id="pLabel">En attente</span>
        <span id="pPct">0%</span>
      </div>
      <div class="pbar"><div class="pbar-fill" id="pFill"></div></div>
      <div class="phase" id="pPhase"><?= count(SOURCES) ?> sources prêtes</div>
    </div>
  </div>

  <!-- SOURCE GRID -->
  <div class="src-wrap">
    <div class="src-label">État des <?= count(SOURCES) ?> sources</div>
    <div class="src-grid" id="srcGrid">
      <?php foreach (array_keys(SOURCES) as $n): ?>
      <div class="sc" id="sc-<?= htmlspecialchars($n) ?>" title="<?= htmlspecialchars($n) ?>">
        <?= htmlspecialchars(substr($n, 0, 9)) ?>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- TERMINAL -->
  <div class="term" id="term">
    <div class="tl info">
      <span class="tl-ts">--:--:--</span>
      <span class="tl-step">[BOOT]</span>
      <span class="tl-msg">Prêt · <?= count(SOURCES) ?> sources configurées</span>
    </div>
  </div>

</aside>

<!-- RIGHT PANEL -->
<main class="right">
  <div class="right-head">
    <h2>Archives de recherche</h2>
    <span id="artBadge">0 articles</span>
  </div>
  <div class="art-list" id="artList">
    <div class="empty">
      <div class="empty-ico">◎</div>
      <div>Aucun article généré.</div>
      <div style="font-size:.6rem;color:var(--txt3)">
        Cliquez sur LANCER — l'IA choisit un sujet,<br>
        interroge <?= count(SOURCES) ?> sources et rédige<br>
        un article de synthèse ≥ 3000 mots.
      </div>
    </div>
  </div>
</main>

</div><!-- .wrap -->

<!-- MODAL -->
<div id="modal" onclick="if(event.target===this)closeModal()">
  <div class="mbox">
    <div class="mhead">
      <div class="mtitle" id="mTitle">—</div>
      <button class="mbtn-close" onclick="closeModal()">✕</button>
    </div>
    <div class="mstats" id="mStats"></div>
    <div class="mbody"><div class="md" id="mBody"></div></div>
    <div class="msrc" id="mSrc"></div>
  </div>
</div>

<script>
const API = 'api.php';
const SOURCES = <?= json_encode(array_keys(SOURCES)) ?>;
let busy = false;

// ── API call ────────────────────────────────────────────────
async function api(action, data = {}) {
  const isGet = ['health','get_articles','get_article'].includes(action);
  const p = new URLSearchParams({ action, ...data });
  try {
    const r = await fetch(
      isGet ? `${API}?${p}` : API,
      isGet
        ? { headers: { Accept: 'application/json' } }
        : { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: p }
    );
    const ct = r.headers.get('content-type') || '';
    if (!ct.includes('json')) {
      const txt = await r.text();
      throw new Error('Non-JSON: ' + txt.slice(0, 160));
    }
    return await r.json();
  } catch(e) {
    tlog('❌ ' + e.message, 'NET', 'err');
    return { success: false, error: e.message };
  }
}

// ── Terminal log ─────────────────────────────────────────────
function tlog(msg, step = '···', type = 'ok') {
  const el = document.getElementById('term');
  const d = document.createElement('div');
  d.className = 'tl ' + type;
  const now = new Date();
  const ts = [now.getHours(), now.getMinutes(), now.getSeconds()].map(x => String(x).padStart(2,'0')).join(':');
  d.innerHTML = `<span class="tl-ts">${ts}</span><span class="tl-step">[${step.slice(0,11)}]</span><span class="tl-msg">${msg}</span>`;
  el.appendChild(d);
  el.scrollTop = el.scrollHeight;
}

// ── Progress ─────────────────────────────────────────────────
function prog(pct, label, phase) {
  document.getElementById('pFill').style.width = pct + '%';
  document.getElementById('pPct').textContent  = pct + '%';
  if (label) document.getElementById('pLabel').textContent = label;
  if (phase) document.getElementById('pPhase').textContent = phase;
}

// ── Source cell state ─────────────────────────────────────────
function sc(name, state) {
  const el = document.getElementById('sc-' + name);
  if (el) el.className = 'sc ' + state;
}

// ── MAIN PIPELINE ─────────────────────────────────────────────
async function run() {
  if (busy) return;
  busy = true;
  const btn = document.getElementById('btnLaunch');
  btn.disabled = true;
  SOURCES.forEach(s => sc(s, ''));

  tlog('══════════════════════════════', 'START', 'big');
  prog(2, 'Démarrage…', 'Initialisation base de données');

  // ── 1. Choisir le sujet ───────────────────────────────────
  prog(4, 'IA réfléchit…', '🤖 Mistral choisit le sujet de recherche');
  tlog('Interrogation Mistral: choix du sujet…', 'TOPIC', 'info');

  const s1 = await api('step_pick_topic');
  if (!s1.success) { tlog('❌ ' + s1.error, 'TOPIC', 'err'); btn.disabled = false; busy = false; return; }

  const { session_id: sid, topic } = s1.data;
  tlog(`✓ Sujet: "${topic}"`, 'TOPIC', 'big');
  prog(8, 'Sujet choisi', `📌 "${topic}"`);

  // ── 2. Préparer les requêtes ──────────────────────────────
  tlog('Génération des termes de recherche…', 'PREP', 'info');
  prog(10, 'Préparation…', '🔎 Optimisation des requêtes pour 36 sources');

  const s2 = await api('step_prepare_queries', { session_id: sid, topic });
  if (!s2.success) { tlog('❌ ' + s2.error, 'PREP', 'err'); btn.disabled = false; busy = false; return; }

  const { term, queries } = s2.data;
  tlog(`✓ Terme: "${term}" · ${queries.length} requêtes prêtes`, 'PREP');
  prog(13, `${queries.length} requêtes`, `Terme de recherche: "${term}"`);

  // ── 3. Exécuter les requêtes ──────────────────────────────
  tlog('══ INTERROGATION DES 36 SOURCES ══', 'FETCH', 'big');
  let okCount = 0, hitTotal = 0;

  for (let i = 0; i < queries.length; i++) {
    const q = queries[i];
    const pct = 13 + Math.round((i / queries.length) * 62);
    prog(pct, `${i+1}/${queries.length}`, `⬡ ${q.source}`);
    sc(q.source, 'run');

    const r = await api('step_exec_query', { query_id: q.id });

    if (r.success && r.data.ok) {
      sc(q.source, 'ok');
      okCount++;
      hitTotal += r.data.hits || 0;
      tlog(`✓ ${q.source} · ${r.data.code} · ${r.data.ms}ms · ${r.data.hits} résultats`, 'FETCH', 'ok');
    } else {
      sc(q.source, 'err');
      tlog(`✗ ${q.source} · ${r.data?.code || '?'} · ${r.data?.ms || 0}ms`, 'FETCH', 'err');
    }

    await sleep(250); // petit délai pour ne pas rate-limiter les APIs
  }

  tlog(`══ ${okCount}/${queries.length} sources · ${hitTotal} données collectées ══`, 'DONE', 'big');
  prog(78, 'Collecte finie', `✓ ${okCount} sources · ${hitTotal} résultats · Rédaction…`);

  // ── 4. Rédiger l'article ──────────────────────────────────
  tlog('Envoi à Mistral-Large pour synthèse…', 'WRITE', 'info');
  tlog(`Analyse de ${hitTotal} résultats, rédaction en cours…`, 'WRITE', 'info');
  prog(82, 'Rédaction IA…', '✍ Mistral synthétise les données');

  const s4 = await api('step_write_article', { session_id: sid, topic });
  if (!s4.success) { tlog('❌ ' + s4.error, 'WRITE', 'err'); btn.disabled = false; busy = false; return; }

  const { title, word_count, sources_ok, total_hits, article_id } = s4.data;
  prog(100, 'Terminé ✓', `📄 "${title}" · ${word_count} mots`);
  tlog('══════════════════════════════', 'END', 'big');
  tlog(`✓ Article #${article_id}: "${title}"`, 'END', 'big');
  tlog(`✓ ${word_count} mots · ${sources_ok} sources · ${total_hits} données`, 'STATS');

  await loadArticles();
  toast(`✓ Article publié · ${word_count} mots`);
  btn.disabled = false;
  busy = false;
}

// ── Load articles ─────────────────────────────────────────────
async function loadArticles() {
  const r = await api('get_articles');
  if (!r.success) return;
  const arts = r.data || [];
  document.getElementById('artBadge').textContent = arts.length + ' article' + (arts.length !== 1 ? 's' : '');

  const list = document.getElementById('artList');
  if (!arts.length) {
    list.innerHTML = '<div class="empty"><div class="empty-ico">◎</div><div>Aucun article généré.</div></div>';
    return;
  }
  list.innerHTML = arts.map(a => `
    <div class="acard" onclick="openArticle(${a.id})">
      <div class="acard-title">${esc(a.title)}</div>
      <div class="acard-meta">
        <span class="chip">📌 ${esc(a.topic)}</span>
        <span>${a.word_count || '?'} mots</span>
        <span>${a.sources_ok || 0} sources</span>
        <span>${a.total_hits || 0} données</span>
        <span>${fmtDate(a.created_at)}</span>
      </div>
      <div class="acard-sum">${esc(a.summary || '')}</div>
    </div>
  `).join('');
}

// ── Open article ──────────────────────────────────────────────
async function openArticle(id) {
  const r = await api('get_article', { id });
  if (!r.success) return;
  const { article: a, by_source } = r.data;

  document.getElementById('mTitle').textContent = a.title;
  document.getElementById('mStats').innerHTML = `
    <span>📌 <b>${esc(a.topic)}</b></span>
    <span>📊 <b>${a.word_count}</b> mots</span>
    <span>🔬 <b>${a.sources_ok}</b> sources actives</span>
    <span>📋 <b>${a.total_hits}</b> données collectées</span>
    <span>🕒 ${fmtDate(a.created_at)}</span>
  `;
  document.getElementById('mBody').innerHTML = md(a.content || '');
  document.getElementById('mSrc').innerHTML = (by_source || [])
    .map(s => `<span class="msrc-pill">${esc(s.source)} (${s.cnt})</span>`)
    .join('') || '<span class="msrc-pill">—</span>';

  document.getElementById('modal').classList.add('open');
}

function closeModal() { document.getElementById('modal').classList.remove('open'); }

// ── Markdown renderer ─────────────────────────────────────────
function md(s) {
  if (!s) return '';
  return s
    .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
    .replace(/^## (.+)$/gm, '<h2>$1</h2>')
    .replace(/^### (.+)$/gm, '<h3>$1</h3>')
    .replace(/^#### (.+)$/gm, '<h4>$1</h4>')
    .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
    .replace(/\*(.+?)\*/g, '<em>$1</em>')
    .replace(/`([^`]+)`/g, '<code>$1</code>')
    .replace(/^&gt; (.+)$/gm, '<blockquote>$1</blockquote>')
    .replace(/^[-*] (.+)$/gm, '<li>$1</li>')
    .replace(/(<li>.*?<\/li>(\n|$))+/gs, m => '<ul>' + m + '</ul>')
    .split(/\n{2,}/).map(b => {
      b = b.trim();
      if (!b || /^<(h[2-4]|ul|blockquote)/.test(b)) return b;
      return '<p>' + b.replace(/\n/g,'<br>') + '</p>';
    }).join('\n');
}

// ── Helpers ───────────────────────────────────────────────────
function esc(s){ if(!s)return''; const d=document.createElement('div'); d.textContent=s; return d.innerHTML; }
function fmtDate(dt){ if(!dt)return'—'; try{return new Date(dt).toLocaleDateString('fr-FR',{day:'2-digit',month:'short',year:'2-digit',hour:'2-digit',minute:'2-digit'})}catch{return dt} }
function sleep(ms){ return new Promise(r=>setTimeout(r,ms)); }
function toast(msg,err=false){ const t=document.createElement('div'); t.className='toast'+(err?' err':''); t.textContent=msg; document.body.appendChild(t); setTimeout(()=>t.remove(),4000); }

// ── Init ──────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', loadArticles);
</script>
</body>
</html>
