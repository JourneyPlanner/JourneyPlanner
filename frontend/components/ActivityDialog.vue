<script setup lang="ts">
import { T, useTranslate } from "@tolgee/vue";
import { format } from "date-fns";
//import {  } from "vee-validate";
import * as yup from "yup";

const { t } = useTranslate();

const props = defineProps({ visible: Boolean });
const emit = defineEmits(["close"]);

const isVisible = ref(props.visible);
const loadingSave = ref(false);
const activeIndex = ref(0);

const store = useJourneyStore();
const to = new Date(store.getToDate());
const from = new Date(store.getFromDate());

const selectedDate = ref();

watch(
    () => props.visible,
    (value) => {
        isVisible.value = value;
    },
);

interface ActivityForm {
    name: string;
    duration: string;
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
    bool: yup.boolean(),
    name: yup.string().required(t.value("form.input.required")),
    duration: yup
        .string()
        .test("not-00-00", t.value("form.input.required"), (value) => {
            const time = new Date(value || "");
            return time.getHours() + time.getMinutes() !== 0;
        })
        .required(t.value("form.input.required")),
    address: yup.string().nullable(),
    costs: yup.string().nullable(),
    description: yup.string().nullable(),
    link: yup.string().url(t.value("form.input.link.error")).nullable(),
    email: yup.string().email(t.value("form.input.email.error")).nullable(),
    phone: yup.string().nullable(),
    open: yup.string().nullable(),
    date: yup
        .string()
        .ensure()
        .when("time", (time, schema) => {
            console.log(time);
            return time[0] !== undefined
                ? schema.required(t.value("form.input.activity.date.error"))
                : schema.notRequired();
        })
        .notRequired(),
    time: yup.string().notRequired(),
});

const { handleSubmit } = useForm<ActivityForm>({
    validationSchema: validationSchema,
});

const onSubmit = handleSubmit(onSuccess, onInvalidSubmit);

function onSuccess(values: ActivityForm) {
    const durationDate = new Date(values.duration);
    const duration = `${durationDate.getHours()}:${String(durationDate.getMinutes()).padStart(2, "0")}`;

    let time = undefined;

    if (values.date) {
        const timeDate = new Date(values.time);
        time = `${timeDate.getHours()}:${String(timeDate.getMinutes()).padStart(2, "0")}`;
    }

    //TODO: Implement API call
    loadingSave.value = true;
    const activity = {
        name: values.name,
        estimated_duration: duration,
        address: values.address,
        costs: values.costs,
        description: values.description,
        link: values.link,
        email: values.email,
        phone: values.phone,
        opening_hours: values.open,
        date: format(values.date, "yyyy-MM-dd"),
        time: time,
    };
    console.log(activity);
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
    emit("close");
};

function setSelectedDate(date: Date) {
    selectedDate.value = date;
}
</script>

<template>
    <!-- TODO: richtige color bei inkbar light & dark -->
    <Dialog
        v-model:visible="isVisible"
        modal
        class="md:3/4 w-full rounded-lg bg-background font-nunito dark:bg-background-dark md:rounded-xl lg:w-4/5 xl:w-2/4"
        :pt="{
            root: {
                class: 'font-nunito bg-background dark:bg-background-dark',
            },
            header: {
                class: 'flex justify-end h-1 pb-2 font-nunito bg-background dark:bg-background-dark',
            },
            content: {
                class: 'font-nunito bg-background dark:bg-background-dark px-0 sm:px-5',
            },
        }"
        @hide="close"
    >
        <form
            class="bg-background font-nunito text-text dark:bg-background-dark dark:text-input"
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
                        class: 'font-nunito bg-background dark:bg-background-dark',
                    },
                    navContainer: { class: 'border-b-2 border-border-gray' },
                    inkbar: { class: 'text-cta-bg-fill' },
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
                        class="grid grid-cols-2 grid-rows-4 gap-x-3 md:gap-x-10"
                    >
                        <FormClassicInputIcon
                            id="name"
                            name="name"
                            translation-key="form.input.activity.name"
                            icon="pi-tag"
                        />
                        <FormTimeInput
                            id="duration"
                            name="duration"
                            translation-key="form.input.activity.duration"
                            class="w-full"
                            :default-time="new Array(0, 30)"
                            custom-class="w-10"
                        />
                        <FormClassicInputIcon
                            id="address"
                            name="address"
                            translation-key="form.input.activity.address"
                            icon="pi-map-marker"
                        />
                        <FormClassicInputIcon
                            id="costs"
                            name="costs"
                            translation-key="form.input.activity.costs"
                            icon="pi-money-bill"
                        />
                        <FormClassicInputIcon
                            id="description"
                            name="description"
                            translation-key="form.input.activity.description"
                            class="col-span-2 row-span-2"
                            custom-class="h-full"
                            input-type="textarea"
                        />
                    </div>
                    <FormAddressInput />
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
                        class="grid grid-cols-2 gap-x-3 md:grid-rows-2 md:gap-x-10"
                    >
                        <div>
                            <FormGroupInput
                                id="link"
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
                                name="email"
                                icon="pi-at"
                                placeholder="contact@journeyplanner.io"
                            />
                            <FormGroupInput
                                id="phone"
                                name="phone"
                                icon="pi-phone"
                            />
                        </div>
                        <FormClassicInputIcon
                            id="opening-hours"
                            name="open"
                            translation-key="form.input.activity.opening-hours"
                            custom-class="h-full"
                            input-type="textarea"
                            class="col-start-2 md:row-span-2"
                        />
                    </div>
                </TabPanel>
                <TabPanel
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
                        class="md:cols-1 md:cols-2 grid-rows-2 md:grid md:grid-rows-1 md:gap-5"
                    >
                        <div class="col-start-1 flex min-h-72 justify-end">
                            <!-- TODO: dark mode color -->
                            <FormInlineCalendar
                                id="calendar-inline"
                                name="date"
                                :from="from"
                                :to="to"
                                :prefill="selectedDate"
                                @date-selected="setSelectedDate"
                            />
                        </div>
                        <div
                            class="mt-2 flex flex-col gap-3 md:col-start-2 md:mt-0 md:gap-0"
                        >
                            <FormInputCalendar
                                id="calendar-input"
                                name="date"
                                :from="from"
                                :to="to"
                                :prefill="selectedDate"
                                translation-key="form.input.activity.date"
                                @date-selected="setSelectedDate"
                            />
                            <FormTimeInput
                                id="calendar-time"
                                name="time"
                                translation-key="form.input.activity.time"
                            />
                        </div>
                    </div>
                </TabPanel>
            </TabView>
            <div class="col-span-2 flex justify-between gap-2 px-5 md:mt-10">
                <Button
                    type="button"
                    :label="t('common.button.cancel')"
                    icon="pi pi-times"
                    class="h-9 w-40 rounded-xl border-2 border-cancel-border bg-input px-2 font-bold text-text hover:bg-cancel-bg dark:bg-input-dark dark:text-input dark:hover:bg-cancel-bg-dark"
                    :pt="{
                        root: { class: 'flex items-center justify-center' },
                        label: { class: 'display-block flex-none' },
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
                        label: { class: 'display-block flex-none' },
                    }"
                    class="flex h-9 w-40 flex-row justify-center rounded-xl border-2 border-border-green-save bg-input text-center font-bold text-text hover:bg-fill-green-save dark:border-border-green-save-dark dark:bg-input-dark dark:text-input dark:hover:bg-fill-green-save-dark"
                />
            </div>
        </form>
    </Dialog>
</template>
