#!/usr/bin/env python3
"""Crea una copia sfogliabile del sito (si apre con doppio clic su index.html, senza server).

Parte dalla build di Astro (dist/) e riscrive i collegamenti da assoluti (/notizie/x/) a relativi
(../notizie/x/index.html). Le pagine di semplice reindirizzamento (indirizzi vecchi) vengono omesse.

Uso:
    PUBLIC_PREVIEW=1 npm run build      # build con le note per il cliente
    python3 tools/offline_site.py       # crea anteprima/ e anteprima.zip
"""
import os
import re
import shutil
import sys
import zipfile

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
SRC = os.path.join(ROOT, 'dist')
OUT = os.path.join(ROOT, 'anteprima')
ATTR = re.compile(r'''(href|src)="(/[^"/][^"]*|/)"''')


def a_relativo(url, prefisso):
    """Converte un indirizzo assoluto del sito in uno relativo alla pagina corrente."""
    path, sep, frag = url.partition('#')
    path, _, query = path.partition('?')
    t = path.lstrip('/')
    if t == '' or t.endswith('/'):
        t += 'index.html'
    elif '.' not in os.path.basename(t):
        t += '/index.html'
    return prefisso + t + (sep + frag if frag else '')


def main():
    if not os.path.isdir(SRC):
        sys.exit('Manca dist/: esegui prima la build (PUBLIC_PREVIEW=1 npm run build).')
    shutil.rmtree(OUT, ignore_errors=True)
    n_html = n_redirect = n_altri = 0
    for base, _, files in os.walk(SRC):
        for f in files:
            src = os.path.join(base, f)
            rel = os.path.relpath(src, SRC)
            dst = os.path.join(OUT, rel)
            os.makedirs(os.path.dirname(dst), exist_ok=True)
            if f.endswith('.html'):
                t = open(src, encoding='utf-8').read()
                if 'http-equiv="refresh"' in t[:600]:
                    n_redirect += 1
                    continue
                prefisso = '../' * rel.count(os.sep) or './'
                # il caricamento anticipato dei font serve solo da server: da file darebbe errori inutili
                t = re.sub(r'<link rel="preload"[^>]*as="font"[^>]*>\s*', '', t)
                t = ATTR.sub(lambda m: f'{m.group(1)}="{a_relativo(m.group(2), prefisso)}"', t)
                open(dst, 'w', encoding='utf-8').write(t)
                n_html += 1
            elif f.endswith('.css'):
                t = open(src, encoding='utf-8').read()
                prefisso = '../' * rel.count(os.sep) or './'
                t = re.sub(r'url\((["\']?)/([^)"\']+)\1\)', lambda m: f'url({m.group(1)}{prefisso}{m.group(2)}{m.group(1)})', t)
                open(dst, 'w', encoding='utf-8').write(t)
                n_altri += 1
            else:
                shutil.copyfile(src, dst)
                n_altri += 1
    # controllo: ogni href/src relativo deve esistere
    rotti = []
    for base, _, files in os.walk(OUT):
        for f in files:
            if not f.endswith('.html'):
                continue
            p = os.path.join(base, f)
            for m in re.finditer(r'(?:href|src)="([^":#?]+)(?:[#?][^"]*)?"', open(p, encoding='utf-8').read()):
                u = m.group(1)
                if u.startswith(('http', 'mailto', 'tel', 'data')) or not u:
                    continue
                if not os.path.exists(os.path.normpath(os.path.join(base, u))):
                    rotti.append((os.path.relpath(p, OUT), u))
    zip_path = OUT + '.zip'
    with zipfile.ZipFile(zip_path, 'w', zipfile.ZIP_DEFLATED) as z:
        for base, _, files in os.walk(OUT):
            for f in files:
                p = os.path.join(base, f)
                z.write(p, os.path.join('anteprima', os.path.relpath(p, OUT)))
    print(f'pagine: {n_html} | reindirizzamenti omessi: {n_redirect} | altri file: {n_altri}')
    print(f'collegamenti rotti: {len(rotti)}')
    for r in rotti[:15]:
        print('  ', r)
    print(f'creato: {OUT}/index.html e {zip_path} ({os.path.getsize(zip_path) / 1048576:.1f} MB)')


if __name__ == '__main__':
    main()
