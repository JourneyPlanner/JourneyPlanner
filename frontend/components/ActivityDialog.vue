<script setup lang="ts">
import { T, useTranslate } from "@tolgee/vue";
import { format } from "date-fns";
import { Form } from "vee-validate";
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

const validationSchema = yup.object({
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
    "opening-hours": yup.string().nullable(),
    "calendar-date": yup.date().nullable(),
    "calendar-time": yup.string().nullable(),
});

function onSubmit(values: {
    name: string;
    duration: string;
    address: string;
    costs: string;
    description: string;
    link: string;
    email: string;
    phone: string;
    "opening-hours": string;
    "calendar-date": Date;
    "calendar-time": string;
}) {
    //TODO: Implement API call
    loadingSave.value = true;
    const activity = {
        name: values.name,
        duration: values.duration,
        address: values.address,
        costs: values.costs,
        description: values.description,
        link: values.link,
        email: values.email,
        phone: values.phone,
        openingHours: values["opening-hours"],
        date: format(values["calendar-date"], "yyyy-MM-dd"),
        time: values["calendar-time"],
    };
    console.log(activity);
}

function onInvalidSubmit({ errors }: { errors: { link: string } }) {
    if (errors.link) {
        activeIndex.value = 1;
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
        <Form
            :validation-schema="validationSchema"
            class="bg-background font-nunito text-text dark:bg-background-dark dark:text-input"
            @submit="onSubmit"
            @invalid-submit="onInvalidSubmit"
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
                </TabPanel>
                <TabPanel
                    :header="t('activity.extra.header')"
                    :pt="{
                        headerAction: {
                            class: 'font-nunito bg-background dark:bg-background-dark',
                        },
                        headerTitle: { class: 'text-input-placeholder' },
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
                            name="opening-hours"
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
                        header: {
                            class: 'font-nunito bg-background dark:bg-background-dark text-input-placeholder',
                        },
                        headerAction: {
                            class: 'font-nunito bg-background dark:bg-background-dark',
                        },
                        headerTitle: { class: 'text-input-placeholder' },
                    }"
                >
                    <div
                        class="md:cols-1 md:cols-2 grid-rows-2 md:grid md:grid-rows-1 md:gap-5"
                    >
                        <div class="col-start-1 flex min-h-72 justify-end">
                            <!-- TODO: dark mode color -->
                            <FormInlineCalendar
                                id="calendar-inline"
                                name="calendar-date"
                                :from="from"
                                :to="to"
                                :prefill="selectedDate"
                                @date-selected="setSelectedDate"
                            />
                        </div>
                        <div
                            class="mt-2 flex flex-col gap-3 md:col-start-2 md:mt-0 md:gap-5"
                        >
                            <div class="flex flex-col">
                                <label for="calendar-input">
                                    <T key-name="form.input.activity.date" />
                                </label>
                                <FormInputCalendar
                                    id="calendar-input"
                                    name="calendar-input"
                                    :from="from"
                                    :to="to"
                                    :prefill="selectedDate"
                                    @date-selected="setSelectedDate"
                                />
                            </div>
                            <FormTimeInput
                                id="calendar-time"
                                name="calendar-time"
                                translation-key="form.input.activity.time"
                                :default-time="new Array(12, 0)"
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
                    :label="t('common.save')"
                    icon="pi pi-check"
                    :loading="loadingSave"
                    :pt="{
                        root: { class: 'flex items-center justify-center' },
                        label: { class: 'display-block flex-none' },
                    }"
                    class="flex h-9 w-40 flex-row justify-center rounded-xl border-2 border-border-green-save bg-input text-center font-bold text-text hover:bg-fill-green-save dark:border-border-green-save-dark dark:bg-input-dark dark:text-input dark:hover:bg-fill-green-save-dark"
                />
            </div>
        </Form>
    </Dialog>
</template>
