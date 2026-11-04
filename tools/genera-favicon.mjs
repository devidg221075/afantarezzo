// Ricava il simbolo vettoriale dal logo ufficiale AFANT e genera le icone browser.
import { readFile, writeFile } from 'node:fs/promises';
import sharp from 'sharp';

const logo = await readFile('public/brand/afant.svg', 'utf8');
const symbol = logo.match(/<g id="_x35_nOW4P1"[\s\S]*?<\/g>/)?.[0];
if (!symbol) throw new Error('Simbolo non trovato nel logo ufficiale.');
const svg = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="130 195 100 100"><rect x="130" y="195" width="100" height="100" rx="18" fill="#fff"/><style>.st1{fill:#1d71b8}</style>${symbol}</svg>`;
await writeFile('public/favicon.svg', svg);
const png = size => sharp(Buffer.from(svg), { density: 288 }).resize(size, size).png().toBuffer();
await writeFile('public/favicon-32.png', await png(32));
await writeFile('public/apple-touch-icon.png', await png(180));
const images = await Promise.all([16, 32, 48].map(png));
const header = Buffer.alloc(6 + images.length * 16);
header.writeUInt16LE(1, 2); header.writeUInt16LE(images.length, 4);
let offset = header.length;
for (const [i, image] of images.entries()) {
  const pos = 6 + i * 16; const size = [16, 32, 48][i];
  header[pos] = size; header[pos + 1] = size;
  header.writeUInt16LE(1, pos + 4); header.writeUInt16LE(32, pos + 6);
  header.writeUInt32LE(image.length, pos + 8); header.writeUInt32LE(offset, pos + 12);
  offset += image.length;
}
await writeFile('public/favicon.ico', Buffer.concat([header, ...images]));
