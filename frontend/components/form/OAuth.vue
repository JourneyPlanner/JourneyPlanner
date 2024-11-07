<template>
    <div class="flex w-full items-center">
        <span class="h-0.5 w-1/2 bg-calypso-300 dark:bg-calypso-400" />
        <span class="mx-2 font-bold text-text dark:text-natural-50"
            ><T key-name="common.or"
        /></span>
        <span class="h-0.5 w-1/2 bg-calypso-300 dark:bg-calypso-400" />
    </div>
    <div class="flex flex-col justify-center gap-y-2">
        <FormGoogleButton />
        <FormMicrosoftButton />
    </div>
</template>
