<script setup lang="ts">
import { useTranslate } from "@tolgee/vue";
import { format } from "date-fns";
import Toast from "primevue/toast";
import { v4 as uuidv4 } from "uuid";
import { useForm } from "vee-validate";
import * as yup from "yup";

const { t } = useTranslate();
const client = useSanctumClient();
const toast = useToast();

const journeyInvite = uuidv4();
const journeyInviteLink = window.location.origin + "/invite/" + journeyInvite;

const title = t.value("title.journey.create");
useHead({
    title: `${title} | JourneyPlanner`,
});

definePageMeta({
    middleware: ["sanctum:auth"],
});

/**
 * form validation
 * when submitting form, fields are checked for validation
 */
const { handleSubmit } = useForm({
    validationSchema: yup.object({
        journeyName: yup
            .string()
            .required(t.value("form.error.journey.name"))
            .label(t.value("form.input.journey.name")),
        journeyDestination: yup
            .string()
            .required(t.value("form.error.journey.destination"))
            .label(t.value("form.input.journey.destination")),
        journeyRange: yup
            .array()
            .of(
                yup
                    .date()
                    .required(t.value("form.error.journey.dates"))
                    .label(t.value("form.input.journey.dates")),
            )
            .required(t.value("form.error.journey.dates"))
            .label(t.value("form.input.journey.dates")),
    }),
});

/**
 * form submit
 * when submitting the form, values are checked for validation with handleSubmit
 * and then a journey object is created and sent to the backend
 */
const onSubmit = handleSubmit(async (values) => {
    toast.add({
        severity: "info",
        summary: t.value("common.toast.info.heading"),
        detail: t.value("form.journey.toast.info"),
        life: 6000,
    });

    const name = values.journeyName;
    const destination = values.journeyDestination;
    const from = format(values.journeyRange[0], "yyyy-MM-dd");
    const to = format(values.journeyRange[1], "yyyy-MM-dd");
    const invite = journeyInvite;

    const journey = {
        name,
        destination,
        mapbox_full_address: values.mapbox?.properties?.full_address,
        mapbox_id: values.mapbox?.properties?.mapbox_id,
        from,
        to,
        invite,
    };

    await client("/api/journey", {
        method: "POST",
        body: journey,
        async onResponse({ response }) {
            if (response.ok) {
                toast.add({
                    severity: "success",
                    summary: t.value("form.journey.toast.success.heading"),
                    detail: t.value("form.journey.toast.success"),
                    life: 6000,
                });
                await navigateTo("/dashboard");
            }
        },
        async onResponseError() {
            toast.add({
                severity: "error",
                summary: t.value("common.toast.error.heading"),
                detail: t.value("common.error.unknown"),
                life: 6000,
            });
        },
    });
});

function copyToClipboard() {
    navigator.clipboard.writeText(journeyInviteLink);
    toast.add({
        severity: "info",
        summary: t.value("common.toast.info.heading"),
        detail: t.value("common.invite.toast.info"),
        life: 2000,
    });
}
</script>

<template>
    <div>
        <div class="z-10 flex min-h-screen flex-col justify-between">
            <Toast class="w-3/4 sm:w-auto" />
            <div
                class="z-50 mt-16 flex items-center justify-center font-nunito"
            >
                <fieldset
                    id="create-journey"
                    class="w-full rounded-2xl border-2 border-border bg-surface px-5 shadow-sm dark:bg-surface-dark sm:w-1/4 md:w-1/3"
                >
                    <legend
                        for="create-journey"
                        class="ml-4 px-2 text-center text-2xl font-bold text-text dark:text-white lg:text-left lg:text-3xl"
                    >
                        <T key-name="form.header.journey.create" />
                    </legend>
                    <form class="px-1 lg:px-5" @submit="onSubmit">
                        <FormInput
                            id="journey-name"
                            name="journeyName"
                            translation-key="form.input.journey.name"
                        />
                        <FormAddressInput
                            id="journey-destination"
                            name="journeyDestination"
                            :placeholder="t('form.input.journey.destination')"
                            class="relative mb-5"
                            :translation-key="
                                t('form.input.journey.destination')
                            "
                            bg-light-key="surface"
                            bg-dark-key="surface-dark"
                            custom-class=".SearchIcon {visibility: hidden;} .Input {height: fit-content; font-weight: 700; padding-right: 0.625rem; padding-top: 0.625rem; padding-bottom: 0.625rem; padding-left: 0.625rem;} .Input::placeholder {font-family: Nunito; font-weight: 400; font-size: 0.875rem; line-height: 1.25rem;}"
                        />
                        <FormCalendar
                            id="journey-range-calendar"
                            name="journeyRange"
                            translation-key="form.input.journey.dates"
                        />
                        <Divider
                            type="solid"
                            class="border-10 border text-input-label"
                        />

                        <div class="relative my-2 flex">
                            <input
                                id="journey-invite"
                                v-model="journeyInviteLink"
                                type="text"
                                name="journey-invite"
                                disabled
                                class="text-md peer w-[90%] rounded-lg border-2 border-border bg-input-disabled px-2.5 pb-1 pt-4 font-bold text-text-disabled focus:outline-none focus:ring-1 dark:bg-input-disabled-dark-grey dark:text-input-disabled-dark-gray"
                                placeholder=" "
                            />
                            <label
                                for="journey-invite"
                                class="absolute left-0 ml-1.5 mt-1 -translate-y-0.5 px-1 text-xs text-link transition-transform duration-100 ease-linear peer-placeholder-shown:translate-y-2.5 peer-placeholder-shown:text-sm peer-placeholder-shown:text-input-placeholder peer-focus:ml-1.5 peer-focus:-translate-y-0.5 peer-focus:px-1 peer-focus:text-xs peer-focus:text-input-label dark:text-border"
                            >
                                <T key-name="form.input.journey.invite" />
                            </label>
                            <div class="flex items-center justify-center">
                                <button
                                    type="button"
                                    class="ml-2 flex h-10 w-10 items-center justify-center rounded-full border-2 border-cta-border bg-white hover:bg-cta-bg dark:bg-input-dark dark:hover:bg-cta-bg-dark"
                                    @click="copyToClipboard"
                                >
                                    <SvgCopy class="w-4" />
                                </button>
                            </div>
                        </div>

                        <div class="mb-5 mt-6 flex justify-between gap-5">
                            <NuxtLink to="/dashboard">
                                <button
                                    type="button"
                                    class="rounded-xl border-2 border-cancel-border bg-input px-7 py-1 font-bold text-text hover:bg-cancel-bg dark:bg-input-dark dark:text-white dark:hover:bg-cancel-bg-dark"
                                >
                                    <T key-name="common.button.cancel" />
                                </button>
                            </NuxtLink>

                            <button
                                type="submit"
                                class="rounded-xl border-2 border-cta-border bg-input px-7 py-1 font-bold text-text hover:bg-cta-bg dark:bg-input-dark dark:text-white dark:hover:bg-cta-bg-dark"
                            >
                                <T key-name="common.button.create" />
                            </button>
                        </div>
                    </form>
                </fieldset>
            </div>
            <div class="z-10">
                <div
                    class="relative flex flex-row items-end justify-between border-b border-border-grey"
                >
                    <SvgPeopleBackpackMap class="hidden h-full lg:flex" />
                    <div
                        class="mt-2 flex h-full w-full flex-row items-end justify-between sm:mt-0 lg:absolute lg:inset-0 lg:justify-end"
                    >
                        <SvgWomanSuitcaseLeft />
                        <SvgWomanSuitcaseRight class="ml-10 mr-5" />
                    </div>
                </div>
            </div>
        </div>
        <div class="z-10">
            <SvgCloud
                class="invisible absolute left-[28%] top-72 z-0 h-14 overflow-hidden object-none md:visible"
            />
            <SvgCloud
                class="invisible absolute right-[20%] top-36 z-0 h-16 overflow-hidden object-none md:visible"
            />
        </div>
    </div>
</template>
