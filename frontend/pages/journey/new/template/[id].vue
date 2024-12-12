<script setup lang="ts">
import { UTCDate } from "@date-fns/utc";
import { useTranslate } from "@tolgee/vue";
import { differenceInDays, format } from "date-fns";
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

const templateID = route.params.id;
const namePrefill = (route.query.name as string) || "";
const datePrefill = (route.query.date as string[]) || null;

const template = ref({
    name: "tolle Reise",
    destination: "East Melissa",
    from: "2025-09-03T00:00:00.000000Z",
    to: "2025-09-12T00:00:00.000000Z",
    user: [{ username: "Roman Nebs" }],
});

const activeIndex = ref(0);
const { value: name, setValue } = useField("journeyName");
setValue(namePrefill);
const journeyRange = ref(
    datePrefill ? datePrefill.map((date) => new Date(date)) : null,
);
const cancel = ref("/dashboard");
const journeyInvite = ref(uuidv4());
const journeyInviteLink = ref("");
const loading = ref(false);
const page = ref(1);

const tooShort = ref(false);
const isInfoDialogVisible = ref(false);
const isFocused = ref(false);

const title = t.value("title.journey.create");

const days = ref(
    differenceInDays(
        new UTCDate(template.value.to),
        new UTCDate(template.value.from),
    ) + 1,
);
if (datePrefill != null) {
    const newDuration =
        differenceInDays(
            new UTCDate(datePrefill[1]),
            new UTCDate(datePrefill[0]),
        ) + 1;
    if (days.value > newDuration) {
        tooShort.value = true;
    } else {
        tooShort.value = false;
    }
}

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

// Copy to clipboard
function copyToClipboard() {
    navigator.clipboard.writeText(journeyInviteLink.value);
    toast.add({
        severity: "info",
        summary: t.value("common.toast.info.heading"),
        detail: t.value("common.invite.toast.info"),
        life: 2000,
    });
}

// Validation Schema
const { errors, handleSubmit } = useForm({
    validationSchema: yup.object({
        journeyRange: yup
            .array()
            .of(yup.date().required(t.value("form.error.journey.date.invalid")))
            .min(2, t.value("form.error.journey.date.min")),
        journeyName: yup
            .string()
            .trim()
            .required(t.value("form.error.journey.name"))
            .matches(/^(?!\s+$).*/, t.value("form.error.journey.name.invalid")),
    }),
});

// Form Submission
const validateData = handleSubmit(onSuccess);

async function onSuccess() {
    page.value = 2;
}

// Start submit logic
async function startSubmit() {
    loading.value = true;
    toast.add({
        severity: "info",
        summary: t.value("common.toast.info.heading"),
        detail: t.value("form.journey.toast.info"),
        life: 6000,
    });

    const journeyName = name.value;
    const destination = template.value.destination;
    const from = journeyRange.value
        ? format(journeyRange.value[0], "yyyy-MM-dd")
        : null;
    const to = journeyRange.value
        ? format(journeyRange.value[1], "yyyy-MM-dd")
        : null;
    const invite = journeyInvite.value;

    let calendarInsertMode;
    switch (activeIndex.value) {
        case 0:
            calendarInsertMode = "direct";
            break;
        case 1:
            calendarInsertMode = "smart";
            break;
        case 2:
            calendarInsertMode = "pool";
            break;
    }

    const journey = {
        name: journeyName,
        destination,
        from,
        to,
        invite,
        role: 1,
        template_id: templateID,
        calendar_activity_insert_mode: calendarInsertMode,
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
}

const handleFocus = () => {
    isFocused.value = true;
};

const handleBlur = () => {
    isFocused.value = false;
};

const backToFirstSite = () => {
    page.value = 1;
};

function changeDuration() {
    if (journeyRange.value) {
        const newDuration =
            differenceInDays(
                new UTCDate(journeyRange.value[1]),
                new UTCDate(journeyRange.value[0]),
            ) + 1;
        if (days.value > newDuration) {
            tooShort.value = true;
        } else {
            tooShort.value = false;
        }
        console.log(newDuration);
    }
}
</script>

<template>
    <div class="overflow-hidden">
        <div>
            <SvgCloud
                class="absolute left-[23rem] top-20 hidden h-20 overflow-hidden object-none md:visible"
            />
            <SvgCloud
                class="absolute right-[26rem] top-80 hidden h-[4.5rem] overflow-hidden object-none md:visible"
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
                    <div class="grid w-[96%] grid-cols-2">
                        <div
                            v-tooltip.bottom="{
                                value:
                                    t('template.using') +
                                    ':  &quot;' +
                                    template.name +
                                    '&quot; ' +
                                    t('template.by') +
                                    ' ' +
                                    template.user[0].username,
                                pt: { root: 'font-nunito' },
                            }"
                            class="col-span-2 mb-4 overflow-hidden overflow-ellipsis text-nowrap pl-1 text-sm text-natural-700 dark:text-natural-200 md:-mt-5 lg:pl-6"
                        >
                            <T key-name="template.using" />
                            "{{ template.name }}"
                            <T key-name="template.by" />
                            {{
                                template.user && template.user[0]
                                    ? template.user[0].username
                                    : ""
                            }}
                        </div>
                    </div>
                    <form class="px-1 lg:px-5" @submit="validateData">
                        <div class="relative mb-4">
                            <Field
                                id="journeyName"
                                v-model="name"
                                type="text"
                                name="journeyName"
                                placeholder=" "
                                class="placeholder:text-transparent dark: peer w-full rounded-lg border-2 border-natural-300 bg-natural-50 px-2.5 pb-1 pt-4 text-base font-bold text-text hover:border-calypso-400 focus:border-calypso-400 focus:outline-none dark:border-natural-800 dark:bg-natural-700 dark:text-natural-50 dark:hover:border-calypso-400 dark:focus:border-calypso-400"
                            />
                            <label
                                for="journey-name"
                                class="absolute left-0 ml-1.5 mt-1 -translate-y-0.5 px-1 text-xs text-natural-400 transition-transform duration-100 ease-linear peer-placeholder-shown:translate-y-2.5 peer-placeholder-shown:text-sm peer-placeholder-shown:text-natural-600 peer-focus:ml-1.5 peer-focus:-translate-y-0.5 peer-focus:px-1 peer-focus:text-xs peer-focus:text-calypso-600 dark:peer-placeholder-shown:text-natural-200"
                            >
                                <T key-name="form.input.journey.name" />
                            </label>
                            <div
                                class="w-full text-left leading-3"
                                :class="
                                    errors.journeyName
                                        ? '-mb-1.5 mt-0.5 block'
                                        : 'hidden'
                                "
                            >
                                <span
                                    class="ml-0.5 text-xs text-mahagony-600 dark:font-medium dark:text-mahagony-300"
                                    >{{ errors.journeyName }}</span
                                >
                            </div>
                        </div>
                        <input
                            id="journey-destination"
                            name="journeyDestination"
                            disabled
                            class="font text-medium peer mb-4 flex w-full items-center justify-center rounded-lg border-2 border-natural-300 bg-natural-100 px-2.5 py-2.5 text-natural-700 dark:border-natural-800 dark:bg-natural-900 dark:text-natural-300"
                            :value="template.destination"
                        />
                        <div class="relative">
                            <Field v-slot="{ field }" name="journeyRange">
                                <Calendar
                                    id="journey-range-calendar"
                                    v-model="journeyRange"
                                    v-bind="field"
                                    name="journeyRange"
                                    selection-mode="range"
                                    :manual-input="true"
                                    :number-of-months="1"
                                    show-other-months
                                    select-other-months
                                    hide-on-range-selection
                                    date-format="dd/mm/yy"
                                    panel-class="bg-natural-50 dark:bg-natural-900 dark:text-natural-50"
                                    input-class="border-natural-300 hover:border-calypso-400 dark:hover:border-calypso-400s block rounded-lg px-2.5 pt-4 pb-1  w-full text-md text-text dark:text-natural-50 font-bold bg-natural-50 border-2 dark:border-natural-800 dark:bg-natural-700 dark:text-natural-50 focus:outline-none focus:border-calypso-400 dark:focus:border-calypso-400 dark:hover:border-calypso-400"
                                    :pt="{
                                        root: { class: 'lg:w-3/5' },
                                        panel: {
                                            class: 'text-text font-nunito z-50',
                                        },
                                        header: {
                                            class: 'flex justify-between border-b bg-natural-50 dark:bg-natural-900 dark:text-natural-50',
                                        },
                                        title: {
                                            class: 'text-text dark:text-natural-50 flex gap-1 font-nunito',
                                        },
                                        dayLabel: { class: 'text-calypso-400' },
                                        datepickerMask: {
                                            class: 'text-text bg-natural-900',
                                        },
                                    }"
                                    @focus="handleFocus"
                                    @hide="handleBlur"
                                    @input="handleFocus"
                                    @date-select="changeDuration()"
                                />
                            </Field>
                            <br />
                            <div class="ml-0.5 mt-1 h-3 leading-3">
                                <span
                                    class="text-xs text-mahagony-600 dark:font-medium dark:text-mahagony-300"
                                    >{{ errors.journeyRange }}</span
                                >
                            </div>
                            <label
                                for="journey-range-calendar"
                                class="pointer-events-none absolute left-0 top-0 overflow-hidden whitespace-nowrap py-4 pl-2.5 text-sm transition-all duration-300"
                                :class="{
                                    'text-natural-600': !isFocused,
                                    'dark:text-natural-200': !isFocused,
                                    'text-calypso-600': isFocused,
                                    '-translate-x-6 -translate-y-4 scale-75':
                                        isFocused || journeyRange,
                                    'translate-y-0 scale-100':
                                        !isFocused && !journeyRange,
                                }"
                            >
                                <T key-name="form.input.journey.dates" />
                            </label>
                        </div>
                        <div
                            class="flex items-center text-xs text-natural-700 dark:text-natural-400"
                            :class="errors.journeyRange ? 'mt-2' : '-mt-3'"
                        >
                            <T key-name="template.recommended.duration" />
                            {{ days }}
                            <T v-if="days > 1" key-name="template.days" />
                            <T v-else-if="days == 1" key-name="template.day" />
                            <button
                                type="button"
                                @click="isInfoDialogVisible = true"
                            >
                                <span
                                    class="pi pi-info-circle pl-1"
                                    :class="
                                        tooShort
                                            ? 'text-dandelion-400 dark:text-pesto-600'
                                            : 'text-natural-500 hover:text-natural-900 dark:text-natural-400 dark:hover:text-natural-100'
                                    "
                                ></span>
                            </button>
                        </div>
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
                                class="peer w-[90%] rounded-lg border-2 border-natural-300 bg-natural-100 px-2.5 pb-1 pt-4 text-base font-medium text-natural-600 focus:outline-none focus:ring-1 dark:border-natural-800 dark:bg-natural-900 dark:text-natural-300"
                                placeholder=" "
                            />
                            <label
                                for="journey-invite"
                                class="absolute left-0 ml-1.5 mt-1 -translate-y-0.5 px-1 text-xs text-natural-600 transition-transform duration-100 ease-linear peer-placeholder-shown:translate-y-2.5 peer-placeholder-shown:text-sm peer-placeholder-shown:text-natural-400 peer-focus:ml-1.5 peer-focus:-translate-y-0.5 peer-focus:px-1 peer-focus:text-xs peer-focus:text-calypso-500 dark:text-natural-300"
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
                                type="submit"
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
                    <div class="w-80 sm:w-full">
                        <div
                            v-tooltip.bottom="{
                                value:
                                    t('template.using') +
                                    ':  &quot;' +
                                    template.name +
                                    '&quot; ' +
                                    t('template.by') +
                                    ' ' +
                                    template.user[0].username,
                                pt: { root: 'font-nunito' },
                            }"
                            class="col-span-2 mb-4 overflow-hidden overflow-ellipsis text-nowrap pl-1 text-sm text-natural-700 dark:text-natural-200 md:-mt-5 lg:pl-6"
                        >
                            <T key-name="template.using" />
                            "{{ template.name }}"
                            <T key-name="template.by" />
                            {{ template.user[0].username }}
                        </div>
                    </div>
                    <form class="px-1 lg:px-5">
                        <div class="text-text dark:text-natural-50">
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
                                                'border-2 border-calypso-300 rounded-lg dark:border-calypso-600':
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
                                                'bg-gothic-50 dark:bg-gothic-900':
                                                    activeIndex === 0,
                                                'bg-background dark:bg-background-dark':
                                                    activeIndex !== 0,
                                            },
                                        ],
                                    }),
                                    content: () => ({
                                        class: [
                                            'text-text dark:text-natural-50 rounded-lg',
                                            {
                                                'bg-gothic-50 dark:bg-gothic-900':
                                                    activeIndex === 0,
                                                'bg-background dark:bg-background-dark':
                                                    activeIndex !== 0,
                                            },
                                        ],
                                    }),
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
                                                'border-2 border-calypso-300 rounded-lg dark:border-calypso-600 dark:bg-gothic-900':
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
                                                'bg-gothic-50 dark:bg-gothic-900':
                                                    activeIndex === 1,
                                                'bg-background dark:bg-background-dark':
                                                    activeIndex !== 1,
                                            },
                                        ],
                                    }),
                                    content: () => ({
                                        class: [
                                            'text-text dark:text-natural-50 rounded-lg',
                                            {
                                                'bg-gothic-50 dark:bg-gothic-900':
                                                    activeIndex === 1,
                                                'bg-background dark:bg-background-dark':
                                                    activeIndex !== 1,
                                            },
                                        ],
                                    }),
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
                                                'border-2 border-calypso-300 rounded-lg dark:border-calypso-600':
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
                                                'bg-gothic-50 dark:bg-gothic-900':
                                                    activeIndex === 2,
                                                'bg-background dark:bg-background-dark':
                                                    activeIndex !== 2,
                                            },
                                        ],
                                    }),
                                    content: () => ({
                                        class: [
                                            'text-text dark:text-natural-50 rounded-lg',
                                            {
                                                'bg-gothic-50 dark:bg-gothic-900':
                                                    activeIndex === 2,
                                                'bg-background dark:bg-background-dark':
                                                    activeIndex !== 2,
                                            },
                                        ],
                                    }),
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
                                @click="backToFirstSite"
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
                                @click="startSubmit"
                            />
                        </div>
                    </form>
                </fieldset>
            </div>
        </div>
        <SvgPeopleBackpackMap class="absolute bottom-14 hidden h-44 lg:block" />
        <SvgWomanSuitcaseLeft
            class="absolute bottom-14 block h-28 md:h-44 lg:right-44"
        />
        <SvgWomanSuitcaseRight
            class="absolute bottom-14 right-0 block h-28 md:h-44"
        />
        <Divider
            type="solid"
            class="border-10 absolute bottom-9 mt-2 border pt-0 text-natural-100 dark:text-natural-700"
        />
        <div id="dialogs">
            <TemplateDialogsCreationDurationInfo
                :is-visible="isInfoDialogVisible"
                @close="isInfoDialogVisible = false"
            />
        </div>
    </div>
</template>
