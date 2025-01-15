<script setup lang="ts">
import { useTranslate } from "@tolgee/vue";
import { ref } from "vue";
import { useRoute } from "vue-router";

//const toast = useToast();
const { t } = useTranslate();
//const client = useSanctumClient();

useHead({
    title: `${t.value("password.reset.title")} | JourneyPlanner`,
});

const route = useRoute();
const loading = ref(true);
const isConfirmEmailDialogVisible = ref<boolean>(false);
const user_id = ref<string>(route.query.user_id as string);
const isUpdating = ref<boolean>(false);

/*
 * reset address by calling the API
 */
async function resetPassword() {
    const query = route.query;
    console.log(query);

    /*try {
        
        if (query.expires && query.hash && query.signature && query.user_id) {
            isUpdating.value = false;
            await client(`/email/verify/${query.user_id}/${query.hash}`, {
                method: "POST",
                params: {
                    expires: query.expires,
                    signature: query.signature,
                },
                async onResponse({ response }) {
                    if (response.ok) {
                        toast.add({
                            severity: "success",
                            summary: t.value("email.verify.success.heading"),
                            detail: t.value("email.verify.success.detail"),
                            life: 6000,
                        });
                        navigateTo("/dashboard");
                    } else {
                        throw new Error();
                    }
                },
            });
        } else if (query.token && query.expires && query.signature) {
            isUpdating.value = true;
            await client(`/email/update/verify/${query.token}`, {
                method: "POST",
                params: {
                    expires: query.expires,
                    signature: query.signature,
                },
                async onResponse({ response }) {
                    if (response.ok) {
                        toast.add({
                            severity: "success",
                            summary: t.value("email.verify.success.heading"),
                            detail: t.value("email.verify.success.detail"),
                            life: 6000,
                        });
                        navigateTo("/dashboard");
                    } else {
                        throw new Error();
                    }
                },
            });
        } else {
            toast.add({
                severity: "error",
                summary: t.value("email.verify.error.parameters.heading"),
                detail: t.value("email.verify.error.parameters.detail"),
                life: 6000,
            });
            loading.value = false;
        }
    } catch {
        toast.add({
            severity: "error",
            summary: t.value("email.verify.error.invalid.heading"),
            detail: t.value("email.verify.error.invalid.detail"),
            life: 6000,
        });
        loading.value = false;
    }
        */
}

resetPassword();

function resend() {
    isConfirmEmailDialogVisible.value = true;
}
</script>

<template>
    <div class="mt-32 flex flex-col items-center justify-center">
        <div class="absolute left-4 top-4 z-50">
            <NuxtLink to="/" class="z-50">
                <SvgLogoHorizontalBlue class="w-44 lg:w-52" />
            </NuxtLink>
        </div>
        <SvgLogin class="w-72" />
        <ProgressSpinner v-if="loading" class="mt-2" />
        <div
            class="mt-5 max-w-80 text-center text-xl font-medium text-text dark:text-natural-50"
        >
            <span v-if="loading">
                <T key-name="email.verify.info" />
            </span>
            <div v-else class="flex flex-col items-center gap-y-2">
                <span>
                    <T key-name="email.verify.error" />
                </span>
                <button
                    type="button"
                    class="mb-2 rounded-xl border-2 border-dandelion-300 bg-natural-50 px-5 py-1 font-nunito text-lg hover:bg-dandelion-200 disabled:cursor-not-allowed disabled:border-dandelion-200 disabled:text-natural-500 disabled:hover:bg-natural-50 dark:bg-natural-800 dark:text-natural-50 dark:hover:bg-pesto-600 disabled:dark:hover:bg-natural-800"
                    @click="resend()"
                >
                    <T key-name="form.register.button.email.resend" />
                </button>
            </div>
        </div>
        <div id="dialogs">
            <MailVerifyDialog
                :is-confirm-email-dialog-visible="isConfirmEmailDialogVisible"
                :do-resend="true"
                :is-updating="isUpdating"
                :user-id="user_id"
                @close="isConfirmEmailDialogVisible = false"
            />
        </div>
    </div>
</template>
