// Verifica i vincoli del cliente sulla build: CSS sotto 30 KB, font solo incorporati (max 2 famiglie, 150 KB), nessuna richiesta esterna.
import { readdirSync, readFileSync, statSync } from 'node:fs';
import { join } from 'node:path';
const dist = 'dist';
const walk = (d) => readdirSync(d).flatMap((f) => { const p = join(d, f); return statSync(p).isDirectory() ? walk(p) : [p]; });
const files = walk(dist);
let ko = 0;
const css = files.filter((f) => f.endsWith('.css'));
const kb = css.reduce((s, f) => s + statSync(f).size, 0) / 1024;
console.log(`CSS: ${css.length} file, ${kb.toFixed(1)} KB (limite 30 KB)`); if (kb >= 30) ko++;
const fonts = files.filter((f) => /\.(woff2?|ttf|otf|eot)$/i.test(f));
const fkb = fonts.reduce((s, f) => s + statSync(f).size, 0) / 1024;
console.log(`Font incorporati: ${fonts.length} file, ${fkb.toFixed(0)} KB (limite 150 KB)`); if (fonts.length > 2 || fkb > 150) ko++;
const ext = new Set();
for (const f of files.filter((f) => /\.(html|css|js)$/.test(f))) {
  const t = readFileSync(f, 'utf8');
  for (const m of t.matchAll(/(?:src|href)=["'](https?:)?\/\/[^"']+["']/g)) if (/<(script|link|img|iframe)[^>]*$/i.test(t.slice(Math.max(0, m.index - 40), m.index))) ext.add(m[0]);
  for (const m of t.matchAll(/url\(\s*["']?https?:\/\/[^)]+\)/g)) ext.add(m[0]);
  for (const m of t.matchAll(/@import\s+["']?https?:/g)) ext.add(m[0]);
}
console.log(`Richieste esterne: ${ext.size}`); if (ext.size) { [...ext].slice(0, 10).forEach((e) => console.log('  ', e)); ko++; }
process.exit(ko ? 1 : 0);
