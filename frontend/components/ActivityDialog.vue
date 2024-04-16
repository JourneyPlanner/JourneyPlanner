<script setup lang="ts">
import { T, useTranslate } from "@tolgee/vue";
import { useForm } from "vee-validate";
import * as yup from "yup";

const { t } = useTranslate();

const props = defineProps({ visible: Boolean });
const emit = defineEmits(["close"]);

const isVisible = ref(props.visible);
const loadingSave = ref(false);

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

const { handleSubmit } = useForm({
    validationSchema: yup.object({
        name: yup.string().required(t.value("form.input.required")),
        duration: yup.string().required(t.value("form.input.required")),
    }),
});

const onSubmit = handleSubmit((values) => {
    console.log(values);
    console.log(values.duration.getHours());
    console.log(values.duration.getMinutes());
});

const close = () => {
    selectedDate.value = null;
    emit("close");
};

function setSelectedDate(date: Date) {
    selectedDate.value = date;
    console.log(selectedDate.value);
}
</script>

<template>
    <Dialog
        v-model:visible="isVisible"
        modal
        class="w-2/4"
        :pt="{
            root: { class: 'font-nunito' },
            header: { class: 'flex justify-end h-1 pb-2' },
        }"
        @hide="close"
    >
        <form @submit="onSubmit">
            <TabView :pt="{ root: { class: 'font-nunito' } }">
                <TabPanel :header="t('activity.create.header')">
                    <div class="grid grid-cols-2 grid-rows-4 gap-x-10">
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
                            class="col-span-2 row-span-3"
                            custom-class="h-full"
                            input-type="textarea"
                        />
                    </div>
                </TabPanel>
                <TabPanel :header="t('activity.extra.header')">
                    <div class="grid grid-cols-2 grid-rows-1 gap-x-10">
                        <div>
                            <FormGroupInput
                                id="link"
                                name="link"
                                translation-key="form.input.activity.link"
                                icon="pi-globe"
                            />
                            <label>
                                <T key-name="form.input.activity.contact" />
                            </label>
                            <FormGroupInput
                                id="email"
                                name="email"
                                icon="pi-at"
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
                            custom-class="h-full pb-32"
                            input-type="textarea"
                            class="col-start-2 row-span-2"
                        />
                    </div>
                </TabPanel>
                <TabPanel :header="t('activity.manual.header')">
                    <div
                        class="cols-1 md:cols-2 grid grid-rows-2 md:grid-rows-1"
                    >
                        <div class="col-start-1 h-72">
                            <FormInlineCalendar
                                id="calendar-inline"
                                name="calendar-date"
                                :from="from"
                                :to="to"
                                :prefill="selectedDate"
                                @date-selected="setSelectedDate"
                            />
                        </div>
                        <div class="flex flex-col gap-5 md:col-start-2">
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
                            />
                        </div>
                    </div>
                </TabPanel>
            </TabView>
            <div class="col-span-2 mt-10 flex justify-between gap-2 px-5">
                <Button
                    type="button"
                    :label="t('common.button.cancel')"
                    icon="pi pi-times"
                    class="h-9 w-40 rounded-xl border-2 border-cancel-border bg-input px-2 font-bold text-text hover:bg-cancel-bg dark:bg-input-dark dark:text-white dark:hover:bg-cancel-bg-dark"
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
                    class="flex h-9 w-40 flex-row justify-center rounded-xl border-2 border-border-green-save bg-input text-center font-bold text-text hover:bg-fill-green-save dark:border-border-green-save-dark dark:bg-input-dark dark:text-white dark:hover:bg-fill-green-save-dark"
                />
            </div>
        </form>
    </Dialog>
</template>
