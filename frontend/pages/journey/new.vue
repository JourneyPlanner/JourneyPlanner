<script setup lang="ts">
import { useTranslate } from "@tolgee/vue";
import { format } from "date-fns";
import { v4 as uuidv4 } from "uuid";
import { useForm } from "vee-validate";
import * as yup from "yup";

const { t } = useTranslate();
const client = useSanctumClient();
const { isAuthenticated } = useSanctumAuth();
const toast = useToast();
const store = useDashboardStore();
const journeyStore = useJourneyStore();
journeyStore.resetJourney();

const cancel = ref("/dashboard");
const journeyInvite = ref(uuidv4());
const journeyInviteLink = ref("");
const loading = ref(false);

const title = t.value("title.journey.create");
useHead({
    title: `${title} | JourneyPlanner`,
});

if (!isAuthenticated.value) {
    if (localStorage.getItem("JP_guest_journey_id") !== null) {
        navigateTo("/journey/" + localStorage.getItem("JP_guest_journey_id"));
    } else {
        cancel.value = "/";
    }
} else {
    journeyInviteLink.value =
        window.location.origin + "/invite/" + journeyInvite.value;
}

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
            .min(1, t.value("form.error.journey.destination"))
            .required(t.value("form.error.journey.destination"))
            .matches(/^(?!\s+$).*/, t.value("form.error.journey.destination"))
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
    loading.value = true;
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
    const invite = journeyInvite.value;
    const mapbox_full_address = values.mapbox?.properties.full_address;
    const mapbox_id = values.mapbox?.properties.mapbox_id;

    const journey = {
        name,
        destination,
        mapbox_full_address: mapbox_full_address,
        mapbox_id: mapbox_id,
        from,
        to,
        invite,
        role: 1,
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
                    life: 3000,
                });
                store.addJourney(journey);
                if (!isAuthenticated.value) {
                    console.log(
                        "new: setting guest journey id",
                        response._data.journey.id,
                    );

                    localStorage.setItem(
                        "JP_guest_journey_id",
                        response._data.journey.id,
                    );
                }
                await navigateTo("/journey/" + response._data.journey.id);
                loading.value = false;
            }
        },
        async onResponseError() {
            toast.add({
                severity: "error",
                summary: t.value("common.toast.error.heading"),
                detail: t.value("common.error.unknown"),
                life: 6000,
            });
            loading.value = false;
        },
    });
});

function copyToClipboard() {
    navigator.clipboard.writeText(journeyInviteLink.value);
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
            <div
                class="z-50 mt-16 flex items-center justify-center px-4 font-nunito"
            >
                <fieldset
                    id="create-journey"
                    class="w-full rounded-2xl border-2 border-calypso-300 bg-calypso-200 bg-opacity-30 px-2 shadow-sm dark:bg-gothic-300 dark:bg-opacity-20 xs:px-5 sm:w-1/4 md:w-1/3"
                >
                    <legend
                        for="create-journey"
                        class="text-center text-2xl font-bold text-text dark:text-natural-50 lg:text-3xl xl:ml-4 xl:px-2 xl:text-left"
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
                            custom-class=".SearchIcon {visibility: hidden;} .Input {height: fit-content; font-weight: 700; padding-right: 0.625rem; padding-top: 0.625rem; padding-bottom: 0.625rem; padding-left: 0.625rem;} .Input::placeholder {font-family: Nunito; font-weight: 400; font-size: 0.875rem; line-height: 1.25rem;}"
                        />
                        <FormCalendar
                            id="journey-range-calendar"
                            name="journeyRange"
                            translation-key="form.input.journey.dates"
                        />
                        <Divider
                            type="solid"
                            class="border-10 border text-calypso-300"
                        />

                        <div v-if="isAuthenticated" class="relative my-2 flex">
                            <input
                                id="journey-invite"
                                v-model="journeyInviteLink"
                                type="text"
                                name="journey-invite"
                                disabled
                                class="text-md peer w-[90%] rounded-lg border-2 border-calypso-300 bg-natural-100 px-2.5 pb-1 pt-4 font-bold text-natural-500 focus:outline-none focus:ring-1 dark:bg-natural-900 dark:text-natural-300"
                                placeholder=" "
                            />
                            <label
                                for="journey-invite"
                                class="absolute left-0 ml-1.5 mt-1 -translate-y-0.5 px-1 text-xs text-calypso-500 transition-transform duration-100 ease-linear peer-placeholder-shown:translate-y-2.5 peer-placeholder-shown:text-sm peer-placeholder-shown:text-natural-400 peer-focus:ml-1.5 peer-focus:-translate-y-0.5 peer-focus:px-1 peer-focus:text-xs peer-focus:text-calypso-500 dark:text-calypso-300"
                            >
                                <T key-name="form.input.journey.invite" />
                            </label>
                            <div class="flex items-center justify-center">
                                <button
                                    type="button"
                                    class="ml-2 flex h-10 w-10 items-center justify-center rounded-full border-2 border-dandelion-300 bg-natural-50 hover:bg-dandelion-200 dark:bg-natural-800 dark:hover:bg-pesto-600"
                                    @click="copyToClipboard"
                                >
                                    <SvgCopy class="w-4" />
                                </button>
                            </div>
                        </div>

                        <div class="mb-5 mt-6 flex justify-between gap-5">
                            <NuxtLink :to="cancel">
                                <button
                                    type="button"
                                    class="rounded-xl border-2 border-mahagony-500 bg-natural-50 px-7 py-1 font-bold text-text hover:bg-mahagony-300 dark:bg-natural-800 dark:text-natural-50 dark:hover:bg-mahagony-500 dark:hover:bg-opacity-30"
                                >
                                    <T key-name="common.button.cancel" />
                                </button>
                            </NuxtLink>

                            <Button
                                :loading="loading"
                                type="submit"
                                :label="t('common.button.create')"
                                :pt="{
                                    root: {
                                        class: 'flex items-center justify-center',
                                    },
                                    label: {
                                        class: 'display-block flex-none font-bold font-nunito',
                                    },
                                }"
                                class="rounded-xl border-2 border-dandelion-300 bg-natural-50 px-7 py-1 font-bold text-text hover:bg-dandelion-200 dark:bg-natural-800 dark:text-natural-50 dark:hover:bg-pesto-600"
                            />
                        </div>
                    </form>
                </fieldset>
            </div>
            <div class="z-10">
                <div
                    class="relative flex flex-row items-end justify-between border-b border-natural-200"
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
