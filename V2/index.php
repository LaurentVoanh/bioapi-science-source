<?php require_once __DIR__ . '/config.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>GENESIS-ULTRA v<?= APP_VERSION ?></title>
<link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&family=Lora:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
<style>
:root{
  --bg:#080c12;--bg2:#0d1320;--bg3:#121a28;--bg4:#18223a;
  --line:#1c2d46;--line2:#243850;
  --c:#00e5ff;--c2:#00b4cc;--g:#2eff99;--r:#ff3e55;--y:#ffcc00;--o:#ff8c00;
  --txt:#c0d4e8;--txt2:#5a7a99;--txt3:#2e4a66;
  --mono:'Space Mono',monospace;--body:'Lora',Georgia,serif;
}
*{box-sizing:border-box;margin:0;padding:0}
html{font-size:14px}
body{font-family:var(--mono);background:var(--bg);color:var(--txt);min-height:100vh;overflow-x:hidden}
body::after{content:'';position:fixed;inset:0;background:repeating-linear-gradient(0deg,transparent,transparent 3px,rgba(0,0,0,.04) 3px,rgba(0,0,0,.04) 4px);pointer-events:none;z-index:9999}

/* ─── LAYOUT ── */
.wrap{display:grid;grid-template-columns:400px 1fr;grid-template-rows:48px 1fr;min-height:100vh}
@media(max-width:860px){.wrap{grid-template-columns:1fr}}

/* ─── TOPBAR ── */
.topbar{grid-column:1/-1;background:var(--bg2);border-bottom:1px solid var(--line);padding:0 1.4rem;display:flex;align-items:center;gap:1.2rem}
.logo{font-weight:700;font-size:.95rem;letter-spacing:.12em;color:var(--c);text-shadow:0 0 18px rgba(0,229,255,.35)}
.topbar-info{font-size:.65rem;color:var(--txt2);display:flex;gap:1.4rem}
.topbar-info b{color:var(--c2)}

/* ─── LEFT ── */
.left{background:var(--bg2);border-right:1px solid var(--line);display:flex;flex-direction:column;overflow:hidden;min-height:0}

/* ─── LAUNCH AREA ── */
.launch{padding:1rem 1.2rem;border-bottom:1px solid var(--line)}

.question-wrap{margin-bottom:.65rem}
.question-wrap label{display:block;font-size:.58rem;color:var(--txt3);letter-spacing:.08em;text-transform:uppercase;margin-bottom:.3rem}
.question-input{
  width:100%;background:var(--bg3);border:1px solid var(--line2);color:var(--txt);
  font-family:var(--mono);font-size:.72rem;padding:.5rem .7rem;resize:vertical;
  min-height:52px;max-height:110px;
}
.question-input::placeholder{color:var(--txt3)}
.question-input:focus{outline:none;border-color:var(--c2)}

.btn-row{display:grid;grid-template-columns:1fr;gap:.4rem;margin-bottom:.65rem}

.btn{
  width:100%;padding:.65rem;
  font-family:var(--mono);font-size:.72rem;font-weight:700;letter-spacing:.1em;
  text-transform:uppercase;cursor:pointer;
  background:transparent;border:1px solid;
  position:relative;overflow:hidden;transition:color .25s
}
.btn::before{content:'';position:absolute;inset:0;transform:scaleX(0);transform-origin:left;transition:transform .3s ease;z-index:0}
.btn span{position:relative;z-index:1}
.btn:disabled{opacity:.35;cursor:not-allowed}
.btn:disabled::before{display:none}

.btn-launch{color:var(--c);border-color:var(--c)}
.btn-launch::before{background:var(--c)}
.btn-launch:hover:not(:disabled)::before{transform:scaleX(1)}
.btn-launch:hover:not(:disabled){color:var(--bg)}

.pbar-wrap{margin-top:.5rem}
.pbar-top{display:flex;justify-content:space-between;font-size:.6rem;color:var(--txt2);margin-bottom:.25rem}
.pbar{height:2px;background:var(--line);overflow:hidden}
.pbar-fill{height:100%;background:linear-gradient(90deg,var(--c2),var(--c),var(--g));width:0%;transition:width .45s;box-shadow:0 0 6px rgba(0,229,255,.5)}
.phase{font-size:.62rem;color:var(--txt3);margin-top:.3rem;min-height:1.1em;font-style:italic}

/* ─── SOURCE GRID ── */
.src-wrap{padding:.7rem 1.2rem .4rem;border-bottom:1px solid var(--line);flex-shrink:0}
.src-label{font-size:.58rem;color:var(--txt3);letter-spacing:.08em;text-transform:uppercase;margin-bottom:.4rem}
.src-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:2px}
.sc{height:18px;font-size:.5rem;display:flex;align-items:center;justify-content:center;border:1px solid var(--line);border-radius:1px;color:var(--txt3);background:var(--bg3);overflow:hidden;white-space:nowrap;padding:0 1px;transition:all .25s;cursor:default}
.sc.run{border-color:var(--y);color:var(--y);background:rgba(255,204,0,.05);animation:blink .7s infinite}
.sc.ok {border-color:var(--g);color:var(--g);background:rgba(46,255,153,.05)}
.sc.err{border-color:var(--r);color:var(--r);background:rgba(255,62,85,.05)}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.4}}

/* ─── TERMINAL ── */
.term{flex:1;min-height:0;overflow-y:auto;padding:.6rem 1.2rem;font-size:.68rem;line-height:1.85;background:#05080e}
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
.tl.deep .tl-msg{color:var(--o)}

/* ─── RIGHT ── */
.right{display:flex;flex-direction:column;overflow:hidden;min-height:0}
.right-head{padding:.6rem 1.4rem;border-bottom:1px solid var(--line);background:var(--bg2);display:flex;align-items:center;gap:.8rem}
.right-head h2{font-size:.85rem;font-weight:400;color:var(--txt2);font-style:italic}
#artBadge{margin-left:auto;font-size:.6rem;padding:.12rem .45rem;border:1px solid var(--line2);color:var(--c2);background:rgba(0,229,255,.05);border-radius:1px}
.art-list{flex:1;min-height:0;overflow-y:auto;padding:1rem 1.4rem;display:flex;flex-direction:column;gap:.5rem}
.art-list::-webkit-scrollbar{width:3px}
.art-list::-webkit-scrollbar-thumb{background:var(--line2)}
.empty{display:flex;flex-direction:column;align-items:center;justify-content:center;height:100%;gap:.8rem;color:var(--txt3);font-size:.75rem;text-align:center}
.empty-ico{font-size:2.5rem;opacity:.2}

/* ─── ARTICLE CARD ── */
.acard{background:var(--bg3);border:1px solid var(--line);border-left:2px solid var(--line2);padding:.8rem 1rem;cursor:pointer;transition:border-left-color .2s,background .2s}
.acard:hover{border-left-color:var(--c);background:rgba(0,229,255,.02)}
.acard-title{font-family:var(--body);font-size:.95rem;font-weight:600;color:#ddeeff;line-height:1.35;margin-bottom:.35rem}
.acard-meta{font-size:.6rem;color:var(--txt3);display:flex;gap:.9rem;margin-bottom:.35rem;flex-wrap:wrap}
.acard-meta .chip{color:var(--c2);border:1px solid var(--line2);padding:.05rem .3rem}
.acard-meta .chip-deep{color:var(--o);border-color:rgba(255,140,0,.3);padding:.05rem .3rem}
.acard-sum{font-size:.72rem;color:var(--txt2);line-height:1.5;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}

/* ─── MODAL ── */
#modal{position:fixed;inset:0;background:rgba(0,0,0,.82);backdrop-filter:blur(4px);display:none;align-items:flex-start;justify-content:center;z-index:500;padding:2rem 1rem;overflow-y:auto}
#modal.open{display:flex}
.mbox{background:var(--bg2);border:1px solid var(--line2);width:100%;max-width:940px;margin:auto}
.mhead{padding:1rem 1.4rem;border-bottom:1px solid var(--line);display:flex;align-items:flex-start;gap:1rem}
.mtitle{font-family:var(--body);font-size:1.25rem;font-weight:600;color:#eef4ff;line-height:1.35;flex:1}
.mhead-btns{display:flex;gap:.5rem;align-items:flex-start;flex-shrink:0}
.mbtn-close{background:none;border:1px solid var(--line2);color:var(--txt2);width:26px;height:26px;font-family:var(--mono);cursor:pointer;font-size:.75rem}
.mbtn-close:hover{border-color:var(--r);color:var(--r)}
.mbtn-deep{
  background:none;border:1px solid rgba(255,140,0,.4);color:var(--o);
  font-family:var(--mono);cursor:pointer;font-size:.6rem;padding:.2rem .6rem;
  white-space:nowrap;transition:all .2s;letter-spacing:.05em
}
.mbtn-deep:hover{background:rgba(255,140,0,.1)}
.mbtn-deep:disabled{opacity:.35;cursor:not-allowed}

.mstats{display:flex;flex-wrap:wrap;gap:1rem;padding:.55rem 1.4rem;background:rgba(0,229,255,.02);border-bottom:1px solid var(--line);font-size:.64rem;color:var(--txt2)}
.mstats b{color:var(--c)}

.mdeep-status{padding:.5rem 1.4rem;background:rgba(255,140,0,.04);border-bottom:1px solid rgba(255,140,0,.15);font-size:.65rem;color:var(--o);display:none}
.mdeep-status.active{display:block}

.mbody{padding:1.4rem;max-height:60vh;overflow-y:auto;line-height:1.85}
.mbody::-webkit-scrollbar{width:3px}
.mbody::-webkit-scrollbar-thumb{background:var(--line2)}

/* ─── MARKDOWN ── */
.md h2{font-family:var(--body);font-size:1.1rem;font-weight:600;color:var(--c);border-bottom:1px solid var(--line);padding-bottom:.3rem;margin:1.4rem 0 .6rem;letter-spacing:.01em}
.md h3{font-family:var(--body);font-size:.95rem;color:var(--y);margin:1.1rem 0 .4rem;font-weight:600}
.md h4{font-size:.85rem;color:var(--txt);margin:.9rem 0 .3rem;font-weight:700}
.md p{font-family:var(--body);font-size:.88rem;font-weight:400;color:var(--txt);margin-bottom:.85rem;line-height:1.85}
.md strong{color:#ddeeff;font-weight:600}
.md em{color:var(--txt2)}
.md ul,.md ol{margin:.4rem 0 .9rem 1.4rem}
.md li{font-family:var(--body);font-size:.86rem;color:var(--txt);margin-bottom:.25rem;line-height:1.7}
.md code{font-family:var(--mono);font-size:.78em;background:rgba(0,229,255,.06);border:1px solid var(--line);padding:.1rem .35rem;border-radius:1px;color:var(--c2)}
.md blockquote{border-left:2px solid var(--c2);padding:.4rem .9rem;margin:.7rem 0;background:rgba(0,229,255,.03);color:var(--txt2);font-family:var(--body);font-style:italic}
.md a{color:var(--c2);text-decoration:none;border-bottom:1px solid rgba(0,180,204,.3)}
.md a:hover{color:var(--c);border-bottom-color:var(--c)}
.md table{width:100%;border-collapse:collapse;margin:.8rem 0;font-size:.8rem}
.md th{background:var(--bg3);border:1px solid var(--line);padding:.4rem .6rem;color:var(--c2);font-weight:700;text-align:left}
.md td{border:1px solid var(--line);padding:.35rem .6rem;color:var(--txt)}

/* ─── DEEP ANALYSIS SECTION ── */
.deep-section{border-top:2px solid rgba(255,140,0,.3);padding:1rem 1.4rem;background:rgba(255,140,0,.02)}
.deep-label{font-size:.6rem;text-transform:uppercase;letter-spacing:.1em;color:var(--o);margin-bottom:.8rem;display:flex;align-items:center;gap:.4rem}

/* ─── SOURCES PANEL ── */
.msrc-panel{border-top:1px solid var(--line);background:var(--bg)}
.msrc-toggle{width:100%;background:none;border:none;cursor:pointer;padding:.5rem 1.4rem;text-align:left;font-family:var(--mono);font-size:.6rem;color:var(--txt2);letter-spacing:.06em;display:flex;align-items:center;gap:.4rem}
.msrc-toggle:hover{color:var(--c)}
.msrc-body{display:none;padding:.5rem 1.4rem .8rem;flex-wrap:wrap;gap:.3rem}
.msrc-body.open{display:flex}
.msrc-pill{font-size:.56rem;padding:.12rem .4rem;border:1px solid var(--line2);color:var(--txt3);background:var(--bg3);border-radius:1px}
.msrc-link{color:var(--c2);text-decoration:none;border-bottom:1px solid rgba(0,180,204,.2);font-family:var(--mono);font-size:.56rem;padding:.1rem .3rem;display:inline-block}
.msrc-link:hover{color:var(--c)}

/* ─── TOAST ── */
.toast{position:fixed;bottom:1.2rem;right:1.2rem;font-size:.72rem;padding:.6rem 1rem;background:var(--bg3);border:1px solid var(--g);color:var(--g);border-radius:1px;z-index:9998;animation:tIn .2s ease}
.toast.err{border-color:var(--r);color:var(--r)}
.toast.warn{border-color:var(--o);color:var(--o)}
@keyframes tIn{from{transform:translateY(6px);opacity:0}to{transform:translateY(0);opacity:1}}
</style>
</head>
<body>
<div class="wrap">

<!-- TOP BAR -->
<header class="topbar">
  <span class="logo">GENESIS-ULTRA</span>
  <span style="color:var(--txt3)">|</span>
  <div class="topbar-info">
    <span>v<?= APP_VERSION ?></span>
    <span>Sources: <b><?= count(SOURCES) ?></b></span>
    <span>Modèle: <b>Mistral Large</b></span>
  </div>
</header>

<!-- LEFT PANEL -->
<aside class="left">
  <div class="launch">
    <!-- Champ question libre -->
    <div class="question-wrap">
      <label>Question ou sujet (optionnel)</label>
      <textarea id="questionInput" class="question-input"
        placeholder="Ex: Quels sont les biomarqueurs de la myocardite post-vaccin ARNm ?&#10;Ou laissez vide pour une sélection automatique par l'IA…"></textarea>
    </div>

    <div class="btn-row">
      <button id="btnLaunch" class="btn btn-launch" onclick="run()">
        <span>⬡ LANCER LA RECHERCHE</span>
      </button>
    </div>

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
        Posez une question ou cliquez sur LANCER — l'IA choisit un sujet,<br>
        interroge <?= count(SOURCES) ?> sources et rédige<br>
        un article de synthèse ≥ 3000 mots.
      </div>
    </div>
  </div>
</main>

</div>

<!-- MODAL -->
<div id="modal" onclick="if(event.target===this)closeModal()">
  <div class="mbox">
    <div class="mhead">
      <div class="mtitle" id="mTitle">—</div>
      <div class="mhead-btns">
        <button class="mbtn-deep" id="btnDeep" onclick="launchDeep()" title="Recherche approfondie ciblée">🔬 APPROFONDIR</button>
        <button class="mbtn-close" onclick="closeModal()">✕</button>
      </div>
    </div>
    <div class="mstats" id="mStats"></div>
    <div class="mdeep-status" id="mDeepStatus"></div>
    <div class="mbody">
      <div class="md" id="mBody"></div>
      <div class="deep-section" id="mDeepSection" style="display:none">
        <div class="deep-label">🔬 Analyse approfondie</div>
        <div class="md" id="mDeepBody"></div>
      </div>
    </div>

    <!-- Sources panel -->
    <div class="msrc-panel">
      <button class="msrc-toggle" onclick="toggleSrc()">
        <span id="srcToggleIcon">▶</span> Sources & liens directs
        <span id="srcCount" style="margin-left:auto;color:var(--txt3)"></span>
      </button>
      <div class="msrc-body" id="mSrcBody"></div>
    </div>
  </div>
</div>

<script>
const API = 'api.php';
const SOURCES = <?= json_encode(array_keys(SOURCES)) ?>;
let busy = false;
let currentArticleId = null;
let deepBusy = false;

// ── API call ──
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
      throw new Error('Non-JSON: ' + txt.slice(0, 200));
    }
    return await r.json();
  } catch(e) {
    tlog('❌ ' + e.message, 'NET', 'err');
    return { success: false, error: e.message };
  }
}

// ── Terminal log ──
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

// ── Progress ──
function prog(pct, label, phase) {
  document.getElementById('pFill').style.width = pct + '%';
  document.getElementById('pPct').textContent  = pct + '%';
  if (label) document.getElementById('pLabel').textContent = label;
  if (phase) document.getElementById('pPhase').textContent = phase;
}

// ── Source cell state ──
function sc(name, state) {
  const el = document.getElementById('sc-' + name);
  if (el) el.className = 'sc ' + state;
}

function resetSources() {
  SOURCES.forEach(s => sc(s, ''));
}

// ── MAIN PIPELINE ──
async function run() {
  if (busy) return;
  busy = true;
  const btn = document.getElementById('btnLaunch');
  btn.disabled = true;
  resetSources();

  const question = document.getElementById('questionInput').value.trim();

  tlog('══════════════════════════════', 'START', 'big');
  prog(2, 'Démarrage…', 'Initialisation');

  // 1. Choisir le sujet
  if (question) {
    prog(4, 'Question reçue', '💬 Analyse de votre question…');
    tlog(`Question: "${question}"`, 'TOPIC', 'info');
  } else {
    prog(4, 'IA réfléchit…', '🤖 Mistral choisit le sujet de recherche');
    tlog('Interrogation Mistral: choix du sujet…', 'TOPIC', 'info');
  }

  const s1 = await api('step_pick_topic', question ? { question } : {});
  if (!s1.success) { tlog('❌ ' + s1.error, 'TOPIC', 'err'); btn.disabled = false; busy = false; return; }

  const { session_id: sid, topic } = s1.data;
  tlog(`✓ Sujet: "${topic}"`, 'TOPIC', 'big');
  prog(8, 'Sujet choisi', `📌 "${topic}"`);

  // 2. Préparer les requêtes — l'IA sait comment questionner chaque source
  tlog('Génération des termes adaptés à chaque source…', 'PREP', 'info');
  prog(10, 'Préparation…', '🔎 Mistral génère 36 requêtes spécialisées');

  const s2 = await api('step_prepare_queries', { session_id: sid, topic });
  if (!s2.success) { tlog('❌ ' + s2.error, 'PREP', 'err'); btn.disabled = false; busy = false; return; }

  const { queries } = s2.data;
  tlog(`✓ ${queries.length} requêtes générées et optimisées`, 'PREP');
  prog(13, `${queries.length} requêtes`, `Interrogation des ${queries.length} sources…`);

  // 3. Exécuter les requêtes
  tlog('══ INTERROGATION DES SOURCES ══', 'FETCH', 'big');
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

    await sleep(200);
  }

  tlog(`══ ${okCount}/${queries.length} sources · ${hitTotal} données collectées ══`, 'DONE', 'big');
  prog(78, 'Collecte finie', `✓ ${okCount} sources · ${hitTotal} résultats · Rédaction…`);

  // 4. Rédiger l'article
  tlog('Envoi à Mistral Large pour synthèse…', 'WRITE', 'info');
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

  // Réinitialiser le champ question
  document.getElementById('questionInput').value = '';

  btn.disabled = false;
  busy = false;
}

// ── Load articles ──
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

// ── Open article ──
async function openArticle(id) {
  currentArticleId = id;
  const r = await api('get_article', { id });
  if (!r.success) return;
  const { article: a, by_source, findings_with_links } = r.data;

  document.getElementById('mTitle').textContent = a.title;
  document.getElementById('mStats').innerHTML = `
    <span>📌 <b>${esc(a.topic)}</b></span>
    <span>📊 <b>${a.word_count}</b> mots</span>
    <span>🔬 <b>${a.sources_ok}</b> sources actives</span>
    <span>📋 <b>${a.total_hits}</b> données collectées</span>
    <span>🕒 ${fmtDate(a.created_at)}</span>
  `;
  document.getElementById('mBody').innerHTML = md(a.content || '');

  // Analyse approfondie
  const deepSection = document.getElementById('mDeepSection');
  if (a.deep_analysis) {
    deepSection.style.display = 'block';
    document.getElementById('mDeepBody').innerHTML = md(a.deep_analysis);
  } else {
    deepSection.style.display = 'none';
  }

  // Sources avec liens
  const srcBody = document.getElementById('mSrcBody');
  const srcCount = document.getElementById('srcCount');

  let srcHtml = '';
  const bySourceMap = {};
  (by_source || []).forEach(s => { bySourceMap[s.source] = s.cnt; });
  (by_source || []).forEach(s => {
    srcHtml += `<span class="msrc-pill">${esc(s.source)} (${s.cnt})</span>`;
  });

  // Liens directs vers les résultats
  if (findings_with_links && findings_with_links.length > 0) {
    srcHtml += '<div style="width:100%;height:4px;margin:.3rem 0"></div>';
    findings_with_links.slice(0, 40).forEach(f => {
      const link = f.source_url || f.url;
      if (link) {
        const shortTitle = f.title.length > 50 ? f.title.slice(0, 50) + '…' : f.title;
        srcHtml += `<a class="msrc-link" href="${esc(link)}" target="_blank" rel="noopener" title="${esc(f.title)}">[${esc(f.source)}] ${esc(shortTitle)}</a>`;
      }
    });
  }

  srcBody.innerHTML = srcHtml || '<span class="msrc-pill">—</span>';
  srcCount.textContent = (findings_with_links || []).filter(f => f.source_url || f.url).length + ' liens';
  srcBody.classList.remove('open');
  document.getElementById('srcToggleIcon').textContent = '▶';

  // Reset deep status
  document.getElementById('mDeepStatus').classList.remove('active');
  document.getElementById('mDeepStatus').textContent = '';
  document.getElementById('btnDeep').disabled = !!a.deep_analysis;
  document.getElementById('btnDeep').textContent = a.deep_analysis ? '✓ Approfondi' : '🔬 APPROFONDIR';

  document.getElementById('modal').classList.add('open');
}

function closeModal() {
  document.getElementById('modal').classList.remove('open');
  currentArticleId = null;
}

function toggleSrc() {
  const b = document.getElementById('mSrcBody');
  const icon = document.getElementById('srcToggleIcon');
  if (b.classList.toggle('open')) {
    icon.textContent = '▼';
  } else {
    icon.textContent = '▶';
  }
}

// ── RECHERCHE APPROFONDIE ──
async function launchDeep() {
  if (!currentArticleId || deepBusy) return;
  deepBusy = true;

  const btn = document.getElementById('btnDeep');
  btn.disabled = true;
  btn.textContent = '⏳ En cours…';

  const statusEl = document.getElementById('mDeepStatus');
  statusEl.classList.add('active');
  statusEl.textContent = '🔬 Planification de la recherche approfondie par Mistral…';

  tlog('══ RECHERCHE APPROFONDIE ══', 'DEEP', 'deep');

  // 1. Lancer le plan
  const s1 = await api('step_deep_research', { article_id: currentArticleId });
  if (!s1.success) {
    toast('❌ ' + s1.error, true);
    statusEl.textContent = '❌ Erreur: ' + s1.error;
    btn.disabled = false;
    btn.textContent = '🔬 APPROFONDIR';
    deepBusy = false;
    return;
  }

  const { session_id: sid, topic, queries, rationale, estimated_queries } = s1.data;
  statusEl.textContent = `📋 Plan: ${estimated_queries} requêtes · ${rationale}`;
  tlog(`Plan: ${queries.length} requêtes ciblées · ${rationale}`, 'DEEP', 'deep');

  // 2. Exécuter les requêtes prioritaires
  let deepHits = 0;
  for (let i = 0; i < queries.length; i++) {
    const q = queries[i];
    statusEl.textContent = `🔎 ${i+1}/${queries.length} · ${q.source}…`;
    sc(q.source, 'run');

    const r = await api('step_exec_query', { query_id: q.id });
    if (r.success && r.data.ok) {
      sc(q.source, 'ok');
      deepHits += r.data.hits || 0;
      tlog(`[DEEP] ✓ ${q.source} · ${r.data.hits} résultats`, 'DEEP', 'deep');
    } else {
      sc(q.source, 'err');
      tlog(`[DEEP] ✗ ${q.source}`, 'DEEP', 'err');
    }
    await sleep(200);
  }

  tlog(`[DEEP] ${deepHits} nouvelles données · Synthèse en cours…`, 'DEEP', 'deep');
  statusEl.textContent = `✍ Synthèse des ${deepHits} nouvelles données par Mistral…`;

  // 3. Finaliser
  const s3 = await api('step_deep_finalize', {
    session_id: sid,
    article_id: currentArticleId,
    topic,
  });

  if (!s3.success) {
    toast('❌ Erreur synthèse approfondie', true);
    statusEl.textContent = '❌ Erreur: ' + s3.error;
    deepBusy = false;
    return;
  }

  // Afficher l'analyse approfondie
  const deepSection = document.getElementById('mDeepSection');
  deepSection.style.display = 'block';
  document.getElementById('mDeepBody').innerHTML = md(s3.data.deep_analysis || '');

  statusEl.textContent = `✅ Analyse approfondie terminée · ${s3.data.new_findings} nouvelles données intégrées`;
  btn.textContent = '✓ Approfondi';
  tlog(`[DEEP] ✓ Analyse approfondie ajoutée · ${s3.data.new_findings} données`, 'DEEP', 'deep');

  await loadArticles();
  toast(`🔬 Recherche approfondie terminée · ${s3.data.new_findings} nouvelles données`);
  deepBusy = false;
}

// ── Markdown renderer ──
function md(s) {
  if (!s) return '';
  // Échapper le HTML sauf les liens qu'on va créer
  s = s.replace(/&(?!amp;|lt;|gt;|quot;)/g, '&amp;')
       .replace(/</g, '&lt;')
       .replace(/>/g, '&gt;');

  return s
    .replace(/^## (.+)$/gm, '<h2>$1</h2>')
    .replace(/^### (.+)$/gm, '<h3>$1</h3>')
    .replace(/^#### (.+)$/gm, '<h4>$1</h4>')
    .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
    .replace(/\*(.+?)\*/g, '<em>$1</em>')
    .replace(/`([^`]+)`/g, '<code>$1</code>')
    .replace(/^&gt; (.+)$/gm, '<blockquote>$1</blockquote>')
    // Liens Markdown [texte](url)
    .replace(/\[([^\]]+)\]\((https?:\/\/[^)]+)\)/g, '<a href="$2" target="_blank" rel="noopener">$1</a>')
    // URLs nues
    .replace(/(?<!["\(])(https?:\/\/[^\s&<>]+)/g, '<a href="$1" target="_blank" rel="noopener">$1</a>')
    // Listes
    .replace(/^[-*] (.+)$/gm, '<li>$1</li>')
    .replace(/^(\d+)\. (.+)$/gm, '<li>$2</li>')
    .replace(/(<li>.*?<\/li>(\n|$))+/gs, m => '<ul>' + m + '</ul>')
    // Tableaux Markdown
    .replace(/^\|(.+)\|$/gm, (_, cells) => {
      const tds = cells.split('|').map(c => `<td>${c.trim()}</td>`).join('');
      return `<tr>${tds}</tr>`;
    })
    .replace(/^<tr>.*<td>[-: ]+<\/td>.*<\/tr>$/gm, '') // supprimer ligne séparateur
    .replace(/(<tr>.*<\/tr>(\n|$))+/gs, m => {
      const rows = m.trim().split('\n');
      if (!rows.length) return m;
      const header = rows[0].replace(/<td>/g, '<th>').replace(/<\/td>/g, '</th>');
      const body = rows.slice(1).join('\n');
      return `<table><thead>${header}</thead><tbody>${body}</tbody></table>`;
    })
    // Paragraphes
    .split(/\n{2,}/).map(b => {
      b = b.trim();
      if (!b || /^<(h[2-4]|ul|ol|blockquote|table)/.test(b)) return b;
      return '<p>' + b.replace(/\n/g, '<br>') + '</p>';
    }).join('\n');
}

// ── Helpers ──
function esc(s) {
  if (!s) return '';
  const d = document.createElement('div');
  d.textContent = String(s);
  return d.innerHTML;
}
function fmtDate(dt) {
  if (!dt) return '—';
  try { return new Date(dt).toLocaleDateString('fr-FR', {day:'2-digit',month:'short',year:'2-digit',hour:'2-digit',minute:'2-digit'}); }
  catch { return dt; }
}
function sleep(ms) { return new Promise(r => setTimeout(r, ms)); }
function toast(msg, err = false, warn = false) {
  const t = document.createElement('div');
  t.className = 'toast' + (err ? ' err' : warn ? ' warn' : '');
  t.textContent = msg;
  document.body.appendChild(t);
  setTimeout(() => t.remove(), 5000);
}

// ── Init ──
document.addEventListener('DOMContentLoaded', loadArticles);
</script>
</body>
</html>
