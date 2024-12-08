<script setup lang="ts">
import { useTranslate } from "@tolgee/vue";
import { format } from "date-fns";
import { v4 as uuidv4 } from "uuid";
import { useForm } from "vee-validate";
import * as yup from "yup";

const route = useRoute();
const router = useRouter();
const { t } = useTranslate();
const client = useSanctumClient();
const { isAuthenticated } = useSanctumAuth();
const toast = useToast();
const store = useJourneysStore();
const journeyStore = useJourneyStore();
journeyStore.resetJourney();

const templateID = route.params.id;
const activeIndex = ref(0);
const cancel = ref("/dashboard");
const journeyInvite = ref(uuidv4());
const journeyInviteLink = ref("");
const loading = ref(false);
const page = ref(1);

const title = t.value("title.journey.create");
const { data } = await useAsyncData("journey", () =>
    client(`/api/template/${templateID}`),
);

watch(
    activeIndex,
    () => {
        if (activeIndex.value == null) {
            activeIndex.value = 0;
        }
    },
    { immediate: true },
);

console.log(data);
console.log(data.value.destination);
useHead({
    title: `${title} | JourneyPlanner`,
});

if (!isAuthenticated.value) {
    if (localStorage.getItem("JP_guest_journey_id") !== null) {
        toast.add({
            severity: "info",
            summary: t.value("journey.unlock.create.heading"),
            detail: t.value("journey.unlock.create.detail"),
            life: 2000,
        });
        await navigateTo(
            "/journey/" + localStorage.getItem("JP_guest_journey_id"),
        );
    }
    cancel.value = "/";
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
            .matches(/^(?!\s+$).*/, t.value("form.error.journey.name"))
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
        <div>
            <SvgCloud
                class="invisible absolute left-[22%] top-20 h-20 overflow-hidden object-none md:visible"
            />
            <SvgCloud
                class="invisible absolute right-[25%] top-80 h-[4.5rem] overflow-hidden object-none md:visible"
            />
        </div>
        <div class="absolute left-4 top-4 z-50">
            <NuxtLink :to="cancel">
                <SvgLogoHorizontalBlue class="w-44 lg:w-56" />
            </NuxtLink>
        </div>
        <div class="relative flex flex-col">
            <div
                class="mt-10 flex items-center justify-center px-4 font-nunito sm:mt-8"
            >
                <fieldset
                    v-if="page == 1"
                    id="create-journey"
                    class="w-full rounded-3xl border-2 border-calypso-300 bg-natural-50 px-2 shadow-sm dark:border-calypso-600 dark:bg-background-dark xs:px-5 sm:w-1/4 md:w-2/5"
                >
                    <legend
                        for="create-journey"
                        class="text-center text-2xl font-bold text-text dark:text-natural-50 md:mb-5 lg:text-3xl xl:ml-4 xl:px-2 xl:text-left"
                    >
                        <T key-name="form.header.journey.create" />
                    </legend>
                    <form class="px-1 lg:px-5" @submit="onSubmit">
                        <FormInput
                            id="journey-name"
                            name="journeyName"
                            translation-key="form.input.journey.name"
                            border-color="natural-300"
                        />
                        <input
                            id="journey-destination"
                            name="journeyDestination"
                            disabled
                            class="font peer mb-4 flex w-full items-center justify-center rounded-lg border-2 border-natural-300 bg-natural-100 px-2.5 py-2.5 text-xl text-natural-700 dark:border-natural-800 dark:bg-natural-900 dark:text-natural-300"
                            :value="data.destination"
                        />
                        <FormCalendar
                            id="journey-range-calendar"
                            name="journeyRange"
                            translation-key="form.input.journey.dates"
                        />
                        <Divider
                            v-if="isAuthenticated"
                            type="solid"
                            class="border-10 mt-2 border pt-0 text-calypso-300 dark:text-calypso-400"
                        />

                        <div v-if="isAuthenticated" class="relative my-2 flex">
                            <input
                                id="journey-invite"
                                v-model="journeyInviteLink"
                                type="text"
                                name="journey-invite"
                                disabled
                                class="peer w-[90%] rounded-lg border-2 border-calypso-300 bg-natural-100 px-2.5 pb-1 pt-4 text-base font-medium text-natural-500 focus:outline-none focus:ring-1 dark:border-calypso-400 dark:bg-natural-700 dark:text-natural-300"
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
                        <div class="mb-3 mt-6 flex justify-between gap-5">
                            <button
                                type="button"
                                class="w-36 rounded-xl border-2 border-mahagony-500 bg-natural-50 px-7 py-1 text-text hover:bg-mahagony-300 dark:bg-natural-800 dark:text-natural-50 dark:hover:bg-mahagony-500 dark:hover:bg-opacity-30"
                                @click="router.back()"
                            >
                                <T key-name="common.button.cancel" />
                            </button>

                            <Button
                                :loading="loading"
                                type="button"
                                :label="t('common.button.continue')"
                                :pt="{
                                    root: {
                                        class: 'flex items-center justify-center',
                                    },
                                    label: {
                                        class: 'display-block flex-none font-nunito',
                                    },
                                }"
                                class="w-36 rounded-xl border-2 border-dandelion-300 bg-natural-50 px-7 py-1 text-text hover:bg-dandelion-200 dark:bg-natural-800 dark:text-natural-50 dark:hover:bg-pesto-600"
                                @click="page = 2"
                            />
                        </div>
                    </form>
                </fieldset>
                <fieldset
                    v-else-if="page == 2"
                    id="create-journey"
                    class="w-full rounded-3xl border-2 border-calypso-300 bg-natural-50 px-2 shadow-sm dark:border-calypso-600 dark:bg-background-dark xs:px-5 sm:w-1/4 md:w-2/5"
                >
                    <legend
                        for="create-journey"
                        class="text-center text-2xl font-bold text-text dark:text-natural-50 md:mb-5 lg:text-3xl xl:ml-4 xl:px-2 xl:text-left"
                    >
                        <T key-name="form.header.journey.create" />
                    </legend>
                    <form class="px-1 lg:px-5" @submit="onSubmit">
                        <div>
                            <T key-name="template.choose.option" />
                        </div>
                        <Accordion
                            v-model:active-index="activeIndex"
                            class="font-nunito"
                        >
                            <AccordionTab
                                :header="t('template.shift.common')"
                                :pt="{
                                    root: () => ({
                                        class: [
                                            {
                                                'border-2 border-calypso-300 rounded-lg':
                                                    activeIndex === 0,
                                                'border-b-0': activeIndex === 1,
                                                'border-b-2 border-natural-300 dark:border-natural-300':
                                                    activeIndex === 2,
                                            },
                                        ],
                                    }),
                                    headerTitle: {
                                        class: 'font-semibold text-lg',
                                    },
                                    headerAction: () => ({
                                        class: [
                                            ' font-semibold text-text dark:text-natural-50 py-1.5',
                                            {
                                                'bg-gothic-50 dark:bg-background-dark':
                                                    activeIndex === 0,
                                                'bg-background dark:bg-background-dark':
                                                    activeIndex !== 0,
                                            },
                                        ],
                                    }),
                                    content: {
                                        class: 'bg-gothic-50 dark:bg-background-dark text-text dark:text-natural-50 rounded-lg',
                                    },
                                }"
                            >
                                <div
                                    class="border-t-2 border-calypso-300 pt-3 dark:border-calypso-600"
                                >
                                    <span>
                                        <T
                                            key-name="template.shift.common.description.part.1"
                                        />
                                    </span>
                                    <br />
                                    <br />
                                    <ol class="ml-5 list-disc">
                                        <li>
                                            <span>
                                                <T
                                                    key-name="template.shift.common.description.part.2"
                                                />
                                            </span>
                                        </li>
                                        <li>
                                            <span>
                                                <T
                                                    key-name="template.shift.common.description.part.3"
                                                />
                                            </span>
                                        </li>
                                    </ol>
                                </div>
                            </AccordionTab>
                            <AccordionTab
                                :header="t('template.shift.smart')"
                                :pt="{
                                    root: () => ({
                                        class: [
                                            {
                                                'border-2 border-calypso-300 rounded-lg':
                                                    activeIndex === 1,
                                                'border-b-0': activeIndex === 2,
                                                'border-b-2 border-natural-300 dark:border-natural-300':
                                                    activeIndex === 3 ||
                                                    activeIndex === 0,
                                            },
                                        ],
                                    }),
                                    headerTitle: {
                                        class: 'font-semibold text-lg',
                                    },
                                    headerAction: () => ({
                                        class: [
                                            ' font-semibold text-text dark:text-natural-50 py-1.5',
                                            {
                                                'bg-gothic-50 dark:bg-background-dark':
                                                    activeIndex === 1,
                                                'bg-background dark:bg-background-dark':
                                                    activeIndex !== 1,
                                            },
                                        ],
                                    }),
                                    content: {
                                        class: 'bg-gothic-50 dark:bg-background-dark text-text dark:text-natural-50 rounded-lg',
                                    },
                                }"
                            >
                                <div
                                    class="border-t-2 border-calypso-300 pt-3 dark:border-calypso-600"
                                >
                                    <span>
                                        <T
                                            key-name="template.shift.smart.description.part.1"
                                        />
                                    </span>
                                    <br />
                                    <br />
                                    <ol class="ml-5 list-disc">
                                        <li>
                                            <span>
                                                <T
                                                    key-name="template.shift.smart.description.part.2"
                                                />
                                            </span>
                                        </li>
                                        <li>
                                            <span>
                                                <T
                                                    key-name="template.shift.smart.description.part.3"
                                                />
                                            </span>
                                        </li>
                                    </ol>
                                </div>
                            </AccordionTab>
                            <AccordionTab
                                :header="t('template.shift.activitypool')"
                                :pt="{
                                    root: () => ({
                                        class: [
                                            {
                                                'border-2 border-calypso-300 rounded-lg':
                                                    activeIndex === 2,
                                                'border-b-0': activeIndex === 3,
                                                'border-b-2 border-natural-300 dark:border-natural-300':
                                                    activeIndex === 1 ||
                                                    activeIndex === 0,
                                            },
                                        ],
                                    }),
                                    headerTitle: {
                                        class: 'font-semibold text-lg',
                                    },
                                    headerAction: () => ({
                                        class: [
                                            ' font-semibold text-text dark:text-natural-50 py-1.5',
                                            {
                                                'bg-gothic-50 dark:bg-background-dark':
                                                    activeIndex === 2,
                                                'bg-background dark:bg-background-dark':
                                                    activeIndex !== 2,
                                            },
                                        ],
                                    }),
                                    content: {
                                        class: 'bg-gothic-50 dark:bg-background-dark text-text dark:text-natural-50 rounded-lg',
                                    },
                                }"
                            >
                                <div
                                    class="border-t-2 border-calypso-300 pt-3 dark:border-calypso-600"
                                >
                                    <span>
                                        <T
                                            key-name="template.shift.activitypool.description"
                                        />
                                    </span>
                                </div>
                            </AccordionTab>
                        </Accordion>
                        <div class="mb-3 mt-6 flex justify-between gap-5">
                            <button
                                type="button"
                                class="w-36 rounded-xl border-2 border-natural-400 bg-natural-50 px-7 py-1 text-text hover:bg-natural-200 dark:border-natural-500 dark:bg-natural-900 dark:text-natural-50 dark:hover:bg-natural-950"
                                @click="console.log(activeIndex)"
                            >
                                <T key-name="common.back" />
                            </button>

                            <Button
                                :loading="loading"
                                type="button"
                                :label="t('common.button.create')"
                                :pt="{
                                    root: {
                                        class: 'flex items-center justify-center',
                                    },
                                    label: {
                                        class: 'display-block flex-none font-nunito',
                                    },
                                }"
                                class="w-36 rounded-xl border-2 border-dandelion-300 bg-natural-50 px-7 py-1 text-text hover:bg-dandelion-200 dark:bg-natural-800 dark:text-natural-50 dark:hover:bg-pesto-600"
                            />
                        </div>
                    </form>
                </fieldset>
            </div>
        </div>
        <SvgPeopleBackpackMap class="absolute bottom-0 hidden h-44 lg:block" />
        <SvgWomanSuitcaseLeft
            class="absolute bottom-0 hidden h-44 sm:block lg:right-44"
        />
        <SvgWomanSuitcaseRight
            class="absolute bottom-0 right-0 hidden h-44 sm:block"
        />
    </div>
</template>
