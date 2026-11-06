import { defineConfig } from 'astro/config';
import redirects from './src/data/redirects.json' with { type: 'json' };
import pageRedirects from './src/data/redirects-pagine.json' with { type: 'json' };

// Gli indirizzi vecchi degli articoli (/slug/) puntano ai nuovi (/notizie/slug/).
export default defineConfig({
  output: 'static',
  trailingSlash: 'always',
  build: { format: 'directory' },
  redirects: { ...redirects, ...pageRedirects },
});
