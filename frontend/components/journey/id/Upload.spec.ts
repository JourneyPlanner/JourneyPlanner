/**
 * Tests for Upload.vue
 * Testing library/framework: Vitest + @vue/test-utils (Vue 3)
 *
 * Focused on Upload.vue script-setup logic from the PR diff:
 * - Locale selection based on Tolgee language (en -> English, de -> German)
 * - Token bootstrap with Sanctum client + localStorage caching
 * - Uppy/Tus configuration (endpoint, headers incl. Authorization, restrictions)
 * - Dashboard props (locale, disabled based on auth)
 * - "complete" event emits "uploaded" with uploadID
 */

import { describe, it, beforeEach, afterEach, expect, vi } from "vitest";
import { mount } from "@vue/test-utils";
import { defineComponent, h, ref, nextTick } from "vue";

// ---------- Mocks (before importing the SFC) ----------

// CSS imports (noop)
vi.mock("@uppy/core/css/style.css", () => ({}), { virtual: true });
vi.mock("@uppy/dashboard/css/style.css", () => ({}), { virtual: true });

// Locales with sentinels to assert exact selection
vi.mock("@uppy/locales/lib/de_DE", () => ({ default: { name: "DE_LOCALE" } }), { virtual: true });
vi.mock("@uppy/locales/lib/en_US", () => ({ default: { name: "EN_LOCALE" } }), { virtual: true });

// Tus plugin stub
vi.mock("@uppy/tus", () => {
  const Tus = function TusPlugin() {};
  return { default: Tus };
}, { virtual: true });

// Uppy core constructor stub with instance tracking
const UppyCtor = vi.fn(function (this: any, opts: any) {
  (this as any).opts = opts;
  (this as any).use = vi.fn().mockReturnThis();
  (this as any).on = vi.fn();
});
vi.mock("@uppy/core", () => ({ default: UppyCtor }), { virtual: true });

// Tolgee mock with runtime language switch
let __mockLang = "en";
export const __setTolgeeLanguage = (lang: "en" | "de") => { __mockLang = lang; };
vi.mock("@tolgee/vue", () => {
  return {
    useTolgee: () => ({
      value: {
        getLanguage: () => __mockLang,
      },
    }),
  };
});

// Dashboard stub that surfaces received props for assertions
vi.mock("@uppy/vue/dashboard", () => {
  const Comp = defineComponent({
    name: "UppyDashboardStub",
    props: {
      uppy: { type: Object, required: true },
      props: { type: Object, required: false },
    },
    setup(props) {
      return () =>
        h("div", {
          "data-test": "dashboard",
          "data-locale-name": props.props?.locale?.name ?? "",
          "data-disabled": String(props.props?.disabled ?? false),
        });
    },
  });
  return { default: Comp };
}, { virtual: true });

// Stub Nuxt composables as globals (script-setup uses them without explicit imports)
declare global {
  // eslint-disable-next-line no-var
  var useJourneyStore: () => { getID: () => string };
  // eslint-disable-next-line no-var
  var useRuntimeConfig: () => { public: { NUXT_UPLOAD_URL: string } };
  // eslint-disable-next-line no-var
  var useSanctumClient: () => (url: string) => Promise<any>;
  // eslint-disable-next-line no-var
  var useSanctumAuth: () => { isAuthenticated: { value: boolean } };
}
const defaultJourneyId = "journey-123";
const defaultUploadEndpoint = "https://upload.example.test/files";

function stubNuxtComposables({
  isAuthed = true,
  journeyId = defaultJourneyId,
  uploadUrl = defaultUploadEndpoint,
  clientImpl = async () => ({ token: "api-token-xyz" }),
}: {
  isAuthed?: boolean;
  journeyId?: string;
  uploadUrl?: string;
  clientImpl?: (url: string) => Promise<any>;
} = {}) {
  vi.stubGlobal("useJourneyStore", () => ({ getID: () => journeyId }));
  vi.stubGlobal("useRuntimeConfig", () => ({ public: { NUXT_UPLOAD_URL: uploadUrl } }));
  vi.stubGlobal("useSanctumClient", () => clientImpl);
  vi.stubGlobal("useSanctumAuth", () => ({ isAuthenticated: ref(isAuthed) }));
}

// Utility: microtask flush
const flushPromises = () => new Promise((resolve) => setTimeout(resolve, 0));

// Global stub for Tolgee <T> used in template
const globalStubs = { T: { template: "<span />" } };

// Import SFC and Tus identity after mocks
import Upload from "./Upload.vue";
import Tus from "@uppy/tus"; // mocked above

describe("Upload.vue", () => {
  beforeEach(() => {
    vi.clearAllMocks();
    localStorage.clear();
    __setTolgeeLanguage("en");
  });

  afterEach(() => {
    vi.resetModules();
  });

  it("constructs Uppy with meta.journey and default English locale; Dashboard enabled when authenticated", async () => {
    stubNuxtComposables({ isAuthed: true });
    const wrapper = mount(Upload, { global: { stubs: globalStubs } });

    await flushPromises();
    await nextTick();

    expect(UppyCtor).toHaveBeenCalledTimes(1);
    const instance: any = (UppyCtor as any).mock.instances[0];
    expect(instance.opts?.meta?.journey).toBe(defaultJourneyId);
    expect(instance.opts?.locale?.name).toBe("EN_LOCALE");

    const dashboard = wrapper.get('[data-test="dashboard"]');
    expect(dashboard.attributes("data-locale-name")).toBe("EN_LOCALE");
    expect(dashboard.attributes("data-disabled")).toBe("false");
  });

  it("uses German locale when Tolgee language is 'de'", async () => {
    __setTolgeeLanguage("de");
    stubNuxtComposables({ isAuthed: true });

    const wrapper = mount(Upload, { global: { stubs: globalStubs } });
    await flushPromises();

    const instance: any = (UppyCtor as any).mock.instances[0];
    expect(instance.opts?.locale?.name).toBe("DE_LOCALE");

    const dashboard = wrapper.get('[data-test="dashboard"]');
    expect(dashboard.attributes("data-locale-name")).toBe("DE_LOCALE");
  });

  it("disables Dashboard when not authenticated", async () => {
    stubNuxtComposables({ isAuthed: false });

    const wrapper = mount(Upload, { global: { stubs: globalStubs } });
    await flushPromises();

    const dashboard = wrapper.get('[data-test="dashboard"]');
    expect(dashboard.attributes("data-disabled")).toBe("true");
  });

  it("fetches and stores upload token when missing and authenticated, and passes it to Tus headers", async () => {
    expect(localStorage.getItem("JP_upload_token")).toBeNull();

    const clientSpy = vi.fn(async () => ({ token: "fresh-token-123" }));
    stubNuxtComposables({ isAuthed: true, clientImpl: clientSpy });

    mount(Upload, { global: { stubs: globalStubs } });
    await flushPromises();

    expect(clientSpy).toHaveBeenCalledWith("/api/user/tokens/upload");
    expect(localStorage.getItem("JP_upload_token")).toBe("fresh-token-123");

    const instance: any = (UppyCtor as any).mock.instances[0];
    const useCalls = (instance.use as any).mock.calls as any[];
    const tusCall = useCalls.find(([plugin]) => plugin === Tus);
    expect(tusCall).toBeTruthy();
    const tusOpts = tusCall[1];

    expect(tusOpts?.endpoint).toBe(defaultUploadEndpoint);
    expect(tusOpts?.headers?.Authorization).toBe("Bearer fresh-token-123");
    expect(tusOpts?.removeFingerprintOnSuccess).toBe(true);
  });

  it("does not fetch token when already present in localStorage; uses stored token", async () => {
    localStorage.setItem("JP_upload_token", "cached-token-777");

    const clientSpy = vi.fn(async () => ({ token: "should-not-be-used" }));
    stubNuxtComposables({ isAuthed: true, clientImpl: clientSpy });

    mount(Upload, { global: { stubs: globalStubs } });
    await flushPromises();

    expect(clientSpy).not.toHaveBeenCalled();

    const instance: any = (UppyCtor as any).mock.instances[0];
    const tusOpts = ((instance.use as any).mock.calls as any[]).find(([p]: any[]) => p === Tus)?.[1];

    expect(tusOpts?.headers?.Authorization).toBe("Bearer cached-token-777");
  });

  it("does not fetch token when unauthenticated; Authorization header includes 'null'", async () => {
    localStorage.removeItem("JP_upload_token");

    const clientSpy = vi.fn(async () => ({ token: "should-not-be-called" }));
    stubNuxtComposables({ isAuthed: false, clientImpl: clientSpy });

    mount(Upload, { global: { stubs: globalStubs } });
    await flushPromises();

    expect(clientSpy).not.toHaveBeenCalled();

    const instance: any = (UppyCtor as any).mock.instances[0];
    const tusOpts = ((instance.use as any).mock.calls as any[]).find(([p]: any[]) => p === Tus)?.[1];

    // From current code: upload_token remains null -> "Bearer null"
    expect(tusOpts?.headers?.Authorization).toBe("Bearer null");
  });

  it("configures file restrictions (size and types) on Uppy", async () => {
    stubNuxtComposables({ isAuthed: true });
    mount(Upload, { global: { stubs: globalStubs } });
    await flushPromises();

    const instance: any = (UppyCtor as any).mock.instances[0];
    const restrictions = instance.opts?.restrictions;

    expect(restrictions?.maxFileSize).toBe(1024 * 1024 * 1024);
    expect(restrictions?.allowedFileTypes).toEqual([
      "image/*",
      "video/*",
      ".pdf",
      ".txt",
    ]);
  });

  it('emits "uploaded" with uploadID when Uppy fires complete', async () => {
    stubNuxtComposables({ isAuthed: true });
    const wrapper = mount(Upload, { global: { stubs: globalStubs } });
    await flushPromises();

    const instance: any = (UppyCtor as any).mock.instances[0];
    const onCalls = (instance.on as any).mock.calls as any[];
    const completeCall = onCalls.find(([evt]) => evt === "complete");
    expect(completeCall).toBeTruthy();

    const handler = completeCall[1];
    handler({ uploadID: "upload-42" });

    const emitted = wrapper.emitted("uploaded");
    expect(emitted).toBeTruthy();
    expect(emitted?.[0]?.[0]).toBe("upload-42");
  });

  it("handles undefined token from server: stores empty string and uses 'Bearer undefined' header", async () => {
    expect(localStorage.getItem("JP_upload_token")).toBeNull();

    const clientSpy = vi.fn(async () => ({ token: undefined }));
    stubNuxtComposables({ isAuthed: true, clientImpl: clientSpy });

    mount(Upload, { global: { stubs: globalStubs } });
    await flushPromises();

    expect(clientSpy).toHaveBeenCalledWith("/api/user/tokens/upload");
    expect(localStorage.getItem("JP_upload_token")).toBe("");

    const instance: any = (UppyCtor as any).mock.instances[0];
    const tusOpts = ((instance.use as any).mock.calls as any[]).find(([p]: any[]) => p === Tus)?.[1];
    expect(tusOpts?.headers?.Authorization).toBe("Bearer undefined");
  });

  it("handles empty string token from server: stores empty string and uses 'Bearer ' header", async () => {
    expect(localStorage.getItem("JP_upload_token")).toBeNull();

    const clientSpy = vi.fn(async () => ({ token: "" }));
    stubNuxtComposables({ isAuthed: true, clientImpl: clientSpy });

    mount(Upload, { global: { stubs: globalStubs } });
    await flushPromises();

    expect(clientSpy).toHaveBeenCalledWith("/api/user/tokens/upload");
    expect(localStorage.getItem("JP_upload_token")).toBe("");

    const instance: any = (UppyCtor as any).mock.instances[0];
    const tusOpts = ((instance.use as any).mock.calls as any[]).find(([p]: any[]) => p === Tus)?.[1];
    expect(tusOpts?.headers?.Authorization).toBe("Bearer ");
  });
});