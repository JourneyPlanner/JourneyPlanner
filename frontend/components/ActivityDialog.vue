<script setup lang="ts">
import { T, useTranslate } from "@tolgee/vue";
import { format } from "date-fns";
import * as yup from "yup";

const { t } = useTranslate();
const client = useSanctumClient();
const toast = useToast();

const props = defineProps({
    visible: { type: Boolean, required: true },
    id: { type: String, required: true },
    address: { type: String, default: "" },
    onlyShow: { type: Boolean, default: false },
    cost: { type: String, default: "" },
    created_at: { type: String, default: "" },
    description: { type: String, default: "" },
    email: { type: String, default: "" },
    estimated_duration: { type: String, default: "" },
    journey_id: { type: String, default: "" },
    latitude: { type: String, default: "" },
    longitude: { type: String, default: "" },
    link: { type: String, default: "" },
    mapbox_id: { type: String, default: "" },
    name: { type: String, default: "" },
    opening_hours: { type: String, default: "" },
    phone: { type: String, default: "" },
    updated_at: { type: String, default: "" },
});

const emit = defineEmits(["close"]);

const isVisible = ref(props.visible);
const loadingSave = ref(false);
const activeIndex = ref(0);

const store = useJourneyStore();
const activityStore = useActivityStore();
const to = new Date(store.getToDate());
const from = new Date(store.getFromDate());

const selectedDate = ref();
const timeDisabled = ref(true);

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
}

type ActivityFormErrors = Partial<Record<keyof ActivityForm, string>>;

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
    dateAndTime: yup.bool().when(["time", "date"], {
        is: (time: Date, date: Date) => (!date && !time) || (!!date && !!time),
        then: () => yup.bool().nullable(),
        otherwise: () =>
            yup.bool().required(t.value("form.input.activity.custom.error")),
    }),
});

const { handleSubmit } = useForm<ActivityForm>({
    validationSchema: validationSchema,
});

const onSubmit = handleSubmit(onSuccess, onInvalidSubmit);

async function onSuccess(values: ActivityForm) {
    const durationDate = new Date(values.duration);
    const duration = `${String(durationDate.getHours()).padStart(2, "0")}:${String(durationDate.getMinutes()).padStart(2, "0")}`;

    let date = undefined;
    let time = undefined;

    if (values.date) {
        if (values.time) {
            date = format(values.date, "yyyy-MM-dd");
            const timeDate = new Date(values.time);
            time = `${String(timeDate.getHours()).padStart(2, "0")}:${String(timeDate.getMinutes()).padStart(2, "0")}`;
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
    };

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
                    detail: t.value("form.input.activity.toast.success.detail"),
                    life: 6000,
                });
                close();
                loadingSave.value = false;
                const { data: activityData } = await useAsyncData(
                    "activity",
                    () => client(`/api/journey/${props.id}/activity`),
                );
                activityStore.setActivities(activityData.value);
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
}

function onInvalidSubmit({ errors }: { errors: ActivityFormErrors }) {
    console.log(errors);
    if (errors.link) {
        activeIndex.value = 1;
    } else if (errors.date || errors.time) {
        activeIndex.value = 2;
    } else {
        activeIndex.value = 0;
    }
}

const close = () => {
    selectedDate.value = null;
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
</script>

<template>
    <Dialog
        v-model:visible="isVisible"
        modal
        :auto-z-index="true"
        :base-z-index="1000"
        :draggable="false"
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
                class: 'z-20 text-input-placeholder hover:text-text dark:text-input-placeholder dark:hover:text-input h-10 w-10',
            },
        }"
        @hide="close"
    >
        <form
            class="flex h-full flex-col justify-between bg-background font-nunito text-text dark:bg-background-dark dark:text-input"
            @submit="onSubmit"
        >
            <TabView
                v-model:active-index="activeIndex"
                :pt="{
                    root: {
                        class: 'font-nunito bg-background dark:bg-background-dark text-text dark:text-input',
                    },
                    panelContainer: {
                        class: 'text-text dark:text-input font-nunito bg-background dark:bg-background-dark',
                    },
                    nav: {
                        class: 'font-nunito bg-background dark:bg-background-dark text-lg',
                    },
                    navContainer: {
                        class: 'border-b-2 border-border-gray dark:border-input-placeholder',
                    },
                    inkbar: { class: 'pt-0.5 bg-border' },
                }"
            >
                <TabPanel
                    :header="t('activity.create.header')"
                    :pt="{
                        headerAction: () => ({
                            class: [
                                'font-nunito bg-background dark:bg-background-dark',
                                {
                                    'text-border border-b-2 border-border':
                                        activeIndex === 0,
                                    'text-input-placeholder': activeIndex !== 0,
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
                            :disabled="onlyShow"
                            translation-key="form.input.activity.name"
                            icon="pi-tag"
                            :icon-pos-is-left="true"
                            class="order-1 col-span-full sm:col-span-3"
                        />
                        <FormTimeInput
                            id="duration"
                            name="duration"
                            :value="estimated_duration"
                            :disabled="onlyShow"
                            translation-key="form.input.activity.duration"
                            class="order-2 col-span-1 w-full sm:col-span-2 sm:w-5/6 sm:justify-self-end"
                            :default-time="new Array(0, 30)"
                        />
                        <div
                            v-if="!onlyShow"
                            class="order-4 col-span-full flex flex-col sm:order-3 sm:col-span-3"
                        >
                            <label class="text-sm font-medium md:text-base">
                                <T key-name="form.input.activity.address" />
                            </label>
                            <FormAddressInput name="address" />
                        </div>

                        <FormClassicInputIcon
                            v-if="onlyShow"
                            id="address"
                            name="address"
                            :value="address"
                            :disabled="onlyShow"
                            translation-key="form.input.activity.address"
                            icon="pi-map-marker"
                            :icon-pos-is-left="true"
                            class="order-4 col-span-full flex flex-col sm:order-3 sm:col-span-3"
                        />

                        <FormClassicInputIcon
                            id="costs"
                            name="costs"
                            :value="cost"
                            :disabled="onlyShow"
                            translation-key="form.input.activity.costs"
                            icon="pi-money-bill"
                            class="order-3 col-span-1 w-full sm:order-4 sm:col-span-2 sm:w-5/6 sm:justify-self-end"
                        />
                        <FormClassicInputIcon
                            id="description"
                            name="description"
                            :value="description"
                            :disabled="onlyShow"
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
                                    'text-border border-b-2 border-border':
                                        activeIndex === 1,
                                    'text-input-placeholder': activeIndex !== 1,
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
                                :disabled="onlyShow"
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
                                :disabled="onlyShow"
                                :value="email"
                                name="email"
                                icon="pi-at"
                                placeholder="contact@journeyplanner.io"
                            />
                            <FormGroupInput
                                id="phone"
                                :disabled="onlyShow"
                                :value="phone"
                                name="phone"
                                icon="pi-phone"
                                placeholder="+12 3456789"
                            />
                        </div>
                        <FormClassicInputIcon
                            id="opening-hours"
                            name="open"
                            :disabled="onlyShow"
                            :value="opening_hours"
                            translation-key="form.input.activity.opening-hours"
                            input-type="textarea"
                            custom-class="h-full"
                            class="h-36 sm:col-start-2 sm:h-64"
                        />
                    </div>
                </TabPanel>
                <TabPanel
                    v-if="!onlyShow"
                    :header="t('activity.manual.header')"
                    :pt="{
                        headerAction: () => ({
                            class: [
                                'font-nunito bg-background dark:bg-background-dark',
                                {
                                    'text-border border-b-2 border-border':
                                        activeIndex === 2,
                                    'text-input-placeholder': activeIndex !== 2,
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
                                class="w-full sm:pr-16"
                            />
                        </div>
                    </div>
                </TabPanel>
            </TabView>

            <div
                v-if="!onlyShow"
                class="mx-5 flex h-full flex-row justify-between gap-2 bg-background align-bottom font-nunito dark:bg-background-dark"
            >
                <Button
                    type="button"
                    :label="t('common.button.cancel')"
                    icon="pi pi-times"
                    class="mt-auto h-9 w-40 rounded-xl border-2 border-cancel-border bg-input px-2 font-bold text-text hover:bg-cancel-bg dark:bg-input-dark dark:text-input dark:hover:bg-cancel-bg-dark"
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
                    class="mt-auto flex h-9 w-40 flex-row justify-center rounded-xl border-2 border-cta-border bg-input text-center text-text hover:bg-cta-bg dark:bg-input-dark dark:text-input dark:hover:bg-cta-bg-dark"
                />
            </div>
        </form>
    </Dialog>
</template>
