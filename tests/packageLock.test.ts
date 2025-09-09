/**
 * Tests for package-lock.json constraints based on recent dependency updates.
 *
 * Test framework: This suite uses Jest/Vitest-compatible APIs (describe/it/expect).
 * - If the project uses Vitest, run: npx vitest run
 * - If the project uses Jest, run: npx jest
 *
 * Focus: Validate presence and versions of key dependencies shown in the PR diff.
 */

import fs from 'fs';
import path from 'path';

type Json = Record<string, any>;

function readJson(p: string): Json {
  const full = path.resolve(process.cwd(), p);
  const raw = fs.readFileSync(full, 'utf8');
  return JSON.parse(raw);
}

function get(root: any, dotPath: string) {
  return dotPath.split('.').reduce((acc, k) => (acc == null ? acc : acc[k]), root);
}

describe('package-lock.json integrity (diff-focused)', () => {
  const lockPath = 'package-lock.json';
  let lock: Json;

  beforeAll(() => {
    expect(fs.existsSync(lockPath)).toBe(true);
    lock = readJson(lockPath);
  });

  it('has lockfileVersion 3 and requires=true', () => {
    expect(lock.lockfileVersion).toBe(3);
    expect(lock.requires).toBe(true);
  });

  it('root package metadata is correct', () => {
    const root = get(lock, 'packages.""');
    expect(root).toBeTruthy();
    expect(root.name).toBe('journeyplanner');
    expect(root.hasInstallScript).toBe(true);
  });

  describe('root dependency spec ranges (not resolved versions)', () => {
    const dep = (name: string) => get(lock, 'packages."" .dependencies')?.[name];
    const devDep = (name: string) => get(lock, 'packages."" .devDependencies')?.[name];

    it('contains expected prod dependencies with ranges from diff', () => {
      expect(dep('@nuxt/image')).toBe('^1.11.0');
      expect(dep('@pinia/nuxt')).toBe('^0.11.2');
      expect(dep('nuxt')).toBe('^3.16.0');
      expect(dep('pinia')).toBe('^3.0.3');
      expect(dep('primevue')).toBe('^3.52.0'); // root spec
      expect(dep('vue')).toBe('^3.5.13');
      expect(dep('vue-router')).toBe('^4.5.1');
      expect(dep('date-fns')).toBe('^4.1.0');
      expect(dep('vite')).toBeUndefined(); // vite should be brought via builder, not root dep
    });

    it('contains expected devDependencies ranges from diff', () => {
      expect(devDep('typescript')).toBe('^5.9.2');
      expect(devDep('nuxt-primevue')).toBe('^3.0.0');
      expect(devDep('eslint')).toBe('^9.28.0');
      expect(devDep('lint-staged')).toBe('^16.1.6');
      expect(devDep('mapbox-gl')).toBe('^3.14.0');
      expect(devDep('wait-on')).toBe('^8.0.4');
    });
  });

  describe('resolved package entries under node_modules (versions pinned by lock)', () => {
    const nm = (name: string) => get(lock, `packages.node_modules/${name.replace(/\//g, '\\/')}`);

    it('nuxt resolved version and engine requirements', () => {
      const nuxt = get(lock, 'packages.node_modules/nuxt');
      expect(nuxt).toBeTruthy();
      expect(nuxt.version).toBe('3.16.1');
      expect(get(nuxt, 'engines.node')).toBe('^18.12.0 || ^20.9.0 || >=22.0.0');
      // spot-check some bundled deps
      expect(get(nuxt, 'dependencies.vue')).toBe('^3.5.13');
      expect(get(nuxt, 'dependencies.vue-router')).toBe('^4.5.0');
      expect(get(nuxt, 'dependencies.nitropack')).toBe('^2.11.7');
    });

    it('nitropack resolved and cli binaries present', () => {
      const nitro = get(lock, 'packages.node_modules/nitropack');
      expect(nitro).toBeTruthy();
      expect(nitro.version).toBe('2.11.7');
      expect(get(nitro, 'bin.nitro')).toBe('dist/cli/index.mjs');
      expect(get(nitro, 'bin.nitropack')).toBe('dist/cli/index.mjs');
    });

    it('vue and @vue/* resolved versions are consistent', () => {
      const vue = get(lock, 'packages.node_modules/vue');
      expect(vue).toBeTruthy();
      expect(vue.version).toBe('3.5.18');

      const compilerSfc = get(lock, 'packages.node_modules/@vue/compiler-sfc');
      const serverRenderer = get(lock, 'packages.node_modules/@vue/server-renderer');

      expect(compilerSfc?.version).toBe('3.5.18');
      expect(serverRenderer?.version).toBe('3.5.18');

      // peer dependency lock for @vue/server-renderer expects vue 3.5.18
      expect(get(serverRenderer, 'peerDependencies.vue')).toBe('3.5.18');
    });

    it('pinia resolved version and peer deps', () => {
      const pinia = get(lock, 'packages.node_modules/pinia');
      expect(pinia).toBeTruthy();
      expect(pinia.version).toBe('3.0.3');
      expect(get(pinia, 'peerDependencies.vue')).toBe('^2.7.0 || ^3.5.11');
    });

    it('primevue resolved peer requirement', () => {
      const primevue = get(lock, 'packages.node_modules/primevue');
      expect(primevue).toBeTruthy();
      expect(primevue.version).toBe('3.53.1');
      expect(get(primevue, 'peerDependencies.vue')).toBe('^3.0.0');
    });

    it('vite resolved version and node engine', () => {
      const vite = get(lock, 'packages.node_modules/vite');
      expect(vite).toBeTruthy();
      expect(vite.version).toBe('6.3.4');
      expect(get(vite, 'engines.node')).toBe('^18.0.0 || ^20.0.0 || >=22.0.0');
    });

    it('@vitejs/plugin-vue peer requirements (vite/vue)', () => {
      const pluginVue = get(lock, 'packages.node_modules/@vitejs/plugin-vue');
      expect(pluginVue).toBeTruthy();
      expect(pluginVue.version).toBe('5.2.3');
      expect(get(pluginVue, 'peerDependencies.vite')).toBe('^5.0.0 || ^6.0.0');
      expect(get(pluginVue, 'peerDependencies.vue')).toBe('^3.2.25');
    });

    it('postcss entry is present with engines and deps', () => {
      const postcss = get(lock, 'packages.node_modules/postcss');
      expect(postcss).toBeTruthy();
      expect(postcss.version).toBe('8.5.6');
      expect(get(postcss, 'engines.node')).toBe('^10 || ^12 || >=14');
      expect(get(postcss, 'dependencies.nanoid')).toBe('^3.3.11');
    });

    it('tailwindcss dev entry has correct version and bin', () => {
      const tw = get(lock, 'packages.node_modules/tailwindcss');
      expect(tw).toBeTruthy();
      expect(tw.version).toBe('3.4.17');
      expect(get(tw, 'dev')).toBe(true);
      expect(get(tw, 'bin.tailwind')).toBe('lib/cli.js');
      expect(get(tw, 'dependencies.postcss')).toBe('^8.4.47');
    });

    it('lint-staged entry has Node >=20.17 engine requirement', () => {
      const ls = get(lock, 'packages.node_modules/lint-staged');
      expect(ls).toBeTruthy();
      expect(ls.version).toBe('16.1.6');
      expect(get(ls, 'engines.node')).toBe('>=20.17');
    });

    it('vue-router resolved version and peer', () => {
      const vr = get(lock, 'packages.node_modules/vue-router');
      expect(vr).toBeTruthy();
      expect(vr.version).toBe('4.5.1');
      expect(get(vr, 'peerDependencies.vue')).toBe('^3.2.0');
    });

    it('vee-validate resolved peer requirement on vue', () => {
      const vv = get(lock, 'packages.node_modules/vee-validate');
      expect(vv).toBeTruthy();
      expect(vv.version).toBe('4.15.1');
      expect(get(vv, 'peerDependencies.vue')).toBe('^3.4.26');
    });

    it('vite-plugin-inspect peer requires vite ^6', () => {
      const insp = get(lock, 'packages.node_modules/vite-plugin-inspect');
      expect(insp).toBeTruthy();
      expect(insp.version).toBe('11.0.0');
      expect(get(insp, 'peerDependencies.vite')).toBe('^6.0.0');
    });

    it('typescript lock entry is present with bin scripts', () => {
      const ts = get(lock, 'packages.node_modules/typescript');
      expect(ts).toBeTruthy();
      expect(ts.version).toBe('5.9.2');
      expect(get(ts, 'bin.tsc')).toBe('bin/tsc');
      expect(get(ts, 'engines.node')).toBe('>=14.17');
    });
  });

  describe('sanity checks and edge cases', () => {
    it('no unexpected nulls at key paths', () => {
      const criticalPaths = [
        'packages."" .dependencies.nuxt',
        'packages."" .devDependencies.typescript',
        'packages.node_modules/nuxt.version',
        'packages.node_modules/vue.version',
        'packages.node_modules/vite.version',
      ];
      for (const p of criticalPaths) {
        const v = get(lock, p.replace(/\s+/g, ''));
        expect(v).toBeTruthy();
      }
    });

    it('lockfile contains only supported numeric lockfileVersion', () => {
      expect(typeof lock.lockfileVersion).toBe('number');
      expect(Number.isFinite(lock.lockfileVersion)).toBe(true);
      expect(lock.lockfileVersion).toBeGreaterThanOrEqual(1);
    });
  });
});