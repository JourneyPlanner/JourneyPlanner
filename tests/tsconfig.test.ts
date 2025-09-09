/**
 * tsconfig.json configuration tests
 *
 * Testing library/framework note:
 * - These tests use the Vitest style API: describe/it/expect from 'vitest'.
 * - If this project uses Jest, replace the import line with:
 *     import { describe, it, expect } from '@jest/globals';
 *   The rest of the tests should run unchanged.
 *
 * Scope:
 * - Validates structure and critical options of the root tsconfig.json.
 * - Focuses on the PR-changed fields: "extends", "include" pattern, and compilerOptions
 *   such as moduleResolution, esModuleInterop, allowSyntheticDefaultImports.
 *
 * Rationale:
 * - Guards against common mistakes like using "includes" instead of "include"
 *   and ensures Nuxt's recommended settings remain intact.
 */
import { describe, it, expect } from 'vitest';
import fs from 'node:fs';
import path from 'node:path';

function readTsconfigJsonc(filePath: string): any {
  const raw = fs.readFileSync(filePath, 'utf8');
  // Simple JSONC stripping: remove /* ... */ and // ... comments.
  // Note: This is conservative and assumes no comment tokens appear inside string literals.
  const withoutBlock = raw.replace(/\/\*[\s\S]*?\*\//g, '');
  const withoutLine = withoutBlock.replace(/(^|\s)\/\/.*$/gm, '');
  try {
    return JSON.parse(withoutLine);
  } catch (e) {
    // Provide a clearer message with a snippet for quick debugging
    const preview = raw.split('\n').slice(0, 20).join('\n');
    throw new Error(
      'Failed to parse tsconfig.json as JSONC. Preview:\n' +
        preview +
        '\nOriginal error: ' +
        (e as Error).message
    );
  }
}

function getRootTsconfigPath(): string {
  const candidate = path.resolve(process.cwd(), 'tsconfig.json');
  expect(fs.existsSync(candidate)).toBe(true);
  return candidate;
}

describe('tsconfig.json', () => {
  const tsconfigPath = getRootTsconfigPath();
  const config = readTsconfigJsonc(tsconfigPath);

  it('is an object and contains expected top-level keys', () => {
    expect(config && typeof config === 'object').toBe(true);
    // Expected keys at minimum
    expect(Object.keys(config)).toEqual(
      expect.arrayContaining(['extends', 'compilerOptions'])
    );
  });

  it('has a relative "extends" that targets .nuxt/tsconfig.json', () => {
    expect(typeof config.extends).toBe('string');
    const ext = String(config.extends);
    // Should be a relative path and end with the Nuxt tsconfig
    expect(ext.startsWith('.')).toBe(true);
    expect(ext.replace(/^\.\//, '')).toBe('.nuxt/tsconfig.json');
    expect(ext.endsWith('.nuxt/tsconfig.json')).toBe(true);
  });

  it('uses the correct "include" array and does not use the incorrect "includes" key', () => {
    // The canonical key is "include"
    expect('include' in config).toBe(true);
    // Guard against common typo
    expect('includes' in config).toBe(false);

    const include = config.include;
    expect(Array.isArray(include)).toBe(true);
    // Must include type declaration pattern
    expect(include).toEqual(expect.arrayContaining(['types/*.d.ts']));
  });

  it('defines compilerOptions with required values', () => {
    expect(
      config.compilerOptions && typeof config.compilerOptions === 'object'
    ).toBe(true);
    const co = config.compilerOptions as Record<string, unknown>;

    // moduleResolution should be "bundler" for Nuxt + TS 5 workflows
    expect(co.moduleResolution).toBe('bundler');

    // Interop flags commonly needed with ESM ecosystems
    expect(co.esModuleInterop).toBe(true);
    expect(co.allowSyntheticDefaultImports).toBe(true);
  });

  it('contains no obviously misspelled critical compilerOptions', () => {
    const co = config.compilerOptions as Record<string, unknown>;
    // Spot-check a few common typos/misspellings to fail fast if introduced
    const forbiddenKeys = [
      'moduleResolutions',             // typo
      'esModulesInterop',              // typo
      'allowSynthethicDefaultImports', // typo
      'allowSyntheticDefaultImport',   // singular typo
    ];
    for (const k of forbiddenKeys) {
      expect(
        Object.prototype.hasOwnProperty.call(co, k)
      ).toBe(false);
    }
  });

  it('does not declare top-level "files" and "exclude" that could conflict with include settings (sanity check)', () => {
    // These may legitimately exist in some repos; we only ensure they are not set
    // in ways that would undermine the include pattern for types/*.d.ts.
    if ('files' in config) {
      const files = config.files;
      // If present, it should be an array and must not exclude our types/*.d.ts
      expect(Array.isArray(files)).toBe(true);
      expect(files).not.toEqual(
        expect.arrayContaining(['types/*.d.ts'])
      );
    }
    if ('exclude' in config) {
      const exclude = config.exclude;
      // If present, it should be an array and must not exclude our types directory
      if (Array.isArray(exclude)) {
        const asStrings = exclude.map(String);
        expect(asStrings).not.toEqual(
          expect.arrayContaining(['types', 'types/*.d.ts'])
        );
      }
    }
  });
});