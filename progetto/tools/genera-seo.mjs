import { readdir, readFile, writeFile, mkdir } from 'node:fs/promises';
import path from 'node:path';

const out = path.resolve(process.argv[2] || 'dist');
const origin = (process.env.PUBLIC_SITE_ORIGIN || 'https://www.afantarezzo.it').replace(/\/$/, '');
const production = origin === 'https://www.afantarezzo.it';
const oldNews = JSON.parse(await readFile('src/data/redirects.json', 'utf8'));
const oldPages = JSON.parse(await readFile('src/data/redirects-pagine.json', 'utf8'));
const redirects = { ...oldNews, ...oldPages };

async function filesIn(dir) {
  const entries = await readdir(dir, { withFileTypes: true });
  return (await Promise.all(entries.map(e => e.isDirectory() ? filesIn(path.join(dir, e.name)) : [path.join(dir, e.name)]))).flat();
}
const files = await filesIn(out);
const urls = [];
for (const file of files.filter(f => path.basename(f) === 'index.html')) {
  const route = '/' + path.relative(out, path.dirname(file)).split(path.sep).filter(Boolean).join('/') + (path.dirname(file) === out ? '' : '/');
  if (route in redirects) continue;
  const html = await readFile(file, 'utf8');
  if (/http-equiv=["']refresh["']/i.test(html) || /name=["']robots["'][^>]*content=["'][^"']*noindex/i.test(html)) continue;
  const canonical = html.match(/<link\s+rel=["']canonical["']\s+href=["']([^"']+)["']/i)?.[1];
  if (production && canonical !== origin + route) throw new Error(`Canonical non valido: ${route} (${canonical || 'assente'})`);
  if (production) urls.push(canonical);
}
if (production && !urls.length) throw new Error('Nessuna pagina trovata per la sitemap.');
const escapeXml = s => s.replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;').replaceAll('"', '&quot;').replaceAll("'", '&apos;');
if (production) {
  urls.sort();
  const xml = '<?xml version="1.0" encoding="UTF-8"?>\n<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n' + urls.map(u => `  <url><loc>${escapeXml(u)}</loc></url>`).join('\n') + '\n</urlset>\n';
  await writeFile(path.join(out, 'sitemap.xml'), xml);
}
await writeFile(path.join(out, 'robots.txt'), production ? `User-agent: *\nAllow: /\nSitemap: ${origin}/sitemap.xml\n` : 'User-agent: *\nAllow: /\n');

const rules = ['# Frammento da integrare nel .htaccess del sito definitivo; non sovrascrivere la configurazione esistente.', '<IfModule mod_rewrite.c>', 'RewriteEngine On'];
for (const [from, to] of Object.entries(redirects).sort()) {
  if (!/^\/[a-z0-9_-]+\/$/.test(from) || !/^\/[a-z0-9_/-]+\/$/.test(to)) throw new Error(`Percorso non supportato: ${from} → ${to}`);
  const target = path.join(out, to, 'index.html');
  try { await readFile(target); } catch { throw new Error(`Destinazione mancante: ${to}`); }
  rules.push(`RewriteRule ^${from.slice(1, -1)}(?:/|/index\\.html)?$ ${to} [R=301,L,NE]`);
}
rules.push('</IfModule>', '');
await mkdir('docs', { recursive: true });
await writeFile('docs/redirect-301.conf', rules.join('\n'));
console.log(`${production ? urls.length + ' URL nella sitemap' : 'Build di prova: noindex, senza sitemap'}; ${Object.keys(redirects).length} reindirizzamenti preparati.`);
