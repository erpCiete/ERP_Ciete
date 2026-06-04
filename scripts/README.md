# Scripts de desarrollo

## `generate-favicon.cjs` / `generate-favicon.js`

Generadores del favicon SVG del ERP. Convierten el SVG fuente a los formatos necesarios (PNG, ICO).

- **`generate-favicon.cjs`** — versión CommonJS (compatibilidad con entornos sin ESM)
- **`generate-favicon.js`** — versión ESM

Solo usar si se necesita regenerar el favicon. Requiere `sharp` y `png-to-ico`:

```bash
npm install sharp png-to-ico
node scripts/generate-favicon.cjs
```

El favicon actual está en `public/favicon.svg`.
