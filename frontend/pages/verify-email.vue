<script setup lang="ts">
import { useTranslate } from "@tolgee/vue";
import { useRoute } from "vue-router";

const toast = useToast();
const { t } = useTranslate();
const client = useSanctumClient();

useHead({
    title: `${t.value("email.verify.title")} | JourneyPlanner`,
});

const route = useRoute();
const query = route.query;
console.log(query);

onMounted(async () => {
    try {
        if (query.expires && query.hash && query.signature && query.id) {
            await client("/verify-email", {
                method: "POST",
                params: {
                    expires: query.expires,
                    hash: query.hash,
                    signature: query.signature,
                    user_id: query.id,
                },
                async onResponse({ response }) {
                    console.log(response);

                    if (response.ok) {
                        navigateTo("/dashboard");
                    }
                },
                async onRequestError() {
                    toast.add({
                        severity: "error",
                        summary: t.value("common.toast.error.heading"),
                        detail: t.value("common.error.unknown"),
                        life: 6000,
                    });
                    navigateTo("/login");
                },
            });
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
        <span class="mt-5 text-xl font-medium text-text dark:text-natural-50">
            <T key-name="email.verify.info" />
        </span>
    </div>
</template>
