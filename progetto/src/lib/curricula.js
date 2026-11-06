// I curricula sono dati personali: il file dati e i PDF NON stanno nel repository (vedi .gitignore).
// Se src/data/curricula.json non esiste, la sezione resta vuota.
const mods = import.meta.glob('../data/curricula.json', { eager: true });
const raw = Object.values(mods)[0]?.default ?? [];

const oggi = new Date().toISOString().slice(0, 10);

// Si mostra solo chi ha dato il consenso scritto e non ha superato la scadenza indicata.
export const curricula = raw.filter((c) => c.consensoScritto === true && (!c.scadenza || c.scadenza >= oggi));
