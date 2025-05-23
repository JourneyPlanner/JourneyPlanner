<script setup lang="ts">
import { T, useTranslate } from "@tolgee/vue";
import { add, format } from "date-fns";
import * as yup from "yup";

const { t } = useTranslate();
const client = useSanctumClient();
const toast = useToast();

const props = defineProps({
    visible: { type: Boolean, required: true },
    id: { type: String, required: true },
    activityId: { type: String, default: "" },
    address: { type: String, default: "" },
    onlyShow: { type: Boolean, default: false },
    cost: { type: String, default: "" },
    createdAt: { type: String, default: "" },
    description: { type: String, default: "" },
    email: { type: String, default: "" },
    estimatedDuration: { type: String, default: "" },
    journeyId: { type: String, default: "" },
    latitude: { type: String, default: "" },
    longitude: { type: String, default: "" },
    link: { type: String, default: "" },
    mapboxId: { type: String, default: "" },
    name: { type: String, default: "" },
    openingHours: { type: String, default: "" },
    phone: { type: String, default: "" },
    updatedAt: { type: String, default: "" },
    update: { type: Boolean, default: false },
    calendarActivity: { type: Object, default: null },
    calendarClicked: { type: Boolean, default: false },
    create: { type: Boolean, default: false },
    createAddress: { type: Boolean, default: false },
    journeyStart: { type: Date, required: false, default: new Date() },
    journeyEnd: { type: Date, required: false, default: new Date() },
    propRepeatType: { type: String, default: " " },
    repeatIntervalPrefill: { type: Number, default: 1 },
    repeatOnPrefill: { type: Array, default: () => [] },
    repeatEndOccurencesPrefill: { type: Number, default: 1 },
    repeatIntervalUnitPrefill: { type: String, default: "" },
    repeatEndDatePrefill: { type: String, default: "" },
    isSharedSite: {
        type: Boolean,
        default: false,
    },
});
const noSingle = ref(false);
const onlyShowRef = ref(props.onlyShow);
const updateRef = ref(props.update);
const calendarClickedRef = ref(props.calendarClicked);
const repeatType = ref();
const repeatInterval = ref();
const repeatIntervalUnit = ref();
const repeatOn = ref();
const repeatEndDate = ref();
const repeatEndOccurences = ref();
const value = ref();

watch(
    () => props.onlyShow,
    (value) => {
        onlyShowRef.value = value;
    },
);

watch(
    () => props.update,
    (value) => {
        updateRef.value = value;
    },
);

watch(
    () => props.calendarClicked,
    (value) => {
        calendarClickedRef.value = value;
    },
);

const emit = defineEmits([
    "close",
    "deleteActivity",
    "removeFromCalendar",
    "editCalendarActivity",
    "calendarMoved",
]);

const isVisible = ref(props.visible);
const isRecurringActivityEditVisible = ref(false);
const loadingSave = ref(false);
const activeIndex = ref(0);
const confirm = useConfirm();

const store = useJourneyStore();
const activityStore = useActivityStore();
const to = new Date(store.getToDate());
const from = new Date(store.getFromDate());

const selectedDate = props.calendarActivity
    ? ref(props.calendarActivity.start)
    : ref();
const timeDisabled = ref(true);

watch([() => props.calendarActivity], ([newPrefill]) => {
    selectedDate.value = new Date(newPrefill.start);
    timeDisabled.value = false;
});

watch(
    () => props.visible,
    (value) => {
        isVisible.value = value;
    },
);

interface ActivityForm {
    name: string;
    duration: string;
    mapbox: Feature;
    address: string;
    costs: string;
    description: string;
    link: string;
    email: string;
    phone: string;
    open: string;
    date: Date;
    time: string;
    repeatType: RepeatType;
    editType: string;
}

type ActivityFormErrors = Partial<Record<keyof ActivityForm, string>>;

const confirmDelete = () => {
    confirm.require({
        header: t.value("activity.delete.header"),
        message: t.value("activity.delete.confirm"),
        group: "journey",
        icon: "pi pi-exclamation-triangle",
        rejectClass: "hover:underline",
        acceptClass:
            "text-mahagony-500 dark:text-mahagony-400 hover:underline font-bold",
        rejectLabel: t.value("common.button.cancel"),
        acceptLabel: t.value("journey.delete"),

        accept: () => {
            toast.add({
                severity: "info",
                summary: t.value("journey.delete"),
                detail: t.value("journey.delete.detail"),
                life: 3000,
            });
            emit("deleteActivity");
            close();
        },
    });
};

const confirmRemoveFromCalendar = () => {
    confirm.require({
        header: t.value("activity.remove.header"),
        message: t.value("activity.remove.confirm"),
        group: "journey",
        icon: "pi pi-exclamation-triangle",
        rejectClass: "hover:underline",
        acceptClass:
            "text-mahagony-500 dark:text-mahagony-400 hover:underline font-bold",
        rejectLabel: t.value("common.button.cancel"),
        acceptLabel: t.value("activity.remove"),

        accept: () => {
            toast.add({
                severity: "info",
                summary: t.value("activity.remove"),
                detail: t.value("activity.remove.detail"),
                life: 3000,
            });
            emit("removeFromCalendar");
            close();
        },
    });
};

const validationSchema = yup.object({
    name: yup.string().required(t.value("form.input.required")),
    duration: yup
        .string()
        .test(
            "not-00-00",
            t.value("form.input.activity.duration.error"),
            (value) => {
                const time = new Date(value || "");
                return time.getHours() + time.getMinutes() !== 0;
            },
        )
        .required(t.value("form.input.required")),
    address: yup.string().nullable(),
    costs: yup.string().nullable(),
    description: yup.string().nullable(),
    link: yup.string().url(t.value("form.input.link.error")).nullable(),
    email: yup.string().email(t.value("form.input.email.error")).nullable(),
    phone: yup.string().nullable(),
    open: yup.string().nullable(),
    date: yup.string().nullable(),
    time: yup.string().nullable(),
    repeatType: yup.object().nullable(),
    dateAndTime: yup.bool().when(["time", "date"], {
        is: (time: Date, date: Date) => (!date && !time) || (!!date && !!time),
        then: () => yup.bool().nullable(),
        otherwise: () =>
            yup.bool().required(t.value("form.input.activity.custom.error")),
    }),
    RepeatAndDate: yup
        .bool()
        .test(
            "RepeatAndDate",
            t.value("form.input.activity.custom.error"),
            function () {
                const { repeatType, time, date } = this.parent;
                if (
                    !repeatType ||
                    repeatType.name === t.value("activity.repeat.not")
                ) {
                    return true;
                }
                if (!!repeatType && !!date && !!time) {
                    return true;
                }
                if (!time || !date) {
                    return this.createError({
                        path: "dateAndTime",
                        message: t.value(
                            "form.input.activity.repeat.custom.error",
                        ),
                    });
                }
                return false;
            },
        ),
});

const { handleSubmit } = useForm<ActivityForm>({
    validationSchema: validationSchema,
});

const onSubmit = handleSubmit(onSuccess, onInvalidSubmit);
async function onSuccess(values: ActivityForm) {
    if (props.calendarClicked) {
        if (
            props.propRepeatType != repeatType.value &&
            repeatType.value != null
        ) {
            noSingle.value = true;
        } else {
            noSingle.value = false;
        }
        if (props.propRepeatType != " ") {
            isRecurringActivityEditVisible.value = true;
            value.value = values;
        } else {
            editActivity(values);
        }
    } else {
        editActivity(values);
    }
}

function call(editOption: string) {
    isRecurringActivityEditVisible.value = false;
    value.value.editType = editOption;
    editActivity(value.value);
}

async function editActivity(values: ActivityForm) {
    noSingle.value = false;
    const durationDate = new Date(values.duration);
    const duration = `${String(durationDate.getHours()).padStart(2, "0")}:${String(durationDate.getMinutes()).padStart(2, "0")}:00`;

    let date = undefined;
    let time = undefined;
    let start = undefined;
    let end = undefined;

    if (values.date) {
        if (values.time) {
            if (props.calendarClicked) {
                date = format(values.date, "yyyy-MM-dd");
                const timeDate = new Date(values.time);
                time = `${String(timeDate.getHours()).padStart(2, "0")}:${String(timeDate.getMinutes()).padStart(2, "0")}`;
                start = `${date}T${time}`;
                const timeZone = new Date().getTimezoneOffset() / -60;
                end = add(new Date(start), {
                    hours: parseInt(duration.split(":")[0]) + timeZone,
                    minutes: parseInt(duration.split(":")[1]),
                })
                    .toISOString()
                    .substring(0, 16);
            } else {
                date = format(values.date, "yyyy-MM-dd");
                const timeDate = new Date(values.time);
                time = `${String(timeDate.getHours()).padStart(2, "0")}:${String(timeDate.getMinutes()).padStart(2, "0")}`;
            }
        }
    }

    loadingSave.value = true;

    const activity = {
        name: values.name,
        estimated_duration: duration,
        address: values.address,
        mapbox_full_address: values.mapbox?.properties?.full_address,
        mapbox_id: values.mapbox?.properties?.mapbox_id,
        cost: values.costs,
        description: values.description,
        link: values.link,
        email: values.email,
        phone: values.phone,
        opening_hours: values.open,
        date: date,
        time: time,
        repeat_type: repeatType.value,
        repeat_interval: repeatInterval.value,
        repeat_interval_unit: repeatIntervalUnit.value,
        repeat_on: repeatOn.value,
        repeat_end_date: repeatEndDate.value,
        repeat_end_occurrences: repeatEndOccurences.value,
        edit_type: "all",
    };
    if (props.calendarClicked && values.editType != null) {
        Object.assign(activity, {
            calendar_activity_id: props.calendarActivity.id,
            edit_type: values.editType,
        });
    } else if (props.calendarClicked) {
        Object.assign(activity, {
            calendar_activity_id: props.calendarActivity.id,
        });
    }
    if (updateRef.value) {
        await client(`/api/journey/${props.id}/activity/${props.activityId}`, {
            method: "PATCH",
            body: activity,
            async onResponse({ response }) {
                if (response.ok) {
                    toast.add({
                        severity: "success",
                        summary: t.value(
                            "form.input.activity.edit.toast.success.heading",
                        ),
                        detail: t.value(
                            "form.input.activity.edit.toast.success.detail",
                        ),
                        life: 6000,
                    });
                    close();
                    loadingSave.value = false;
                    activityStore.updateActivity(response._data);
                    if (props.calendarActivity) {
                        emit("editCalendarActivity", activity.name);
                        emit("calendarMoved", start, end);
                    }
                }
            },
            async onRequestError() {
                toast.add({
                    severity: "error",
                    summary: t.value("common.toast.error.heading"),
                    detail: t.value("common.error.unknown"),
                    life: 6000,
                });
                loadingSave.value = false;
            },
            async onResponseError() {
                toast.add({
                    severity: "error",
                    summary: t.value("common.toast.error.heading"),
                    detail: t.value("common.error.unknown"),
                    life: 6000,
                });

                loadingSave.value = false;
            },
        });
    } else {
        await client(`/api/journey/${props.id}/activity`, {
            method: "POST",
            body: activity,
            async onResponse({ response }) {
                if (response.ok) {
                    toast.add({
                        severity: "success",
                        summary: t.value(
                            "form.input.activity.toast.success.heading",
                        ),
                        detail: t.value(
                            "form.input.activity.toast.success.detail",
                        ),
                        life: 6000,
                    });
                    close();
                    loadingSave.value = false;
                    const responseData = Array.isArray(response._data)
                        ? response._data
                        : [response._data];
                    activityStore.addActivity(response._data);
                    const activites = [] as Activity[];
                    activites.push(...responseData);
                    activityStore.setNewActivity(response._data);
                }
            },
            async onRequestError() {
                toast.add({
                    severity: "error",
                    summary: t.value("common.toast.error.heading"),
                    detail: t.value("common.error.unknown"),
                    life: 6000,
                });
                loadingSave.value = false;
            },
            async onResponseError({ response }) {
                if (response.status === 403) {
                    close();
                    toast.add({
                        severity: "error",
                        summary: t.value("common.toast.error.heading"),
                        detail: t.value("journey.unlock.activities"),
                        life: 6000,
                    });
                } else {
                    toast.add({
                        severity: "error",
                        summary: t.value("common.toast.error.heading"),
                        detail: t.value("common.error.unknown"),
                        life: 6000,
                    });
                }
                loadingSave.value = false;
            },
        });
    }
}

function onInvalidSubmit({ errors }: { errors: ActivityFormErrors }) {
    if (errors.link) {
        activeIndex.value = 1;
    } else if (errors.date || errors.time || errors.repeatType) {
        activeIndex.value = 2;
    } else {
        activeIndex.value = 0;
    }
}

const close = () => {
    if (props.update) {
        updateRef.value = true;
    } else {
        updateRef.value = false;
    }

    if (props.calendarClicked) {
        calendarClickedRef.value = true;
    } else {
        calendarClickedRef.value = false;
    }

    onlyShowRef.value = true;
    timeDisabled.value = true;
    loadingSave.value = false;
    activeIndex.value = 0;
    emit("close");
};

function setSelectedDate(date: Date) {
    if (date instanceof Date) {
        timeDisabled.value = false;
        selectedDate.value = date;
    }
}

function changeRepeat(selectedRepeat: string) {
    if (selectedRepeat == t.value("activity.repeat.custom")) {
        repeatType.value = "custom";
    } else {
        if (selectedRepeat == t.value("activity.repeat.daily")) {
            repeatType.value = "daily";
        } else if (selectedRepeat == t.value("activity.repeat.weekly")) {
            repeatType.value = "weekly";
        } else {
            repeatType.value = " ";
        }
        repeatOn.value = undefined;
        repeatInterval.value = undefined;
        repeatIntervalUnit.value = undefined;
        repeatEndDate.value = undefined;
        repeatEndOccurences.value = undefined;
    }
}

function changeCustomRepeat(
    selectedRepeat: string,
    repeat_interval: number,
    repeat_interval_unit: string,
    repeat_on: string[],
    repeat_end_date: Date | null,
    repeat_end_occurences: number | null,
) {
    changeRepeat(selectedRepeat);
    repeatEndDate.value = undefined;
    repeatEndOccurences.value = undefined;
    repeatInterval.value = repeat_interval;
    repeatIntervalUnit.value = repeat_interval_unit;
    repeatOn.value = repeat_on.length == 0 ? null : repeat_on;
    if (repeat_end_date) {
        repeatEndDate.value = repeat_end_date;
    } else {
        repeatEndOccurences.value = repeat_end_occurences;
    }
}
</script>

<template>
    <Dialog
        v-model:visible="isVisible"
        modal
        block-scroll
        dismissable-mask
        :auto-z-index="true"
        :draggable="false"
        close-on-escape
        class="z-50 flex w-full flex-col rounded-lg bg-background font-nunito dark:bg-background-dark sm:w-6/12 md:rounded-xl"
        :pt="{
            root: {
                class: 'font-nunito bg-background dark:bg-background-dark z-10',
            },
            header: {
                class: 'flex justify-end h-1 pb-2 font-nunito bg-background dark:bg-background-dark',
            },
            content: {
                class: 'font-nunito bg-background dark:bg-background-dark px-0 sm:px-5 h-full',
            },
            footer: { class: 'h-0' },
            closeButtonIcon: {
                class: 'z-20 text-natural-500 hover:text-text dark:text-natural-400 dark:hover:text-natural-50 h-10 w-10',
            },
        }"
        @hide="close"
    >
        <form
            class="flex h-full flex-col justify-between bg-background font-nunito text-text dark:bg-background-dark dark:text-natural-50"
            @submit="onSubmit"
        >
            <TabView
                v-model:active-index="activeIndex"
                :pt="{
                    root: {
                        class: 'font-nunito bg-background dark:bg-background-dark text-text dark:text-natural-50',
                    },
                    panelContainer: {
                        class: 'text-text dark:text-natural-50 font-nunito bg-background dark:bg-background-dark',
                    },
                    nav: {
                        class: 'font-nunito bg-background dark:bg-background-dark text-lg',
                    },
                    navContainer: {
                        class: 'border-b-2 border-natural-200 dark:border-natural-500',
                    },
                    inkbar: {
                        class: 'pt-0.5 bg-calypso-400 dark:bg-calypso-400',
                    },
                }"
            >
                <TabPanel
                    :header="t('activity.create.header')"
                    :pt="{
                        headerAction: () => ({
                            class: [
                                'font-nunito bg-background dark:bg-background-dark',
                                {
                                    'text-calypso-600 text-border-600 dark:text-calypso-400 border-b-2 dark:border-calypso-400':
                                        activeIndex === 0,
                                    'text-natural-500': activeIndex !== 0,
                                },
                            ],
                        }),
                    }"
                >
                    <div
                        class="mb-8 grid grid-cols-2 grid-rows-4 gap-x-2 sm:grid-cols-5 sm:gap-x-0"
                    >
                        <FormClassicInputIcon
                            id="name"
                            name="name"
                            :value="name"
                            :disabled="onlyShowRef && !create && !updateRef"
                            translation-key="form.input.activity.name"
                            icon="pi-tag"
                            :icon-pos-is-left="true"
                            class="order-1 col-span-full sm:col-span-3"
                        />
                        <FormTimeInput
                            id="duration"
                            name="duration"
                            :value="estimatedDuration"
                            :disabled="onlyShowRef && !create && !updateRef"
                            translation-key="form.input.activity.duration"
                            class="order-2 col-span-1 w-full sm:col-span-2 sm:w-5/6 sm:justify-self-end"
                            :default-time="new Array(0, 30)"
                        />
                        <div
                            class="order-4 col-span-full flex flex-col sm:order-3 sm:col-span-3"
                        >
                            <label class="text-sm font-medium md:text-base">
                                <T key-name="form.input.activity.address" />
                            </label>
                            <FormAddressInput
                                name="address"
                                :value="address"
                                :disabled="onlyShowRef && !create && !updateRef"
                            />
                        </div>

                        <FormClassicInputIcon
                            id="costs"
                            name="costs"
                            :value="cost"
                            :disabled="onlyShowRef && !create && !updateRef"
                            translation-key="form.input.activity.costs"
                            icon="pi-money-bill"
                            class="order-3 col-span-1 w-full sm:order-4 sm:col-span-2 sm:w-5/6 sm:justify-self-end"
                        />
                        <FormClassicInputIcon
                            id="description"
                            name="description"
                            :value="description"
                            :disabled="onlyShowRef && !create && !updateRef"
                            translation-key="form.input.activity.description"
                            class="order-5 col-span-full row-span-2"
                            custom-class="h-full"
                            input-type="textarea"
                        />
                    </div>
                </TabPanel>
                <TabPanel
                    :header="t('activity.extra.header')"
                    :pt="{
                        headerAction: () => ({
                            class: [
                                'font-nunito bg-background dark:bg-background-dark',
                                {
                                    'text-calypso-600 text-border-600 dark:text-calypso-400 border-b-2 dark:border-calypso-400':
                                        activeIndex === 1,
                                    'text-natural-500': activeIndex !== 1,
                                },
                            ],
                        }),
                    }"
                >
                    <div
                        class="mb-8 grid grid-cols-1 gap-x-3 sm:mb-9 sm:grid-cols-2 md:gap-x-10"
                    >
                        <div>
                            <FormGroupInput
                                id="link"
                                :disabled="onlyShowRef && !create && !updateRef"
                                :value="link"
                                name="link"
                                translation-key="form.input.activity.link"
                                placeholder="https://www.journeyplanner.io"
                                icon="pi-globe"
                            />
                            <label
                                class="whitespace-nowrap text-sm md:text-base"
                            >
                                <T key-name="form.input.activity.contact" />
                            </label>
                            <FormGroupInput
                                id="email"
                                :disabled="onlyShowRef && !create && !updateRef"
                                :value="email"
                                name="email"
                                icon="pi-at"
                                placeholder="contact@journeyplanner.io"
                            />
                            <FormGroupInput
                                id="phone"
                                :disabled="onlyShowRef && !create && !updateRef"
                                :value="phone"
                                name="phone"
                                icon="pi-phone"
                                placeholder="+12 3456789"
                            />
                        </div>
                        <FormClassicInputIcon
                            id="opening-hours"
                            name="open"
                            :disabled="onlyShowRef && !create && !updateRef"
                            :value="openingHours"
                            translation-key="form.input.activity.opening-hours"
                            input-type="textarea"
                            custom-class="h-full"
                            class="h-36 sm:col-start-2 sm:h-64"
                        />
                    </div>
                </TabPanel>
                <TabPanel
                    v-if="!onlyShowRef || create || updateRef"
                    :header="t('activity.manual.header')"
                    :pt="{
                        headerAction: () => ({
                            class: [
                                'font-nunito bg-background dark:bg-background-dark',
                                {
                                    'text-calypso-600 text-border-600 dark:text-calypso-400 border-b-2 dark:border-calypso-400':
                                        activeIndex === 2,
                                    'text-natural-500': activeIndex !== 2,
                                },
                            ],
                        }),
                    }"
                >
                    <div
                        class="grid-rows-1 md:grid md:grid-cols-4 md:grid-rows-1 md:gap-0"
                    >
                        <FormInlineCalendar
                            id="calendar-inline"
                            name="date"
                            :from="from"
                            :to="to"
                            :prefill="selectedDate"
                            class="col-span-2"
                            @date-selected="setSelectedDate"
                        />
                        <div
                            class="col-span-2 mt-2 flex flex-row gap-x-2 sm:mt-0 sm:flex-col sm:pl-10"
                        >
                            <FormInputCalendar
                                id="calendar-input"
                                name="date"
                                :from="from"
                                :to="to"
                                :prefill="selectedDate"
                                translation-key="form.input.activity.date"
                                class="w-full sm:pb-2 sm:pr-16"
                                @date-selected="setSelectedDate"
                            />
                            <FormTimeInput
                                id="calendar-time"
                                name="time"
                                translation-key="form.input.activity.time"
                                :disabled="timeDisabled"
                                :value="
                                    props.calendarActivity
                                        ? props.calendarActivity.start.split(
                                              'T',
                                          )[1]
                                        : null
                                "
                                class="w-full sm:pb-2 sm:pr-16"
                            />
                            <div class="max-sm:hidden sm:pb-2 sm:pr-16">
                                <JourneyIdComponentsActivityRepeat
                                    id="repeatType"
                                    name="repeatType"
                                    class="w-full"
                                    :journey-start="from"
                                    :journey-end="to"
                                    :repeat-type="props.propRepeatType"
                                    :repeat-end-date="
                                        props.repeatEndDatePrefill
                                    "
                                    :repeat-end-occurences="
                                        props.repeatEndOccurencesPrefill
                                    "
                                    :repeat-interval="
                                        props.repeatIntervalPrefill
                                    "
                                    :repeat-interval-unit="
                                        props.repeatIntervalUnitPrefill
                                    "
                                    :repeat-on="props.repeatOnPrefill"
                                    @input="changeRepeat"
                                    @custom-input="changeCustomRepeat"
                                />
                            </div>
                        </div>
                    </div>
                    <JourneyIdComponentsActivityRepeatEditType
                        :visible="isRecurringActivityEditVisible"
                        :no-single="noSingle"
                        @close="isRecurringActivityEditVisible = false"
                        @post="call"
                    />
                    <div
                        class="max-sm:flex max-sm:w-full max-sm:flex-row max-sm:gap-x-2 sm:hidden"
                    >
                        <div class="max-sm:w-1/2" />
                        <div
                            class="mb-0 flex cursor-pointer flex-col pr-2 max-sm:col-span-3 max-sm:w-1/2 sm:mb-2 sm:pr-16"
                        >
                            <JourneyIdComponentsActivityRepeat
                                id="repeatType-mobile"
                                name="repeatType"
                                class="w-full sm:collapse"
                                :journey-start="from"
                                :journey-end="to"
                                :repeat-type="props.propRepeatType"
                                :repeat-end-date="props.repeatEndDatePrefill"
                                :repeat-end-occurences="
                                    props.repeatEndOccurencesPrefill
                                "
                                :repeat-interval="props.repeatIntervalPrefill"
                                :repeat-interval-unit="
                                    props.repeatIntervalUnitPrefill
                                "
                                :repeat-on="props.repeatOnPrefill"
                                @input="changeRepeat"
                                @custom-input="changeCustomRepeat"
                            />
                        </div>
                    </div>
                </TabPanel>
            </TabView>

            <div
                v-if="(!onlyShowRef && !updateRef) || create"
                class="mx-5 flex h-full flex-row justify-between gap-2 bg-background align-bottom font-nunito dark:bg-background-dark"
            >
                <Button
                    type="button"
                    :label="t('common.button.cancel')"
                    icon="pi pi-times"
                    class="mt-auto h-9 w-40 rounded-xl border-2 border-mahagony-400 bg-natural-50 px-2 font-bold text-text hover:bg-mahagony-300 dark:bg-natural-900 dark:text-natural-50 dark:hover:bg-mahagony-500030"
                    :pt="{
                        root: { class: 'flex items-center justify-center' },
                        label: {
                            class: 'display-block flex-none font-bold font-nunito',
                        },
                    }"
                    @click="close"
                />
                <Button
                    type="submit"
                    :label="t('common.button.create')"
                    icon="pi pi-check"
                    :loading="loadingSave"
                    :pt="{
                        root: { class: 'flex items-center justify-center' },
                        label: {
                            class: 'display-block flex-none font-bold font-nunito',
                        },
                    }"
                    class="mt-auto flex h-9 w-40 flex-row justify-center rounded-xl border-2 border-dandelion-300 bg-natural-50 text-center text-text hover:bg-dandelion-200 dark:bg-natural-900 dark:text-natural-50 dark:hover:bg-pesto-600"
                />
            </div>
            <div
                v-else-if="calendarClickedRef"
                class="mx-5 flex h-full flex-row justify-between gap-1.5 bg-background align-bottom font-nunito dark:bg-background-dark"
            >
                <Button
                    v-if="calendarActivity && !props.isSharedSite"
                    type="button"
                    :label="t('calendar.options.remove')"
                    class="mt-auto h-9 w-40 rounded-xl border-2 border-mahagony-400 bg-natural-50 px-2 font-bold text-text hover:bg-mahagony-300 dark:bg-natural-900 dark:text-natural-50 dark:hover:bg-mahagony-500030"
                    icon="pi pi-calendar-times"
                    :pt="{
                        root: { class: 'flex items-center justify-center' },
                        label: {
                            class: 'display-block flex-none font-bold font-nunito',
                        },
                    }"
                    @click="confirmRemoveFromCalendar"
                />
                <Button
                    v-if="!props.isSharedSite"
                    type="button"
                    :label="t('dashboard.options.edit')"
                    class="mt-auto h-9 w-40 rounded-xl border-2 border-dandelion-300 bg-natural-50 px-2 font-bold text-text hover:bg-dandelion-200 dark:bg-natural-900 dark:text-natural-50 dark:hover:bg-pesto-600"
                    icon="pi pi-pencil"
                    :pt="{
                        root: { class: 'flex items-center justify-center' },
                        label: {
                            class: 'display-block flex-none font-bold font-nunito',
                        },
                    }"
                    @click="
                        onlyShowRef = false;
                        updateRef = true;
                        calendarClickedRef = false;
                    "
                />
            </div>
            <div
                v-else-if="updateRef"
                class="mx-5 flex h-full flex-row justify-between gap-1.5 bg-background align-bottom font-nunito dark:bg-background-dark"
            >
                <Button
                    type="button"
                    :label="t('dashboard.options.delete')"
                    class="mt-auto h-9 w-40 rounded-xl border-2 border-mahagony-400 bg-natural-50 px-2 font-bold text-text hover:bg-mahagony-300 dark:bg-natural-900 dark:text-natural-50 dark:hover:bg-mahagony-500030"
                    icon="pi pi-trash"
                    :pt="{
                        root: { class: 'flex items-center justify-center' },
                        label: {
                            class: 'display-block flex-none font-bold font-nunito',
                        },
                    }"
                    @click="confirmDelete"
                />
                <Button
                    type="submit"
                    :label="t('common.save')"
                    :loading="loadingSave"
                    icon="pi pi-check"
                    :pt="{
                        root: { class: 'flex items-center justify-center' },
                        label: {
                            class: 'display-block flex-none font-bold font-nunito',
                        },
                    }"
                    class="mt-auto flex h-9 w-40 flex-row justify-center rounded-xl border-2 border-atlantis-400 bg-natural-50 text-center text-text hover:bg-atlantis-200 dark:bg-natural-900 dark:text-natural-50 dark:hover:bg-atlantis-30040"
                />
            </div>
        </form>
    </Dialog>
</template>
