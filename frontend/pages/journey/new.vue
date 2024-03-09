<script setup lang="ts">
<<<<<<< HEAD
import { Form, Field, ErrorMessage } from "vee-validate";
import { T } from "@tolgee/vue";

const journeyName = ref("");
const journeyDestination = ref("");
const journeyRange = ref("");

let journeyRangeIsFocused = ref(false);

const handleFocus = () => {
  journeyRangeIsFocused.value = true;
};

const handleBlur = () => {
  journeyRangeIsFocused.value = false;
};

function required(value: string) {
  if (value.length > 0) {
    return true;
  }
  return "This field is required";
}

function requiredJourneyName(value: string) {
  if (value.length > 0) {
    return true;
  }

  return "name fehlt";
}

function requiredJourneyDestination(value: string) {
  if (value.length > 0) {
    return true;
  }

  return "destination fehlt";
}

function onSubmit() {
  console.log("submit");
}
=======
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
>>>>>>> a36efa6789044101f1cc82332b7a4dad2c552c61
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
<<<<<<< HEAD
      <Form @submit="onSubmit" class="px-5">
        <Input
          id="journey-name"
          name="journeyName"
          :value="journeyName"
          validation="requiredJourneyName"
          translationKey="form.input.journey.name"
        />
        <Input
          id="journey-destination"
          name="journeyDestination"
          :value="journeyDestination"
          validation="requiredJourneyDestination"
          translationKey="form.input.journey.destination"
        />
        <div class="relative">
          <Calendar
            id="journey-range-calendar"
            v-model="journeyRange"
            selectionMode="range"
            showButtonBar
            dateFormat="dd/mm/yy"
            value=""
            inputClass="block rounded-lg px-2.5 pb-1 pt-4 w-full text-md text-text font-bold bg-input border-2 border-border focus:outline-none focus:ring-1 peer"
            @focus="handleFocus"
            @focusout="handleBlur"
            @input="handleFocus"
            @date-select="handleFocus"
            @clear-click="handleBlur"
            @hide="handleBlur"
            :pt="{
              day: { class: 'text-text font-nunito' },
              panel: { class: 'text-text font-nunito' },
              monthPicker: { class: 'text-cta' },
            }"
          />
          <label
            for="journey-range-calendar"
            class="absolute text-sm top-0 left-0 px-3.5 py-4 pointer-events-none transition-all duration-300"
            :class="{
              'text-input-placeholder': !journeyRangeIsFocused,
              'text-input-label': journeyRangeIsFocused,
              '-translate-y-4 -translate-x-4 scale-75':
                journeyRangeIsFocused || journeyRange,
              'translate-y-0 scale-100':
                !journeyRangeIsFocused && !journeyRange,
            }"
          >
            <T keyName="form.input.journey.range" />
          </label>
        </div>
=======
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
>>>>>>> a36efa6789044101f1cc82332b7a4dad2c552c61

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
<<<<<<< HEAD
=======
            type="submit"
>>>>>>> a36efa6789044101f1cc82332b7a4dad2c552c61
            class="px-7 py-1 bg-input font-bold text-text border-2 border-cta rounded-2xl"
          >
            <T keyName="common.button.create" />
          </button>
        </div>
<<<<<<< HEAD
      </Form>
=======
      </form>
>>>>>>> a36efa6789044101f1cc82332b7a4dad2c552c61
    </fieldset>
  </div>
</template>
