#!/usr/bin/env python3
"""Genera i prototipi grafici della home in prototipi/ (HTML autonomi, CSS incluso, font accanto).

Usa solo testi reali del sito (ultime notizie da src/content/news). Il logo reale non c'è ancora:
nei prototipi è sostituito da un monogramma provvisorio.

Uso:  python3 tools/genera_prototipi.py
"""
import glob
import html
import json
import os
import re

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
OUT = os.path.join(ROOT, 'prototipi')
MESI = ['gennaio', 'febbraio', 'marzo', 'aprile', 'maggio', 'giugno', 'luglio', 'agosto', 'settembre', 'ottobre', 'novembre', 'dicembre']


def data_it(iso):
    y, m, d = map(int, iso.split('-'))
    return f'{d} {MESI[m - 1]} {y}'


def carica_notizie():
    out = []
    for f in glob.glob(os.path.join(ROOT, 'src/content/news/*.md')):
        t = open(f, encoding='utf-8').read()
        m = re.match(r'---\n(.*?)\n---\n(.*)', t, re.S)
        fm = {}
        for line in m.group(1).split('\n'):
            k, _, v = line.partition(': ')
            try:
                fm[k] = json.loads(v)
            except Exception:
                fm[k] = v
        body = re.sub(r'[#*_>\[\]()`]', ' ', m.group(2))
        body = re.sub(r'\s+', ' ', body).strip()
        # nel sito vecchio molti testi iniziano ripetendo il titolo: nell'estratto si toglie
        norm = lambda x: re.sub(r'[^a-z0-9]+', ' ', x.lower()).strip()
        t = fm.get('title', '')
        if t and norm(body).startswith(norm(t)) and len(norm(t)) > 8:
            n = len(t.split())
            body = ' '.join(body.split(' ')[n:]).strip() or body
        body = re.sub(r'^Oggetto:\s*', '', body)
        w = body.split(' ')
        fm['estratto'] = ' '.join(w[:26]) + ('…' if len(w) > 26 else '')
        out.append(fm)
    out.sort(key=lambda n: (n['date'], n['title']), reverse=True)
    return out


NEWS = carica_notizie()
N_TOT = len(NEWS)
ANNO0 = min(n['date'][:4] for n in NEWS)
HERO = NEWS[0]
ALTRE = NEWS[1:4]
MENU = ['Home', 'Chi siamo', 'Iscrizione', 'Notizie', 'Documenti', 'CCNL', 'Lavoro', 'Contatti']
RAPIDI = [
    ('Iscrizione', 'Quota annuale e come pagarla.'),
    ('CCNL', 'I contratti collettivi, con quelli in vigore in evidenza.'),
    ('Documenti e moduli', 'Statuto, codice deontologico, deleghe, circolari.'),
    ('Contatti', "Scrivici per informazioni e consulenza."),
]
INTRO = 'Dal maggio 2008 Afant-Arezzo raggruppa i farmacisti non titolari della provincia di Arezzo, con un proprio statuto e un Consiglio Direttivo.'
VARIANTI = [
    ('a', 'a-salute.html', 'A · Salute', 'Verde moderno, morbido e rassicurante'),
    ('b', 'b-editoriale.html', 'B · Editoriale', 'Serif, carta e bordeaux: come una rivista di categoria'),
    ('c', 'c-blu-logo.html', 'C · Blu del logo', 'I colori della locandina: blu, ciano e viola'),
    ('d', 'd-notte.html', 'D · Notte', 'Scuro ad alto contrasto, con giallo'),
]

RITOCCHI = [
    ('c1', 'c1-luce.html', 'C1 · Luce', 'Più chiara e calma: bianco, blu come accento'),
    ('c2', 'c2-intensa.html', 'C2 · Intensa', 'Più grande e decisa: riquadro della notizia sovrapposto'),
]


def e(s):
    return html.escape(s, quote=True)


def barra(sigla):
    voci = ''.join(
        f'<a href="{f}"{" aria-current=page" if s == sigla else ""}>{e(n)}</a>' for s, f, n, _ in VARIANTI
    )
    rit = ''.join(
        f'<a href="{f}"{" aria-current=page" if s == sigla else ""}>{e(n)}</a>' for s, f, n, _ in RITOCCHI
    )
    return f'<nav class="pbar" aria-label="Prototipi"><span>Proposte:</span>{voci}<span>Ritocchi sulla C:</span>{rit}<a href="index.html">Indice</a></nav>'


BASE_CSS = """
*{box-sizing:border-box}html{-webkit-text-size-adjust:100%}body{margin:0}img{max-width:100%}
a{text-underline-offset:.18em}
.pbar{display:flex;flex-wrap:wrap;gap:.35rem .9rem;align-items:center;background:#111;color:#fff;font:500 13px/1.3 system-ui,sans-serif;padding:.55rem 1rem}
.pbar a{color:#fff;opacity:.8;text-decoration:none;padding:.1rem .15rem}.pbar a:hover{opacity:1;text-decoration:underline}.pbar a[aria-current=page]{opacity:1;border-bottom:2px solid #fff}
.pbar span{opacity:.65;margin-right:.4rem}
.skip{position:absolute;left:-999px}.skip:focus{left:8px;top:8px;background:#fff;color:#000;padding:.4rem .7rem;z-index:9}
:focus-visible{outline:3px solid #f2a900;outline-offset:2px}
@media (prefers-reduced-motion:reduce){*{animation:none!important;transition:none!important}}
"""


def pagina(sigla, titolo, fonts_css, css, corpo):
    return f"""<!doctype html>
<html lang="it">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Prototipo {e(titolo)} | A.FA.N.T. Arezzo</title>
<meta name="robots" content="noindex">
<style>{fonts_css}{BASE_CSS}{css}</style>
</head>
<body class="v-{sigla}">
<a class="skip" href="#main">Vai al contenuto</a>
{barra(sigla)}
{corpo}
</body>
</html>
"""


def font(nome, file, peso='100 900', stile='normal'):
    return f"@font-face{{font-family:'{nome}';src:url(fonts/{file}) format('woff2');font-weight:{peso};font-style:{stile};font-display:swap}}"


def menu_html(extra=''):
    return ''.join(f'<li><a href="#"{" aria-current=page" if i == 0 else ""}>{e(m)}</a></li>' for i, m in enumerate(MENU))


def footer_html():
    return ('<footer class="foot"><div class="wrap"><div><h2>A.FA.N.T. Arezzo</h2><p>Associazione Farmacisti Non Titolari della provincia di Arezzo</p><p class="s">P.IVA / C.F. IT92058170512</p></div>'
            '<div><h2>Contatti</h2><p>Via Newton 48, 52100 Arezzo (AR)</p><p>info@afantarezzo.it</p><p>Tel. 370 3613108</p></div>'
            '<div><h2>Per gli iscritti</h2><p>Come iscriversi</p><p>Documenti e moduli</p><p>Tutte le notizie</p></div></div></footer>')


# ---------------------------------------------------------------- A · Salute
def var_a():
    fonts = font('Jakarta', 'plus-jakarta-sans-latin-wght-normal.woff2', '200 800')
    css = """
:root{--bg:#f4faf8;--ink:#0f2a2e;--mut:#4a6266;--brand:#0b6b5d;--brand2:#12a58e;--soft:#dff2ec;--line:#cfe5df;--acc:#f2a900}
body{font:400 17px/1.65 'Jakarta',system-ui,sans-serif;color:var(--ink);background:var(--bg)}
.wrap{max-width:1160px;margin:0 auto;padding:0 24px}
.top{background:#fff;border-bottom:1px solid var(--line)}
.top .wrap{display:flex;align-items:center;justify-content:space-between;gap:16px;min-height:76px;flex-wrap:wrap}
.brand{display:flex;align-items:center;gap:12px;text-decoration:none;color:var(--ink)}
.mono{width:46px;height:46px;border-radius:14px;background:linear-gradient(135deg,var(--brand),var(--brand2));color:#fff;display:grid;place-items:center;font-weight:800;font-size:22px}
.brand b{display:block;font-size:20px;letter-spacing:-.01em;line-height:1.1}.brand small{color:var(--mut);font-size:12.5px;font-weight:500}
.nav ul{list-style:none;margin:0;padding:0;display:flex;gap:4px;flex-wrap:wrap}
.nav a{display:block;padding:9px 14px;border-radius:999px;color:var(--ink);text-decoration:none;font-weight:600;font-size:15px}
.nav a:hover{background:var(--soft)}.nav a[aria-current=page]{background:var(--brand);color:#fff}
.hero{padding:40px 0 24px}
.hero .box{display:grid;grid-template-columns:1.25fr 1fr;gap:36px;align-items:center;background:linear-gradient(135deg,#e5f6f0 0%,#d4efe7 55%,#c7e9df 100%);border-radius:32px;padding:56px 52px;position:relative;overflow:hidden}
.hero .box:before{content:"";position:absolute;right:-90px;top:-90px;width:320px;height:320px;border-radius:50%;background:rgba(18,165,142,.16)}
.hero .box:after{content:"";position:absolute;left:38%;bottom:-120px;width:260px;height:260px;border-radius:50%;background:rgba(242,169,0,.18)}
.kick{display:inline-block;font-weight:700;font-size:13px;letter-spacing:.08em;text-transform:uppercase;color:var(--brand);background:#fff;padding:6px 14px;border-radius:999px;margin-bottom:18px}
h1{font-size:clamp(34px,5.2vw,58px);line-height:1.04;letter-spacing:-.03em;font-weight:800;margin:0 0 18px;color:#073f37}
h1 span{color:var(--brand2)}
.lead{font-size:19px;color:#244247;max-width:34em;margin:0 0 28px}
.btns{display:flex;gap:12px;flex-wrap:wrap;position:relative;z-index:1}
.btn{display:inline-block;padding:14px 26px;border-radius:999px;font-weight:700;text-decoration:none;border:2px solid var(--brand);background:var(--brand);color:#fff}
.btn:hover{background:#08524a;border-color:#08524a}.btn.g{background:#fff;color:var(--brand)}.btn.g:hover{background:var(--soft)}
.news-card{background:#fff;border-radius:24px;padding:26px;box-shadow:0 18px 40px -18px rgba(7,63,55,.35);position:relative;z-index:1}
.tag{display:inline-block;font-size:12px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--brand);background:var(--soft);padding:4px 11px;border-radius:999px}
.news-card h2{font-size:23px;line-height:1.25;margin:12px 0 6px;letter-spacing:-.01em}.news-card p{margin:0 0 10px;color:var(--mut)}.news-card a{color:var(--brand);font-weight:700}
.date{font-size:14px;color:var(--mut);font-weight:500}
.sec{padding:44px 0}.sec h2.t{font-size:30px;letter-spacing:-.02em;margin:0 0 24px}
.grid{display:grid;grid-template-columns:repeat(3,1fr);gap:22px}
.card{background:#fff;border:1px solid var(--line);border-radius:22px;padding:24px;transition:transform .18s,box-shadow .18s}
.card:hover{transform:translateY(-3px);box-shadow:0 14px 30px -16px rgba(7,63,55,.35)}
.card h3{font-size:20px;line-height:1.3;margin:12px 0 8px;letter-spacing:-.01em}.card p{margin:0;color:var(--mut);font-size:15.5px}
.quick{display:grid;grid-template-columns:repeat(4,1fr);gap:18px}
.q{background:var(--brand);color:#fff;border-radius:22px;padding:24px;min-height:150px;display:flex;flex-direction:column;justify-content:space-between}
.q:nth-child(2){background:#0e8a77}.q:nth-child(3){background:#12a58e;color:#06332d}.q:nth-child(4){background:#073f37}
.q b{font-size:20px}.q span{font-size:15px;opacity:.95}
.foot{background:#073f37;color:#d7efe9;margin-top:30px}.foot .wrap{display:grid;grid-template-columns:repeat(3,1fr);gap:30px;padding-top:36px;padding-bottom:36px}
.foot h2{font-size:16px;margin:0 0 8px;color:#fff}.foot p{margin:0 0 4px;font-size:15px}.foot .s{opacity:.8;font-size:13.5px}
@media (max-width:900px){.hero .box{grid-template-columns:1fr;padding:34px 24px;border-radius:26px}.grid,.quick,.foot .wrap{grid-template-columns:1fr 1fr}}
@media (max-width:620px){.grid,.quick,.foot .wrap{grid-template-columns:1fr}.top .wrap{padding-top:12px;padding-bottom:12px}.nav a{padding:8px 11px;font-size:14px}}
"""
    carte = ''.join(
        f'<article class="card"><span class="tag">{e(n["categoria"])}</span><h3>{e(n["title"])}</h3><p class="date">{data_it(n["date"])}</p><p>{e(n["estratto"])}</p></article>'
        for n in ALTRE)
    rapidi = ''.join(f'<div class="q"><b>{e(t)}</b><span>{e(d)}</span></div>' for t, d in RAPIDI)
    corpo = f"""
<header class="top"><div class="wrap"><a class="brand" href="#"><span class="mono" aria-hidden="true">A</span><span><b>A.FA.N.T. Arezzo</b><small>Associazione Farmacisti Non Titolari</small></span></a>
<nav class="nav" aria-label="Principale"><ul>{menu_html()}</ul></nav></div></header>
<main id="main">
<section class="hero"><div class="wrap"><div class="box">
<div><span class="kick">Arezzo · dal 2008</span><h1>A.FA.N.T. Arezzo<br><span>Associazione Farmacisti Non Titolari</span></h1><p class="lead">{e(INTRO)}</p>
<div class="btns"><a class="btn" href="#">Iscriviti</a><a class="btn g" href="#">Chi siamo</a></div></div>
<aside class="news-card" aria-label="Ultima comunicazione"><span class="tag">Ultima comunicazione</span><h2>{e(HERO["title"])}</h2><p class="date">{data_it(HERO["date"])}</p><p>{e(HERO["estratto"])}</p><a href="#">Leggi tutto →</a></aside>
</div></div></section>
<section class="sec"><div class="wrap"><h2 class="t">Ultime comunicazioni</h2><div class="grid">{carte}</div></div></section>
<section class="sec" style="padding-top:8px"><div class="wrap"><h2 class="t">Per gli iscritti</h2><div class="quick">{rapidi}</div></div></section>
</main>
{footer_html()}"""
    return pagina('a', 'A · Salute', fonts, css, corpo)


# ------------------------------------------------------------- B · Editoriale
def var_b():
    fonts = font('Fraunces', 'fraunces-latin-wght-normal.woff2', '100 900') + font('Fraunces', 'fraunces-latin-wght-italic.woff2', '100 900', 'italic') + font('Inter', 'inter-latin-wght-normal.woff2', '100 900')
    css = """
:root{--paper:#f7f2e8;--ink:#1c1a16;--mut:#5e574a;--acc:#8f1f2e;--line:#d9d0bd}
body{font:400 17px/1.7 'Inter',system-ui,sans-serif;color:var(--ink);background:var(--paper)}
.wrap{max-width:1140px;margin:0 auto;padding:0 28px}
.mast{text-align:center;padding:26px 0 0}
.mast .e{font-size:12px;letter-spacing:.22em;text-transform:uppercase;color:var(--mut);font-weight:600}
.mast .w{font:600 clamp(40px,7vw,76px)/1 'Fraunces',Georgia,serif;letter-spacing:-.03em;margin:10px 0 18px}
.mast .w i{font-style:italic;font-weight:400;color:var(--acc)}
.nav{border-top:3px double var(--ink);border-bottom:1px solid var(--ink)}
.nav ul{list-style:none;margin:0;padding:0;display:flex;justify-content:center;flex-wrap:wrap;gap:2px 8px}
.nav a{display:block;padding:12px 14px;color:var(--ink);text-decoration:none;font-size:13px;letter-spacing:.12em;text-transform:uppercase;font-weight:600}
.nav a:hover,.nav a[aria-current=page]{color:var(--acc)}
.hero{padding:62px 0 54px;border-bottom:1px solid var(--line)}
.hero .g{display:grid;grid-template-columns:1.35fr 1fr;gap:64px;align-items:start}
h1{font:600 clamp(40px,6.4vw,84px)/1.02 'Fraunces',Georgia,serif;letter-spacing:-.035em;margin:0 0 26px}
h1 em{font-style:italic;font-weight:400;color:var(--acc)}
.lead{font:400 21px/1.55 'Fraunces',Georgia,serif;color:#3a3529;max-width:30em;margin:0 0 30px}
.btn{display:inline-block;background:var(--ink);color:var(--paper);padding:15px 28px;text-decoration:none;font-weight:600;letter-spacing:.06em;text-transform:uppercase;font-size:13px;border:2px solid var(--ink)}
.btn:hover{background:var(--acc);border-color:var(--acc)}.btn.o{background:transparent;color:var(--ink);margin-left:10px}.btn.o:hover{background:var(--ink);color:var(--paper)}
.bul{border-top:4px solid var(--ink);padding-top:16px}
.bul .k{font-size:12px;letter-spacing:.2em;text-transform:uppercase;font-weight:700;color:var(--acc)}
.bul .d{display:flex;align-items:baseline;gap:14px;margin:14px 0 6px}
.bul .n{font:600 84px/0.9 'Fraunces',Georgia,serif;letter-spacing:-.04em}.bul .m{font:400 19px/1.2 'Fraunces',Georgia,serif;font-style:italic;color:var(--mut)}
.bul h2{font:600 27px/1.2 'Fraunces',Georgia,serif;letter-spacing:-.015em;margin:14px 0 10px}.bul p{margin:0 0 12px;color:#3a3529;font-size:16px}
.bul a{color:var(--acc);font-weight:600}
.sec{padding:54px 0 12px}.sec h2.t{font:600 14px/1 'Inter',sans-serif;letter-spacing:.22em;text-transform:uppercase;border-bottom:1px solid var(--ink);padding-bottom:12px;margin:0 0 6px}
.item{display:grid;grid-template-columns:150px 1fr;gap:6px 30px;padding:22px 0;border-bottom:1px solid var(--line)}
.item .dt{font-size:13px;letter-spacing:.08em;text-transform:uppercase;color:var(--mut);font-weight:600;padding-top:7px}
.item h3{font:600 27px/1.2 'Fraunces',Georgia,serif;letter-spacing:-.015em;margin:0 0 6px}.item .c{font-size:12px;letter-spacing:.16em;text-transform:uppercase;color:var(--acc);font-weight:700}
.item p{margin:6px 0 0;color:#3a3529;font-size:16px;max-width:46em}
.cols{display:grid;grid-template-columns:repeat(4,1fr);margin-top:8px}
.col{padding:26px 26px 26px 0;border-right:1px solid var(--line);margin-right:26px}.col:last-child{border-right:0;margin-right:0}
.col .nr{font:400 italic 44px/1 'Fraunces',Georgia,serif;color:var(--acc)}.col b{display:block;font:600 22px/1.2 'Fraunces',Georgia,serif;margin:10px 0 6px}.col span{color:var(--mut);font-size:15.5px}
.foot{background:var(--ink);color:#e9e2d1;margin-top:50px}.foot .wrap{display:grid;grid-template-columns:repeat(3,1fr);gap:30px;padding-top:42px;padding-bottom:42px}
.foot h2{font:600 20px 'Fraunces',serif;margin:0 0 8px;color:#fff}.foot p{margin:0 0 4px;font-size:15px}.foot .s{opacity:.7;font-size:13.5px}
@media (max-width:900px){.hero .g{grid-template-columns:1fr;gap:40px}.cols{grid-template-columns:1fr 1fr}.col:nth-child(2){border-right:0;margin-right:0}.foot .wrap{grid-template-columns:1fr}.item{grid-template-columns:1fr}}
@media (max-width:560px){.cols{grid-template-columns:1fr}.col{border-right:0;margin-right:0;border-bottom:1px solid var(--line)}.btn.o{margin-top:10px}.nav a{padding:10px 9px;font-size:12px}.bul .n{font-size:68px}}
"""
    g = HERO['date'].split('-')
    righe = ''.join(
        f'<article class="item"><div class="dt">{data_it(n["date"])}</div><div><span class="c">{e(n["categoria"])}</span><h3>{e(n["title"])}</h3><p>{e(n["estratto"])}</p></div></article>'
        for n in ALTRE)
    cols = ''.join(f'<div class="col"><span class="nr">0{i + 1}</span><b>{e(t)}</b><span>{e(d)}</span></div>' for i, (t, d) in enumerate(RAPIDI))
    corpo = f"""
<header class="mast"><div class="wrap"><div class="e">Associazione Farmacisti Non Titolari · Arezzo</div><div class="w">A.FA.N.T. <i>Arezzo</i></div></div>
<nav class="nav" aria-label="Principale"><ul>{menu_html()}</ul></nav></header>
<main id="main">
<section class="hero"><div class="wrap g">
<div><h1>Associazione Farmacisti <em>Non Titolari</em></h1><p class="lead">{e(INTRO)}</p><a class="btn" href="#">Iscriviti</a><a class="btn o" href="#">Chi siamo</a></div>
<aside class="bul" aria-label="Ultima comunicazione"><div class="k">Ultima comunicazione</div><div class="d"><span class="n">{int(g[2])}</span><span class="m">{MESI[int(g[1]) - 1]}<br>{g[0]}</span></div><h2>{e(HERO["title"])}</h2><p>{e(HERO["estratto"])}</p><a href="#">Leggi tutto →</a></aside>
</div></section>
<section class="sec"><div class="wrap"><h2 class="t">Ultime comunicazioni</h2>{righe}</div></section>
<section class="sec"><div class="wrap"><h2 class="t">Per gli iscritti</h2><div class="cols">{cols}</div></div></section>
</main>
{footer_html()}"""
    return pagina('b', 'B · Editoriale', fonts, css, corpo)


# ------------------------------------------------------------- C · Blu del logo
CSS_C = """
:root{--blue:#0739c2;--navy:#02088f;--violet:#a257dc;--cyan:#70d0f7;--sky:#aee1f8;--bg:#f2f6ff;--ink:#0b1440;--mut:#4a5588}
body{font:400 17px/1.65 'Inter',system-ui,sans-serif;color:var(--ink);background:var(--bg)}
.wrap{max-width:1180px;margin:0 auto;padding:0 24px}
.hero{background:linear-gradient(120deg,#02088f 0%,#0739c2 45%,#6a3fd4 100%);color:#fff;position:relative;overflow:hidden}
.hero:before{content:"";position:absolute;width:520px;height:520px;right:-120px;top:-160px;border-radius:50%;background:radial-gradient(circle,rgba(112,208,247,.55),rgba(112,208,247,0) 68%)}
.hero:after{content:"";position:absolute;left:-90px;bottom:-130px;width:420px;height:420px;border-radius:50%;background:radial-gradient(circle,rgba(220,116,253,.5),rgba(220,116,253,0) 66%)}
.rib{position:absolute;left:46%;top:-20%;width:150px;height:150%;background:linear-gradient(180deg,rgba(174,225,248,.35),rgba(174,225,248,0));transform:skewX(-24deg)}
.head{position:relative;z-index:2;display:flex;align-items:center;justify-content:space-between;gap:16px;padding:22px 0;flex-wrap:wrap}
.brand{display:flex;align-items:center;gap:12px;text-decoration:none;color:#fff}
.mono{width:46px;height:46px;border-radius:50%;background:#fff;color:var(--blue);display:grid;place-items:center;font:700 22px 'Outfit',sans-serif}
.brand b{display:block;font:700 22px/1.05 'Outfit',sans-serif;letter-spacing:.01em}.brand small{font-size:12.5px;opacity:.85}
.nav ul{list-style:none;margin:0;padding:6px;display:flex;gap:2px;flex-wrap:wrap;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.22);border-radius:999px;backdrop-filter:blur(8px)}
.nav a{display:block;padding:8px 15px;border-radius:999px;color:#fff;text-decoration:none;font-weight:500;font-size:15px}
.nav a:hover{background:rgba(255,255,255,.18)}.nav a[aria-current=page]{background:#fff;color:var(--blue);font-weight:600}
.hg{position:relative;z-index:2;display:grid;grid-template-columns:1.2fr 1fr;gap:48px;align-items:center;padding:50px 0 84px}
h1{font:700 clamp(38px,5.6vw,68px)/1.02 'Outfit',sans-serif;letter-spacing:-.025em;margin:0 0 20px}
h1 span{background:linear-gradient(90deg,#aee1f8,#dc74fd);-webkit-background-clip:text;background-clip:text;color:transparent}
.lead{font-size:19px;color:#e5ecff;max-width:33em;margin:0 0 28px}
.btn{display:inline-block;padding:14px 28px;border-radius:999px;font:600 16px 'Outfit',sans-serif;text-decoration:none;background:#fff;color:var(--blue)}
.btn:hover{background:var(--sky)}.btn.g{background:transparent;color:#fff;border:2px solid rgba(255,255,255,.7);margin-left:8px}.btn.g:hover{background:rgba(255,255,255,.14)}
.chips{display:flex;gap:10px;flex-wrap:wrap;margin-top:30px}.chips span{font-size:14px;background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.25);padding:7px 14px;border-radius:999px}
.glass{background:rgba(255,255,255,.96);color:var(--ink);border-radius:28px;padding:28px;box-shadow:0 30px 60px -25px rgba(2,8,143,.6)}
.tag{display:inline-block;font:600 12px 'Outfit',sans-serif;letter-spacing:.08em;text-transform:uppercase;color:#fff;background:linear-gradient(90deg,var(--blue),var(--violet));padding:5px 12px;border-radius:999px}
.glass h2{font:600 25px/1.2 'Outfit',sans-serif;margin:14px 0 6px;letter-spacing:-.01em}.glass p{margin:0 0 10px;color:var(--mut)}.glass a{color:var(--blue);font-weight:600}
.date{font-size:14px;color:var(--mut)}
.sec{padding:60px 0 8px}.sec h2.t{font:700 32px 'Outfit',sans-serif;letter-spacing:-.02em;margin:0 0 26px}
.grid{display:grid;grid-template-columns:repeat(3,1fr);gap:22px}
.card{background:#fff;border-radius:24px;padding:0 0 24px;overflow:hidden;box-shadow:0 10px 30px -18px rgba(7,57,194,.45);transition:transform .2s}.card:hover{transform:translateY(-4px)}
.card .bar{height:8px;background:linear-gradient(90deg,var(--cyan),var(--blue),var(--violet))}
.card .in{padding:22px 24px 0}.card h3{font:600 21px/1.28 'Outfit',sans-serif;margin:12px 0 8px}.card p{margin:0;color:var(--mut);font-size:15.5px}
.tags{display:inline-block;font:600 12px 'Outfit',sans-serif;letter-spacing:.06em;text-transform:uppercase;color:var(--blue);background:#e4ebff;padding:4px 11px;border-radius:999px}
.quick{display:grid;grid-template-columns:repeat(4,1fr);gap:18px}
.q{background:#fff;border-radius:24px;padding:24px;box-shadow:0 10px 30px -18px rgba(7,57,194,.45)}
.q i{display:block;width:46px;height:46px;border-radius:14px;margin-bottom:16px;background:linear-gradient(135deg,var(--cyan),var(--blue))}
.q:nth-child(2) i{background:linear-gradient(135deg,var(--blue),var(--violet))}.q:nth-child(3) i{background:linear-gradient(135deg,var(--violet),#dc74fd)}.q:nth-child(4) i{background:linear-gradient(135deg,#dc74fd,var(--cyan))}
.q b{display:block;font:600 20px 'Outfit',sans-serif;margin-bottom:4px}.q span{color:var(--mut);font-size:15.5px}
.foot{background:var(--navy);color:#d9e1ff;margin-top:64px}.foot .wrap{display:grid;grid-template-columns:repeat(3,1fr);gap:30px;padding-top:40px;padding-bottom:40px}
.foot h2{font:600 18px 'Outfit',sans-serif;margin:0 0 8px;color:#fff}.foot p{margin:0 0 4px;font-size:15px}.foot .s{opacity:.8;font-size:13.5px}
@media (max-width:900px){.hg{grid-template-columns:1fr;padding:30px 0 56px}.grid,.quick{grid-template-columns:1fr 1fr}.foot .wrap{grid-template-columns:1fr}.rib{display:none}}
@media (max-width:620px){.grid,.quick{grid-template-columns:1fr}.btn.g{margin:10px 0 0}.nav a{padding:7px 11px;font-size:14px}.nav ul{border-radius:22px}}
"""


def var_c():
    fonts = font('Outfit', 'outfit-latin-wght-normal.woff2') + font('Inter', 'inter-latin-wght-normal.woff2')
    css = CSS_C
    carte = ''.join(
        f'<article class="card"><div class="bar"></div><div class="in"><span class="tags">{e(n["categoria"])}</span><h3>{e(n["title"])}</h3><p class="date">{data_it(n["date"])}</p><p>{e(n["estratto"])}</p></div></article>'
        for n in ALTRE)
    rapidi = ''.join(f'<div class="q"><i></i><b>{e(t)}</b><span>{e(d)}</span></div>' for t, d in RAPIDI)
    corpo = f"""
<main id="main">
<section class="hero"><div class="rib" aria-hidden="true"></div><div class="wrap">
<div class="head"><a class="brand" href="#"><span class="mono" aria-hidden="true">A</span><span><b>A.FA.N.T. Arezzo</b><small>Associazione Farmacisti Non Titolari</small></span></a><nav class="nav" aria-label="Principale"><ul>{menu_html()}</ul></nav></div>
<div class="hg"><div><h1>A.FA.N.T. Arezzo<br><span>Farmacisti Non Titolari</span></h1><p class="lead">{e(INTRO)}</p><a class="btn" href="#">Iscriviti</a><a class="btn g" href="#">Chi siamo</a>
<div class="chips"><span>Dal 2008</span><span>Quota 15 € l'anno</span><span>{N_TOT} comunicazioni dal {ANNO0}</span></div></div>
<aside class="glass" aria-label="Ultima comunicazione"><span class="tag">Ultima comunicazione</span><h2>{e(HERO["title"])}</h2><p class="date">{data_it(HERO["date"])}</p><p>{e(HERO["estratto"])}</p><a href="#">Leggi tutto →</a></aside></div>
</div></section>
<section class="sec"><div class="wrap"><h2 class="t">Ultime comunicazioni</h2><div class="grid">{carte}</div></div></section>
<section class="sec"><div class="wrap"><h2 class="t">Per gli iscritti</h2><div class="quick">{rapidi}</div></div></section>
</main>
{footer_html()}"""
    return pagina('c', 'C · Blu del logo', fonts, css, corpo)


# ------------------------------------------------------------------ C1 · Luce
def var_c1():
    fonts = font('Outfit', 'outfit-latin-wght-normal.woff2') + font('Inter', 'inter-latin-wght-normal.woff2')
    css = CSS_C + """
.hero{background:linear-gradient(135deg,#eaf0ff 0%,#f4efff 58%,#ffffff 100%);color:var(--ink)}
.hero:before{background:radial-gradient(circle,rgba(112,208,247,.5),rgba(112,208,247,0) 68%)}
.hero:after{background:radial-gradient(circle,rgba(220,116,253,.28),rgba(220,116,253,0) 66%)}
.rib{background:linear-gradient(180deg,rgba(7,57,194,.09),rgba(7,57,194,0))}
.brand{color:var(--ink)}.mono{background:var(--blue);color:#fff}.brand small{opacity:1;color:var(--mut)}
.nav ul{background:#fff;border:1px solid var(--line);box-shadow:0 10px 26px -18px rgba(7,57,194,.5);backdrop-filter:none}
.nav a{color:var(--ink)}.nav a:hover{background:var(--tint)}.nav a[aria-current=page]{background:var(--blue);color:#fff}
h1{color:var(--navy)}h1 span{background:linear-gradient(90deg,#0739c2,#a257dc);-webkit-background-clip:text;background-clip:text;color:transparent}
.lead{color:var(--mut)}
.btn{background:var(--blue);color:#fff}.btn:hover{background:var(--navy)}.btn.g{background:#fff;color:var(--blue);border:2px solid var(--blue)}.btn.g:hover{background:var(--tint)}
.chips span{background:#fff;border:1px solid var(--line);color:var(--ink)}
.glass{background:#fff;border:1px solid var(--line);box-shadow:0 34px 60px -34px rgba(7,57,194,.4)}
.hg{padding:40px 0 92px}
.sec{padding-top:72px}
"""
    base = var_c()
    # riusa il corpo di C: stessa struttura, stile diverso
    corpo = re.search(r'<main id="main">.*?</footer>', base, re.S).group(0)
    return pagina('c1', 'C1 · Luce', fonts, css, corpo)


# ---------------------------------------------------------------- C2 · Intensa
def var_c2():
    fonts = font('Outfit', 'outfit-latin-wght-normal.woff2') + font('Inter', 'inter-latin-wght-normal.woff2')
    css = CSS_C + """
.hero:before{width:640px;height:640px;right:-160px;top:-200px}
.hero:after{width:560px;height:560px}
.hc{position:relative;z-index:2;text-align:center;max-width:900px;margin:0 auto;padding:46px 0 170px}
.eyebrow{display:inline-block;font:600 13px 'Outfit',sans-serif;letter-spacing:.2em;text-transform:uppercase;background:rgba(255,255,255,.16);border:1px solid rgba(255,255,255,.3);padding:7px 16px;border-radius:999px;margin-bottom:24px}
.hc h1{font-size:clamp(46px,7.4vw,92px);line-height:.98;letter-spacing:-.035em;margin:0 0 24px;text-wrap:balance}
.hc .lead{margin:0 auto 32px;font-size:20px}
.hc .btns{display:flex;gap:12px;justify-content:center;flex-wrap:wrap}
.hc .btn.g{margin-left:0}
.float{position:relative;z-index:3;margin-top:-104px}
.fnews{display:grid;grid-template-columns:190px 1fr auto;gap:30px;align-items:center;background:#fff;border-radius:30px;padding:30px 38px;box-shadow:0 44px 80px -34px rgba(2,8,143,.6)}
.fnews .dn{font:700 78px/.95 'Outfit',sans-serif;color:var(--blue);letter-spacing:-.04em}.fnews .dm{font:500 17px 'Outfit',sans-serif;color:var(--mut)}
.fnews h2{font:600 27px/1.2 'Outfit',sans-serif;margin:8px 0 6px;letter-spacing:-.015em}.fnews p{margin:0;color:var(--mut)}
.fnews .go{display:inline-block;padding:14px 26px;border-radius:999px;background:var(--blue);color:#fff;text-decoration:none;font:600 16px 'Outfit',sans-serif;white-space:nowrap}.fnews .go:hover{background:var(--navy)}
.stats3{display:grid;grid-template-columns:repeat(3,1fr);gap:18px;margin-top:22px}
.stats3 div{background:#fff;border-radius:22px;padding:22px 26px;box-shadow:0 10px 30px -18px rgba(7,57,194,.45)}
.stats3 b{display:block;font:700 44px/1 'Outfit',sans-serif;letter-spacing:-.03em;background:linear-gradient(90deg,var(--blue),var(--violet));-webkit-background-clip:text;background-clip:text;color:transparent}.stats3 span{color:var(--mut);font-size:15.5px}
.sec{padding-top:70px}
@media (max-width:900px){.hc{padding:26px 0 130px}.fnews{grid-template-columns:1fr;gap:12px;padding:26px 24px}.stats3{grid-template-columns:1fr}.float{margin-top:-84px}}
"""
    g = HERO['date'].split('-')
    carte = ''.join(
        f'<article class="card"><div class="bar"></div><div class="in"><span class="tags">{e(n["categoria"])}</span><h3>{e(n["title"])}</h3><p class="date">{data_it(n["date"])}</p><p>{e(n["estratto"])}</p></div></article>'
        for n in ALTRE)
    rapidi = ''.join(f'<div class="q"><i></i><b>{e(t)}</b><span>{e(d)}</span></div>' for t, d in RAPIDI)
    corpo = f"""
<main id="main">
<section class="hero"><div class="rib" aria-hidden="true"></div><div class="wrap">
<div class="head"><a class="brand" href="#"><span class="mono" aria-hidden="true">A</span><span><b>A.FA.N.T. Arezzo</b><small>Associazione Farmacisti Non Titolari</small></span></a><nav class="nav" aria-label="Principale"><ul>{menu_html()}</ul></nav></div>
<div class="hc"><span class="eyebrow">Arezzo · dal 2008</span><h1>A.FA.N.T. Arezzo<br><span>Farmacisti Non Titolari</span></h1><p class="lead">{e(INTRO)}</p><div class="btns"><a class="btn" href="#">Iscriviti</a><a class="btn g" href="#">Chi siamo</a></div></div>
</div></section>
<div class="wrap float"><aside class="fnews" aria-label="Ultima comunicazione"><div><div class="dn">{int(g[2])}</div><div class="dm">{MESI[int(g[1]) - 1]} {g[0]}</div></div><div><span class="tag">Ultima comunicazione</span><h2>{e(HERO["title"])}</h2><p>{e(HERO["estratto"])}</p></div><a class="go" href="#">Leggi tutto →</a></aside>
<div class="stats3"><div><b>2008</b><span>anno di fondazione</span></div><div><b>15 €</b><span>quota annuale di iscrizione</span></div><div><b>{N_TOT}</b><span>comunicazioni dal {ANNO0}</span></div></div></div>
<section class="sec"><div class="wrap"><h2 class="t">Ultime comunicazioni</h2><div class="grid">{carte}</div></div></section>
<section class="sec"><div class="wrap"><h2 class="t">Per gli iscritti</h2><div class="quick">{rapidi}</div></div></section>
</main>
{footer_html()}"""
    return pagina('c2', 'C2 · Intensa', fonts, css, corpo)


# ------------------------------------------------------------------ D · Notte
def var_d():
    fonts = font('Grotesk', 'space-grotesk-latin-wght-normal.woff2', '300 700') + font('DM', 'dm-sans-latin-wght-normal.woff2', '100 1000')
    css = """
:root{--bg:#0a0f1d;--s1:#111a2e;--s2:#17223b;--ink:#eaf0ff;--mut:#a3b0cf;--line:rgba(255,255,255,.12);--acc:#ffc857}
body{font:400 17px/1.65 'DM',system-ui,sans-serif;color:var(--ink);background:radial-gradient(820px 560px at 82% 18%,rgba(255,200,87,.16),rgba(255,200,87,0) 70%),var(--bg);background-repeat:no-repeat}
.wrap{max-width:1180px;margin:0 auto;padding:0 24px}
.head{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:20px 0;flex-wrap:wrap;border-bottom:1px solid var(--line)}
.brand{display:flex;align-items:center;gap:12px;text-decoration:none;color:var(--ink)}
.mono{width:42px;height:42px;border-radius:10px;border:2px solid var(--acc);color:var(--acc);display:grid;place-items:center;font:700 20px 'Grotesk',sans-serif}
.brand b{display:block;font:700 20px/1.1 'Grotesk',sans-serif;letter-spacing:-.01em}.brand small{color:var(--mut);font-size:12.5px}
.nav ul{list-style:none;margin:0;padding:0;display:flex;gap:2px;flex-wrap:wrap}
.nav a{display:block;padding:9px 13px;color:var(--mut);text-decoration:none;font-weight:500;font-size:15px;border-radius:8px}
.nav a:hover{color:#fff;background:var(--s1)}.nav a[aria-current=page]{color:var(--bg);background:var(--acc);font-weight:700}
.hero{padding:76px 0 70px;position:relative;overflow:hidden}
.hero .g{position:relative;display:grid;grid-template-columns:1.4fr 1fr;gap:56px;align-items:end}
.kick{font:600 13px 'Grotesk',sans-serif;letter-spacing:.2em;text-transform:uppercase;color:var(--acc);margin-bottom:20px}
h1{font:700 clamp(42px,7vw,96px)/.98 'Grotesk',sans-serif;letter-spacing:-.045em;margin:0 0 26px}
h1 span{color:var(--acc)}
.lead{font-size:19px;color:#c9d4f1;max-width:32em;margin:0 0 32px}
.btn{display:inline-block;padding:15px 28px;border-radius:10px;font:700 16px 'Grotesk',sans-serif;text-decoration:none;background:var(--acc);color:#1b1500}
.btn:hover{background:#ffd98a}.btn.gh{background:transparent;color:#fff;border:1px solid rgba(255,255,255,.35);margin-left:10px}.btn.gh:hover{background:var(--s1)}
.ev{background:var(--s1);border:1px solid var(--line);border-radius:20px;padding:28px}
.ev .k{font:600 12px 'Grotesk',sans-serif;letter-spacing:.2em;text-transform:uppercase;color:var(--acc)}
.ev .d{font:700 76px/1 'Grotesk',sans-serif;letter-spacing:-.05em;margin:12px 0 0}.ev .m{color:var(--mut);margin:0 0 14px;font-weight:500}
.ev h2{font:600 23px/1.25 'Grotesk',sans-serif;margin:0 0 10px;letter-spacing:-.01em}.ev p{margin:0 0 10px;color:var(--mut);font-size:16px}.ev a{color:var(--acc);font-weight:700;text-decoration:none}
.stats{display:grid;grid-template-columns:repeat(3,1fr);border-top:1px solid var(--line);border-bottom:1px solid var(--line)}
.stats div{padding:26px 24px;border-right:1px solid var(--line)}.stats div:last-child{border-right:0}
.stats b{display:block;font:700 40px/1 'Grotesk',sans-serif;letter-spacing:-.03em;color:#fff}.stats span{color:var(--mut);font-size:15px}
.sec{padding:66px 0 6px}.sec h2.t{font:700 34px 'Grotesk',sans-serif;letter-spacing:-.03em;margin:0 0 26px}
.strip{display:grid;grid-template-columns:130px 1fr 40px;gap:24px;align-items:center;padding:24px 22px;border:1px solid var(--line);border-radius:16px;background:var(--s1);margin-bottom:14px;transition:background .2s,border-color .2s}
.strip:hover{background:var(--s2);border-color:rgba(255,200,87,.5)}
.strip .dt{font:700 22px/1.1 'Grotesk',sans-serif;color:var(--acc)}.strip .dt small{display:block;color:var(--mut);font:500 13px 'DM',sans-serif;margin-top:4px}
.strip .c{font:600 12px 'Grotesk',sans-serif;letter-spacing:.16em;text-transform:uppercase;color:var(--mut)}
.strip h3{font:600 22px/1.25 'Grotesk',sans-serif;margin:4px 0 6px;letter-spacing:-.01em}.strip p{margin:0;color:var(--mut);font-size:15.5px}.strip .ar{font-size:26px;color:var(--acc)}
.quick{display:grid;grid-template-columns:repeat(4,1fr);gap:16px}
.q{background:var(--s1);border:1px solid var(--line);border-radius:16px;padding:24px;min-height:178px;display:flex;flex-direction:column;justify-content:space-between}
.q .n{font:700 15px 'Grotesk',sans-serif;color:var(--acc);letter-spacing:.1em}.q b{display:block;font:600 21px/1.2 'Grotesk',sans-serif;margin-bottom:4px}.q span{color:var(--mut);font-size:15.5px}
.foot{border-top:1px solid var(--line);margin-top:70px;background:#070b15}.foot .wrap{display:grid;grid-template-columns:repeat(3,1fr);gap:30px;padding-top:40px;padding-bottom:40px}
.foot h2{font:700 17px 'Grotesk',sans-serif;margin:0 0 8px}.foot p{margin:0 0 4px;font-size:15px;color:var(--mut)}.foot .s{font-size:13.5px}
@media (max-width:900px){.hero{padding:44px 0 46px}.hero .g{grid-template-columns:1fr;gap:36px}.quick{grid-template-columns:1fr 1fr}.foot .wrap{grid-template-columns:1fr}.strip{grid-template-columns:1fr}.strip .ar{display:none}}
@media (max-width:620px){.quick,.stats{grid-template-columns:1fr}.stats div{border-right:0;border-bottom:1px solid var(--line)}.btn.gh{margin:12px 0 0}.nav a{padding:8px 10px;font-size:14px}}
"""
    g = HERO['date'].split('-')
    strisce = ''.join(
        f'<article class="strip"><div class="dt">{int(n["date"].split("-")[2])} {MESI[int(n["date"].split("-")[1]) - 1][:3]}<small>{n["date"][:4]}</small></div><div><span class="c">{e(n["categoria"])}</span><h3>{e(n["title"])}</h3><p>{e(n["estratto"])}</p></div><span class="ar" aria-hidden="true">→</span></article>'
        for n in ALTRE)
    rapidi = ''.join(f'<div class="q"><span class="n">0{i + 1}</span><div><b>{e(t)}</b><span>{e(d)}</span></div></div>' for i, (t, d) in enumerate(RAPIDI))
    corpo = f"""
<div class="wrap"><header class="head"><a class="brand" href="#"><span class="mono" aria-hidden="true">A</span><span><b>A.FA.N.T. Arezzo</b><small>Associazione Farmacisti Non Titolari</small></span></a>
<nav class="nav" aria-label="Principale"><ul>{menu_html()}</ul></nav></header></div>
<main id="main">
<section class="hero"><div class="wrap g">
<div><div class="kick">Arezzo · dal 2008</div><h1>Farmacisti<br><span>non titolari</span>,<br>insieme.</h1><p class="lead">{e(INTRO)}</p><a class="btn" href="#">Iscriviti</a><a class="btn gh" href="#">Chi siamo</a></div>
<aside class="ev" aria-label="Ultima comunicazione"><div class="k">Ultima comunicazione</div><div class="d">{int(g[2])}</div><p class="m">{MESI[int(g[1]) - 1]} {g[0]}</p><h2>{e(HERO["title"])}</h2><p>{e(HERO["estratto"])}</p><a href="#">Leggi tutto →</a></aside>
</div></section>
<div class="wrap"><div class="stats"><div><b>2008</b><span>anno di fondazione</span></div><div><b>15 €</b><span>quota annuale di iscrizione</span></div><div><b>{N_TOT}</b><span>comunicazioni dal {ANNO0}</span></div></div></div>
<section class="sec"><div class="wrap"><h2 class="t">Ultime comunicazioni</h2>{strisce}</div></section>
<section class="sec"><div class="wrap"><h2 class="t">Per gli iscritti</h2><div class="quick">{rapidi}</div></div></section>
</main>
{footer_html()}"""
    return pagina('d', 'D · Notte', fonts, css, corpo)


def indice():
    schede = ''
    for s, f, n, d in VARIANTI + RITOCCHI:
        img = f'img/{s}-desktop.png'
        schede += (f'<a class="c" href="{f}"><span class="im">'
                   + (f'<img src="{img}" alt="Anteprima del prototipo {e(n)}" loading="lazy">' if os.path.exists(os.path.join(OUT, img)) else '')
                   + f'</span><b>{e(n)}</b><span>{e(d)}</span></a>')
    return f"""<!doctype html><html lang="it"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Prototipi grafici | A.FA.N.T. Arezzo</title><meta name="robots" content="noindex">
<style>body{{font:17px/1.6 system-ui,sans-serif;margin:0;background:#f5f5f4;color:#1c1917}}.w{{max-width:1100px;margin:0 auto;padding:32px 22px}}h1{{margin:0 0 6px}}p{{color:#57534e;margin:0 0 26px}}
.g{{display:grid;grid-template-columns:repeat(2,1fr);gap:22px}}.c{{background:#fff;border:1px solid #e7e5e4;border-radius:16px;padding:14px 14px 18px;text-decoration:none;color:inherit;display:block}}.c:hover{{border-color:#1c1917}}
.im{{display:block;aspect-ratio:16/10;background:#e7e5e4;border-radius:10px;overflow:hidden;margin-bottom:12px}}.im img{{width:100%;height:100%;object-fit:cover;object-position:top;display:block}}.c b{{display:block;font-size:19px}}.c span+span,.c b+span{{color:#57534e;font-size:15px}}
@media (max-width:700px){{.g{{grid-template-columns:1fr}}}}</style></head><body><div class="w"><h1>Prototipi grafici della home</h1>
<p>Quattro proposte (A-D) e due ritocchi sulla C scelta (C1, C2), con gli stessi testi reali del sito. Il logo è provvisorio (monogramma): va sostituito con quello dell'associazione. Clicca per aprire a tutta pagina.</p>
<div class="g">{schede}</div></div></body></html>"""


def main():
    os.makedirs(OUT, exist_ok=True)
    for (s, f, n, d), fn in zip(VARIANTI + RITOCCHI, (var_a, var_b, var_c, var_d, var_c1, var_c2)):
        open(os.path.join(OUT, f), 'w', encoding='utf-8').write(fn())
    open(os.path.join(OUT, 'index.html'), 'w', encoding='utf-8').write(indice())
    print('generati:', ', '.join(f for _, f, _, _ in VARIANTI + RITOCCHI), '+ index.html')


if __name__ == '__main__':
    main()
