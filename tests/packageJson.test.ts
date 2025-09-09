/**
 * packageJson.test.ts
 *
 * Thorough validation of frontend/package.json contents as per PR diff.
 * Testing library/framework: Node.js built-in test runner (node:test) with node:assert/strict.
 * Run (if desired) with: node --test (Node 18+); when using a TS runner/transformer.
 */

import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { test } from 'node:test';
import assert from 'node:assert/strict';

type Dict = Record<string, string>;
type Pkg = {
  name?: string;
  private?: boolean;
  type?: string;
  scripts?: Dict;
  dependencies?: Dict;
  devDependencies?: Dict;
  ['lint-staged']?: Record<string, string[]>;
};

function repoRoot(): string {
  const __filename = fileURLToPath(import.meta.url);
  let dir = path.dirname(__filename);
  for (let i = 0; i < 6; i++) {
    if (fs.existsSync(path.join(dir, 'frontend', 'package.json'))) return dir;
    const up = path.dirname(dir);
    if (up === dir) break;
    dir = up;
  }
  return process.cwd();
}

function loadPkg(): Pkg {
  const root = repoRoot();
  const candidates = [
    path.join(root, 'frontend', 'package.json'), // primary target (per PR diff)
    path.join(root, 'package.json'),
    path.join(root, 'app', 'package.json'),
    path.join(root, 'mail', 'package.json'),
    path.join(root, 'backend', 'package.json'),
  ];
  for (const p of candidates) {
    if (fs.existsSync(p)) {
      const raw = fs.readFileSync(p, 'utf8');
      return JSON.parse(raw) as Pkg;
    }
  }
  throw new Error('package.json not found in expected locations.');
}

function hasKey(obj: Record<string, unknown> | undefined, key: string): boolean {
  return obj != null && Object.prototype.hasOwnProperty.call(obj, key);
}

/* -------------------- Basic metadata -------------------- */
test('package.json: basic metadata', () => {
  const pkg = loadPkg();
  assert.equal(pkg.name, 'journeyplanner');
  assert.equal(pkg.private, true);
  assert.equal(pkg.type, 'module');
});

/* -------------------- Scripts (exact and partial checks) -------------------- */
test('scripts: essential nuxt lifecycle scripts are exact', () => {
  const s = loadPkg().scripts as Dict;
  assert.equal(s.build, 'nuxt build');
  assert.equal(s.dev, 'nuxt dev');
  assert.equal(s['dev:host'], 'nuxt dev --host 0 --port 3000');
  assert.equal(s.generate, 'nuxt generate');
  assert.equal(s.preview, 'nuxt preview');
  assert.equal(s.postinstall, 'nuxt prepare');
  assert.equal(s.prepare, 'cd .. && husky frontend/.husky');
});

test('scripts: start orchestration (dev) contains all critical segments', () => {
  const s = loadPkg().scripts as Dict;
  const start = s.start ?? '';
  assert.ok(start.includes('concurrently'));
  assert.ok(start.includes('infisical run --env=dev -- nuxt dev -o'));
  assert.ok(start.includes('cd ../docker/dev'));
  assert.ok(start.includes('docker compose up'));
  assert.ok(start.includes('cd ../backend'));
  assert.ok(start.includes('wait-on tcp:3306 -d 10000 --log'));
  assert.ok(start.includes('php artisan migrate'));
  assert.ok(start.includes('php artisan serve'));
});

test('scripts: start:host includes host binding and wait-on 5000', () => {
  const s = loadPkg().scripts as Dict;
  const cmd = s['start:host'] ?? '';
  assert.ok(cmd.includes('nuxt dev --host 0 --port 3000 -o'));
  assert.ok(cmd.includes('wait-on tcp:3306 -d 5000 --log'));
});

test('scripts: staging variants correct content', () => {
  const s = loadPkg().scripts as Dict;
  assert.ok((s['start:staging'] ?? '').includes('infisical run --env=dev -- nuxt dev -o'));
  assert.ok((s['start:staging'] ?? '').includes('infisical run --env=staging -- php artisan serve'));

  assert.ok((s['start:staging:host'] ?? '').includes('nuxt dev --host 0 --port 3000 -o'));
  assert.ok((s['start:staging:host'] ?? '').includes('infisical run --env=staging -- php artisan serve'));
});

test('scripts: utilities are present and correct', () => {
  const s = loadPkg().scripts as Dict;
  assert.ok((s['clone-staging-db'] ?? '').includes('@powershell -NoProfile -ExecutionPolicy Unrestricted -Command ./clone_db.ps1'));
  const installAll = s['install-all'] ?? '';
  assert.ok(installAll.includes('npm install'));
  assert.ok(installAll.includes('composer install'));
  assert.ok(installAll.includes('docker compose pull'));
});

/* -------------------- Dependencies (exact versions per diff) -------------------- */
test('dependencies: core frameworks exact versions', () => {
  const d = loadPkg().dependencies as Dict;
  assert.equal(d['nuxt'], '^3.16.0');
  assert.equal(d['vue'], '^3.5.13');
  assert.equal(d['pinia'], '^3.0.3');
  assert.equal(d['vue-router'], '^4.5.1');
});

test('dependencies: UI and utility packages exact versions', () => {
  const d = loadPkg().dependencies as Dict;
  assert.equal(d['primevue'], '^3.52.0');
  assert.equal(d['primeicons'], '^7.0.0');
  assert.equal(d['date-fns'], '^4.1.0');
  assert.equal(d['uuid'], '^12.0.0');
  assert.equal(d['qrcode'], '^1.5.4');
  assert.equal(d['concurrently'], '^9.2.1');
  assert.equal(d['file-saver'], '^2.0.5');
  assert.equal(d['js-confetti'], '^0.13.1');
  assert.equal(d['jszip'], '^3.10.1');
  assert.equal(d['lightgallery'], '^2.8.3');
  assert.equal(d['vue-animated-counter'], '^0.3.0');
  assert.equal(d['@date-fns/utc'], '^2.1.0');
});

test('dependencies: FullCalendar, Mapbox, Tolgee, VeeValidate, Uppy stacks exact versions', () => {
  const d = loadPkg().dependencies as Dict;
  // FullCalendar
  assert.equal(d['@fullcalendar/core'], '^6.1.17');
  assert.equal(d['@fullcalendar/daygrid'], '^6.1.15');
  assert.equal(d['@fullcalendar/interaction'], '^6.1.15');
  assert.equal(d['@fullcalendar/timegrid'], '^6.1.15');
  assert.equal(d['@fullcalendar/vue3'], '^6.1.17');

  // Mapbox
  assert.equal(d['@mapbox/mapbox-gl-geocoder'], '^5.1.2');
  assert.equal(d['@mapbox/search-js-web'], '^1.3.0');
  assert.equal(hasKey(d, '@studiometa/vue-mapbox-gl'), true);
  assert.equal(d['@studiometa/vue-mapbox-gl'], '^2.7.2');

  // Nuxt modules and i18n/validation
  assert.equal(d['@nuxt/eslint'], '^1.9.0');
  assert.equal(d['@nuxt/image'], '^1.11.0');
  assert.equal(d['@nuxtjs/seo'], '^3.1.0');
  assert.equal(d['@pinia/nuxt'], '^0.11.2');
  assert.equal(d['@tolgee/vue'], '^6.2.7');
  assert.equal(d['@vee-validate/nuxt'], '^4.15.1');
  assert.equal(d['@vee-validate/yup'], '^4.15.1');
  assert.equal(d['yup'], '^1.7.0');

  // Uppy
  assert.equal(d['@uppy/core'], '^5.0.1');
  assert.equal(d['@uppy/dashboard'], '^5.0.1');
  assert.equal(d['@uppy/locales'], '^5.0.0');
  assert.equal(d['@uppy/tus'], '^5.0.0');
  assert.equal(d['@uppy/vue'], '^3.0.2');

  // Gravatar editor
  assert.equal(d['@gravatar-com/quick-editor'], '^0.8.0');
});

/* -------------------- DevDependencies (exact versions per diff) -------------------- */
test('devDependencies: Nuxt modules and tooling versions', () => {
  const dev = loadPkg().devDependencies as Dict;
  assert.equal(dev['@nuxtjs/color-mode'], '^3.5.2');
  assert.equal(dev['@nuxtjs/tailwindcss'], '^6.14.0');
  assert.equal(dev['nuxt-auth-sanctum'], '^0.6.6');
  assert.equal(dev['nuxt-primevue'], '^3.0.0');
  assert.equal(dev['mapbox-gl'], '^3.14.0');

  // Types support
  assert.equal(dev['@types/mapbox__mapbox-gl-geocoder'], '^5.0.0');
  assert.equal(dev['@types/mapbox-gl'], '^3.4.1');
  assert.equal(dev['@types/qrcode'], '^1.5.5');

  // Lint/format/TS toolchain
  assert.equal(dev['eslint'], '^9.28.0');
  assert.equal(dev['prettier'], '^3.6.2');
  assert.equal(dev['@trivago/prettier-plugin-sort-imports'], '^5.2.2');
  assert.equal(dev['prettier-plugin-tailwindcss'], '^0.6.14');
  assert.equal(dev['typescript'], '^5.9.2');
  assert.equal(dev['vue-tsc'], '^3.0.6');

  // Utilities
  assert.equal(dev['husky'], '^9.1.5');
  assert.equal(dev['lint-staged'], '^16.1.6');
  assert.equal(dev['wait-on'], '^8.0.4');
});

/* -------------------- lint-staged configuration -------------------- */
test('lint-staged configuration: runs eslint --fix and prettier --write on *.*', () => {
  const ls = loadPkg()['lint-staged'];
  assert.ok(ls, 'lint-staged should be defined');
  const all = ls['*.*'];
  assert.ok(Array.isArray(all));
  assert.ok(all.includes('eslint --fix'));
  assert.ok(all.includes('prettier --write'));
});

/* -------------------- Defensive parsing -------------------- */
test('defensive parsing: minimal object does not throw and fields are optional', () => {
  const minimal: Pkg = JSON.parse('{"name":"x","private":true,"type":"module"}');
  assert.equal(minimal.name, 'x');
  assert.equal(typeof (minimal.scripts ?? {}), 'object');
  assert.equal(typeof (minimal.dependencies ?? {}), 'object');
  assert.equal(typeof (minimal.devDependencies ?? {}), 'object');
});