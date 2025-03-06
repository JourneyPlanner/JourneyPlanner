<script setup lang="ts">
import { useTranslate } from "@tolgee/vue";
import { useRoute } from "vue-router";

const toast = useToast();
const { t } = useTranslate();
const client = useSanctumClient();
const { login } = useSanctumAuth();

useHead({
    title: `Microsoft Auth | JourneyPlanner`,
});

defineRouteRules({
    robots: false,
});

const route = useRoute();
const code = route.query.code as string;

onMounted(async () => {
    try {
        if (code) {
            await client("/auth/callback/microsoft", {
                params: { code: code },
            });

            toast.add({
                severity: "success",
                summary: t.value("common.toast.success.heading"),
                detail: t.value("form.login.toast.success"),
                life: 3000,
            });
            await login({});
            navigateTo("/dashboard");
        } else {
            navigateTo("/login");
        }
    } catch {
        toast.add({
            severity: "error",
            summary: t.value("common.toast.error.heading"),
            detail: t.value("common.error.unknown"),
            life: 3000,
        });
        navigateTo("/login");
    }
});
</script>

<template>
    <div class="mt-32 flex flex-col items-center justify-center">
        <SvgLogin class="w-72" />
        <ProgressSpinner class="mt-2" />
        <span class="mt-5 text-xl font-medium text-text dark:text-natural-50"
            ><T key-name="login.microsoft"
        /></span>
    </div>
</template>
