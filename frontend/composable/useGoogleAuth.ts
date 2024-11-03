import { useTranslate } from "@tolgee/vue";

declare global {
    interface Window {
        handleGoogleCredentialResponse: (response: any) => void;
    }
}

export function useGoogleAuth() {
    const client = useSanctumClient();
    const toast = useToast();
    const { t } = useTranslate();
    const { login } = useSanctumAuth();

    // Define the global callback function for Google authentication
    window.handleGoogleCredentialResponse = async (response: any) => {
        try {
            await client("/auth/callback/google", {
                method: "POST",
                body: { token: response.credential },
            });
            toast.add({
                severity: "success",
                summary: t.value("common.toast.success.heading"),
                detail: t.value("form.login.toast.success"),
                life: 3000,
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

    onMounted(() => {
        // Load Google script if not already loaded
        if (!document.getElementById("google-login-script")) {
            const googleLoginScript = document.createElement("script");
            googleLoginScript.id = "google-login-script";
            googleLoginScript.src = "https://accounts.google.com/gsi/client";
            document.head.appendChild(googleLoginScript);
        }
    });
}
