<script setup lang="ts">
import { useTolgee, useTranslate } from "@tolgee/vue";

const client = useSanctumClient();
const toast = useToast();
const { t } = useTranslate();
const { login } = useSanctumAuth();
const config = useRuntimeConfig();
const tolgee = useTolgee(["language"]);

const google = ref();

interface GoogleCredentialResponse {
    credential: string;
}

declare global {
    interface Window {
        handleGoogleCredentialResponse: (
            response: GoogleCredentialResponse,
        ) => void;
        google?: {
            accounts: {
                id: {
                    initialize: (config: {
                        client_id: string;
                        callback: (response: GoogleCredentialResponse) => void;
                    }) => void;
                    renderButton: (
                        div: HTMLElement | null,
                        options: {
                            theme: string;
                            size: string;
                            shape: string;
                            text: string;
                            locale: string;
                        },
                    ) => void;
                };
            };
        };
    }
}

// Define the global callback function for Google authentication
window.handleGoogleCredentialResponse = async (
    response: GoogleCredentialResponse,
) => {
    try {
        await client("/auth/callback/google", {
            method: "POST",
            body: { token: response.credential },
        });
        await login({});
        navigateTo("/dashboard");
    } catch (error) {
        toast.add({
            severity: "error",
            summary: t.value("common.toast.error.heading"),
            detail: t.value("common.error.unknown"),
            life: 3000,
        });
    }
};

onMounted(async () => {
    await nextTick();
    if (!document.getElementById("google-login-script")) {
        const googleLoginScript = document.createElement("script");
        googleLoginScript.id = "google-login-script";
        googleLoginScript.src = "https://accounts.google.com/gsi/client";
        googleLoginScript.onload = initializeGoogleSignIn;
        document.head.appendChild(googleLoginScript);
    } else {
        initializeGoogleSignIn();
    }
});

const initializeGoogleSignIn = () => {
    if (window.google && window.google.accounts) {
        window.google.accounts.id.initialize({
            client_id: config.public.NUXT_GOOGLE_CLIENT_ID,
            callback: window.handleGoogleCredentialResponse,
        });

        window.google.accounts.id.renderButton(google.value, {
            theme: "outline",
            size: "large",
            shape: "rectangular",
            text: "continue_with",
            locale: tolgee.value.getLanguage() || "en",
        });
    }
};
</script>

<template>
    <div
        id="g_id_onload"
        :data-client_id="config.public.NUXT_GOOGLE_CLIENT_ID"
        data-context="use"
        data-callback="handleGoogleCredentialResponse"
        data-itp_support="true"
    ></div>
    <div id="g_id_signin" ref="google"></div>
</template>
