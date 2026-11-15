import { news, formatDate } from '../../lib/news.js';
import documenti from '../../data/documenti.json';

const pagineMd = import.meta.glob('../../content/pagine/*.md', { eager: true });

// Testo semplice: senza markdown, indirizzi e simboli.
const pulito = (s, max) => {
  const t = String(s ?? '')
    .replace(/^---[\s\S]*?---/, ' ')
    .replace(/!?\[([^\]]*)\]\([^)]*\)/g, '$1')
    .replace(/<[^>]*>/g, ' ')
    .replace(/https?:\/\/\S+/g, ' ')
    .replace(/[#*_>`|]/g, ' ')
    .replace(/\s+/g, ' ')
    .trim();
  return max ? t.slice(0, max) : t;
};

const voci = [];

// Pagine del sito. Il testo è la descrizione già presente nella pagina; Lavoro resta senza contenuti dei curricula.
const pagine = [
  ['Chi siamo', '/chi-siamo/', 'La storia di A.FA.N.T. Arezzo, associazione dei farmacisti non titolari della provincia di Arezzo.'],
  ['Iscrizione', '/iscrizione/', 'Richiesta di iscrizione ad A.FA.N.T. Arezzo. Quota annuale, bonifico, modulo di iscrizione.'],
  ['Notizie', '/notizie/', 'Comunicazioni, convocazioni, newsletter e iniziative di A.FA.N.T. Arezzo dal 2014.'],
  ['Appuntamenti AFANT', '/appuntamenti/', 'Convocazioni di assemblee e incontri di A.FA.N.T. Arezzo, con i file allegati.'],
  ['Documenti', '/documenti/', 'Statuto, codice deontologico, contratti, moduli e circolari di A.FA.N.T. Arezzo.'],
  ['CCNL', '/ccnl/', 'I contratti collettivi nazionali per i dipendenti di farmacia, con quelli in vigore in evidenza.'],
  ['Lavoro', '/lavoro/', 'Il servizio di A.FA.N.T. Arezzo per il lavoro dei farmacisti: offerte, cerco/offro e curricula.'],
  ['Contatti', '/contatti/', 'Contatta A.FA.N.T. Arezzo. Modulo contatti, indirizzo, email e telefono.'],
  ['Link utili', '/link-utili/', 'Collegamenti utili per i farmacisti: Conasfa, Sinasfa, FOFI, Ordine, fondi pensione.'],
  ['Presentazione Sinasfa', '/presentazione-sinasfa/', 'Sinasfa, il sindacato nazionale dei farmacisti non titolari.'],
  ['Privacy e cookie', '/privacy/', 'Informativa su privacy e cookie di A.FA.N.T. Arezzo.'],
];
const testoPagina = (url) => {
  const nome = url.replaceAll('/', '');
  const m = Object.entries(pagineMd).find(([p]) => p.endsWith(`/${nome}.md`));
  return m ? pulito(m[1].rawContent(), 1200) : '';
};
for (const [t, u, d] of pagine) voci.push({ t, u, k: 'Pagina', x: `${d} ${testoPagina(u)}`.trim() });

for (const n of news) {
  voci.push({ t: n.title, u: `/notizie/${n.slug}/`, k: n.categoria === 'Appuntamenti' ? 'Appuntamenti' : 'Notizie', c: n.categoria, d: formatDate(n.date), i: n.date, x: pulito(n.raw, 1000) });
}

for (const d of documenti) {
  voci.push({ t: d.titolo, u: d.file ?? '/documenti/', k: 'Documento', c: d.tema, d: d.data ? formatDate(d.data) : (d.anno ? String(d.anno) : ''), i: d.data ?? (d.anno ? `${d.anno}-01-01` : ''), x: '' });
}

export function GET() {
  return new Response(`window.AFANT_INDICE=${JSON.stringify(voci)};`, { headers: { 'Content-Type': 'text/javascript; charset=utf-8' } });
}
