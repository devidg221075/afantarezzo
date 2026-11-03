const mods = import.meta.glob('../content/news/*.md', { eager: true });

const MESI = ['gennaio','febbraio','marzo','aprile','maggio','giugno','luglio','agosto','settembre','ottobre','novembre','dicembre'];

export function formatDate(iso) {
  const [y, m, d] = iso.split('-').map(Number);
  return `${d} ${MESI[m - 1]} ${y}`;
}

export function excerpt(raw, n = 28) {
  const body = raw.replace(/^---[\s\S]*?---/, '').replace(/[#*_>\[\]()`]/g, ' ').replace(/\s+/g, ' ').trim();
  const w = body.split(' ');
  return w.slice(0, n).join(' ') + (w.length > n ? '…' : '');
}

export const news = Object.values(mods)
  .map((m) => ({ ...m.frontmatter, Content: m.Content, raw: m.rawContent() }))
  .sort((a, b) => (a.date < b.date ? 1 : a.date > b.date ? -1 : a.title.localeCompare(b.title)));

export const categorie = [...new Set(news.map((n) => n.categoria))].sort((a, b) =>
  a === 'Altro' ? 1 : b === 'Altro' ? -1 : a.localeCompare(b, 'it'));
