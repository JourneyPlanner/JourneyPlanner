<script setup lang="ts">
import { useTolgee } from "@tolgee/vue";
import { useForm } from "vee-validate";
import * as yup from "yup";
import { useTranslate } from "@tolgee/vue";

const { t } = useTranslate();

/* function test() {
  return t.value("form.input.journey.range");
} */

//TODO: use correct error message
const { handleSubmit } = useForm({
  validationSchema: yup.object({
    journeyName: yup
      .string()
      .required(t.value("form.input.journey.name"))
      .label("Journey Name"),
    journeyDestination: yup.string().required(),
    journeyRange: yup.array().of(yup.date()).required(),
  }),
});

const onSubmit = handleSubmit((values) => {
  alert(JSON.stringify(values, null, 2));
});
</script>

<template>
  <div class="flex justify-center items-center font-nunito">
    <fieldset
      id="create-journey"
      class="w-1/3 px-5 rounded-2xl border-2 border-border shadow-sm bg-surface"
    >
      <legend for="create-journey" class="text-3xl px-2 font-bold">
        <T keyName="form.header.journey.create" />
      </legend>
      <form @submit="onSubmit" class="px-5">
        <FormInput
          id="journey-name"
          name="journeyName"
          translationKey="form.input.journey.name"
        />
        <FormInput
          id="journey-destination"
          name="journeyDestination"
          translationKey="form.input.journey.destination"
        />
        <FormCalendar
          id="journey-range-calendar"
          name="journeyRange"
          translationKey="form.input.journey.range"
        />

        <!-- Journey invite input

        <Divider type="solid" class="text-input-label border border-10" />

        <div class="relative my-2">
          <Field
            type="text"
            id="journey-invite"
            name="journey-invite"
            value=""
            class="block rounded-lg px-2.5 pb-2.5 pt-5 w-full text-sm text-text bg-input border border-border focus:outline-none focus:ring-1 peer"
            placeholder=" "
          />
          <label
            for="journey-invite"
            class="absolute text-sm text-input-placeholder duration-300 transform -translate-y-4 scale-75 top-4 z-10 origin-[0] start-2.5 peer-focus:text-input-label peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-4 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto"
            ><T keyName="form.input.journey.invite"
          /></label>
        </div>
        
        -->

        <div class="flex justify-between mt-8">
          <button
            class="px-7 py-1 text-text font-bold border-2 bg-input border-cancel rounded-2xl"
          >
            <T keyName="common.button.cancel" />
          </button>
          <button
            type="submit"
            class="px-7 py-1 bg-input font-bold text-text border-2 border-cta rounded-2xl"
          >
            <T keyName="common.button.create" />
          </button>
        </div>
      </form>
    </fieldset>
  </div>
</template>
