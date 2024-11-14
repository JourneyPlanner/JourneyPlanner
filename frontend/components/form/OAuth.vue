<template>
    <div class="flex w-full items-center px-10">
        <span class="h-[1.6px] w-2/3 bg-calypso-300 dark:bg-calypso-400" />
        <span class="mx-2 text-lg font-bold text-text dark:text-natural-50"
            ><T key-name="common.or"
        /></span>
        <span class="h-[1.6px] w-2/3 bg-calypso-300 dark:bg-calypso-400" />
    </div>
    <div class="flex flex-col justify-center gap-y-3">
        <FormGoogleButton />
        <FormMicrosoftButton />
    </div>
</template>
