<script setup lang="ts">
const client = useSanctumClient();

async function loginWithMicrosoft() {
    const response = await client("/auth/redirect/microsoft");
    navigateTo(response.url, {
        external: true,
    });
}
</script>

<template>
    <div class="flex w-full justify-center">
        <button
            class="flex w-[290px] items-center justify-center gap-x-1 rounded-[4px] bg-[#ffffff] font-sans font-semibold text-[#5e5e5e] hover:bg-[#fafafa] dark:bg-[#2f2f2f] dark:text-[#ffffff] dark:hover:bg-[#3c3c3c]"
            @click="loginWithMicrosoft"
        >
            <SvgMicrosoft class="h-10 w-10" />
            <T key-name="form.button.login.microsoft" />
        </button>
    </div>
</template>
