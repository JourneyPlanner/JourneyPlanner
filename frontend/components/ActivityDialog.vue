<script setup lang="ts">
import { useTranslate, T } from "@tolgee/vue";
import { useForm } from "vee-validate";
import type { Slot } from "vue";
import * as yup from "yup";

const { t } = useTranslate();

const props = defineProps({ visible: Boolean });
const emit = defineEmits(["close"]);

const isVisible = ref(props.visible);
const loadingSave = ref(false);
const { value: dateValue } = useField<Date>("date");
const { value: timeValue } = useField<String[]>("time");
const store = useJourneyStore();
const date = ref();
const time = ref();

interface SlotProps {
    date: {
        day: number;
        month: number;
        year: number;
    };
}

const toDate = new Date(store.getToDate()).getDate();
const fromDate = new Date(store.getFromDate()).getDate();
const toMonth = new Date(store.getToDate()).getMonth();
const fromMonth = new Date(store.getFromDate()).getMonth();
const toYear = new Date(store.getToDate()).getFullYear();
const fromYear = new Date(store.getFromDate()).getFullYear();

function checkJourneyRange(slotProp: SlotProps) {
    return slotProp.date.day >= fromDate && slotProp.date.day <= toDate && slotProp.date.month >= fromMonth && slotProp.date.month <= toMonth && slotProp.date.year >= fromYear && slotProp.date.year <= toYear;
};


watch(() => props.visible, (value) => {
    isVisible.value = value;
});

const { handleSubmit } = useForm({
    validationSchema: yup.object({
        name: yup.string().required(t.value("form.input.required")),
        duration: yup.string().required(t.value("form.input.required")),
    }),
});

const onSubmit = handleSubmit((values) => {
    console.log(values);
});

const close = () => {
    emit("close");
};

</script>


<template>
    <Dialog v-model:visible="isVisible" modal class="w-2/4" dragabble="false" @hide="close"
        :pt="{ root: { class: 'font-nunito' }, header: { class: 'flex justify-end h-1 pb-2' }, }">
        <form @submit="onSubmit">
            <TabView :pt="{ root: { class: 'font-nunito' } }">
                <TabPanel :header="t('activity.create.header')">
                    <div class="grid grid-cols-2 grid-rows-4 gap-x-10">
                        <FormClassicInput id="name" name="name" translationKey="form.input.activity.name" />
                        <FormTimeInput id="duration" name="duration" translationKey="form.input.activity.duration" />
                        <FormClassicInput id="address" name="address" translationKey="form.input.activity.address" />
                        <FormClassicInput id="costs" name="costs" translationKey="form.input.activity.costs" />
                        <FormClassicInput id="description" name="description"
                            translationKey="form.input.activity.description" class="col-span-2 row-span-3"
                            customClass="h-full" inputType="textarea" />
                    </div>
                </TabPanel>
                <TabPanel :header="t('activity.extra.header')">
                    <div class="grid grid-cols-2 grid-rows-1 gap-x-10 ">
                        <div>
                            <FormGroupInput id="link" name="link" translationKey="form.input.activity.link"
                                icon="pi-globe" />
                            <label>
                                <T keyName="form.input.activity.contact" />
                            </label>
                            <FormGroupInput id="email" name="email" icon="pi-at" />
                            <FormGroupInput id="phone" name="phone" icon="pi-phone" />
                        </div>
                        <FormClassicInput id="opening-hours" name="opening-hours"
                            translationKey="form.input.activity.opening-hours" customClass="h-full pb-32"
                            inputType="textarea" class="col-start-2 row-span-2" />
                    </div>
                </TabPanel>
                <TabPanel :header="t('activity.manual.header')">
                    <div class="grid cols-2 grid-rows-1">
                        <div class="col-start-1">
                            <Calendar v-model="dateValue" inline showWeek class="border rounded-lg border-border-grey">
                                <template #date="slotProps">
                                    <strong v-if="checkJourneyRange(slotProps)" style="font-weight: 800;">{{
                                        slotProps.date.day
                                    }}</strong>
                                    <template v-else>{{ slotProps.date.day }}</template>
                                </template>
                            </Calendar>
                            <p>
                                <T keyName="form.input.date.info" />
                            </p>
                        </div>
                        <!-- TODO: time und date werden noch nicht submitted -->
                        <div class="col-start-2 flex flex-col gap-5">
                            <div class="flex flex-col">
                                <label for="calendar-input">
                                    <T keyName="form.input.activity.date" />
                                </label>
                                <Calendar v-model="dateValue" id="calendar-input" showIcon iconDisplay="input"
                                    name="date"
                                    inputClass="block rounded-lg px-2.5 pb-1 pt-1 text-md text-text font-normal bg-input border-2 border-border focus:outline-none focus:ring-1" />
                            </div>
                            <div class="flex flex-col">
                                <label for="calendar-input">
                                    <T keyName="form.input.activity.time" />
                                </label>
                                <!-- TODO: type -->
                                <Calendar v-model="timeValue" showIcon iconDisplay="input" timeOnly name="time"
                                    inputClass="block rounded-lg px-2.5 pb-1 pt-1 text-md text-text font-normal bg-input border-2 border-border focus:outline-none focus:ring-1">
                                    <template #inputicon="{ clickCallback }">
                                        <InputIcon class="pi pi-clock cursor-pointer" @click="clickCallback" />
                                    </template>
                                </Calendar>
                            </div>
                        </div>
                    </div>
                </TabPanel>
            </TabView>
            <!-- TODO: padding lr -->
            <div class="flex justify-between mt-10 gap-2 col-span-2">
                <Button @click="close" type="button" :label="t('common.button.cancel')" icon="pi pi-times"
                    class="w-40 h-9 px-2 text-text dark:text-white font-bold border-2 bg-input dark:bg-input-dark hover:bg-cancel-bg dark:hover:bg-cancel-bg-dark border-cancel-border rounded-xl"
                    :pt="{
                        root: { class: 'flex items-center justify-center' },
                        label: { class: 'display-block flex-none' },
                    }" />
                <Button type="submit" :label="t('common.save')" icon="pi pi-check" :loading="loadingSave" :pt="{
                    root: { class: 'flex items-center justify-center' },
                    label: { class: 'display-block flex-none' },
                }"
                    class="w-40 h-9 flex flex-row justify-center text-center font-bold text-text dark:text-white border-2 bg-input dark:bg-input-dark hover:bg-fill-green-save dark:hover:bg-fill-green-save-dark border-border-green-save dark:border-border-green-save-dark rounded-xl" />
            </div>
        </form>
    </Dialog>
</template>
