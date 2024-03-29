<script setup lang="ts">
import { useForm } from "vee-validate";
import * as yup from "yup";
import Toast from "primevue/toast";
import { useTranslate } from "@tolgee/vue";
import { v4 as uuidv4 } from "uuid";

const { t } = useTranslate();
const client = useSanctumClient();
const toast = useToast();

const journeyInvite = uuidv4();

const title = t.value("title.journey.create");
useHead({
  title: `${title} | JourneyPlanner`,
})

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
          .label(t.value("form.input.journey.dates"))
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

  let name = values.journeyName;
  let destination = values.journeyDestination;
  let from = values.journeyRange[0];
  let to = values.journeyRange[1];
  let invite = journeyInvite;

  const journey = {
    name,
    destination,
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
</script>

<template>
  <div class="overflow-hidden overflow-y-hidden">
    <div class="flex flex-col justify-between z-10 h-[96vh]">
      <Toast />
      <div class="flex justify-center items-center font-nunito mt-16 z-50">
        <fieldset id="create-journey"
          class="w-full sm:w-1/4 md:w-1/3 px-5 rounded-2xl border-2 border-border shadow-sm bg-surface dark:bg-surface-dark">
          <legend for="create-journey"
            class="text-2xl ml-4 text-center text-text dark:text-white lg:text-left lg:text-3xl px-2 font-bold">
            <T keyName="form.header.journey.create" />
          </legend>
          <form @submit="onSubmit" class="px-1 lg:px-5">
            <FormInput id="journey-name" name="journeyName" translationKey="form.input.journey.name" />
            <FormInput id="journey-destination" name="journeyDestination"
              translationKey="form.input.journey.destination" />
            <FormCalendar id="journey-range-calendar" name="journeyRange" translationKey="form.input.journey.dates" />

            <!--
          <Divider type="solid" class="text-input-label border border-10" />

          <div class="relative my-2">
            <input
              type="text"
              id="journey-invite"
              name="journey-invite"
              v-model="journeyInvite"
              disabled
              class="peer w-full rounded-lg placeholder:text-transparent px-2.5 pb-1 pt-4 text-md text-text font-bold bg-input-disabled border-2 border-border focus:outline-none focus:ring-1"
              placeholder=" "
            />
            <label
              for="journey-invite"
              class="absolute text-input-placeholder left-0 ml-1.5 mt-1 transition-transform -translate-y-0.5 bg-white px-1 text-xs duration-100 ease-linear peer-placeholder-shown:translate-y-2.5 peer-placeholder-shown:text-sm peer-focus:text-input-label peer-placeholder-shown:text-input-placeholder peer-focus:ml-1.5 peer-focus:-translate-y-0.5 peer-focus:px-1 peer-focus:text-xs"
              ><T keyName="form.input.journey.invite"
            /></label>
          </div>
          -->

            <div class="flex justify-between mt-6 mb-5 gap-5">
              <NuxtLink to="/dashboard">
                <button type="button"
                  class="px-7 py-1 text-text dark:text-white font-bold border-2 bg-input dark:bg-input-dark hover:bg-cancel-bg dark:hover:bg-cancel-bg-dark border-cancel-border rounded-xl">
                  <T keyName="common.button.cancel" />
                </button>
              </NuxtLink>

              <button type="submit"
                class="px-7 py-1 font-bold text-text dark:text-white border-2 bg-input dark:bg-input-dark hover:bg-cta-bg dark:hover:bg-cta-bg-dark border-cta-border rounded-xl">
                <T keyName="common.button.create" />
              </button>
            </div>
          </form>
        </fieldset>
      </div>
      <div class="z-10">
        <div class="flex flex-row justify-between items-end">
          <SvgPeopleBackpackMap class="hidden lg:block" />
          <div class="flex flex-row items-end justify-between w-full lg:w-auto mt-2 sm:mt-0">
            <SvgWomanSuitcaseLeft class="" />
            <SvgWomanSuitcaseRight class="ml-10" />
          </div>
        </div>
        <Divider type="solid" class="text-[#CCCCCC] border-b" :pt="{
            root: { class: 'mt-0' },
          }" />
      </div>
    </div>
    <div class="z-10">
      <SvgCloud class="invisible md:visible h-14 object-none overflow-hidden top-72 left-[28%] z-0 absolute" />
      <SvgCloud class="invisible md:visible h-16 object-none overflow-hidden top-36 right-[20%] z-0 absolute" />
    </div>
  </div>
</template>
