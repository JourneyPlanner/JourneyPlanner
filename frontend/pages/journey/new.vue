<script setup lang="ts">
import { useForm } from "vee-validate";
import * as yup from "yup";
import { useTranslate } from "@tolgee/vue";
import { v4 as uuidv4 } from "uuid";

const { t } = useTranslate();

const journeyInvite = uuidv4();

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

const onSubmit = handleSubmit((values) => {
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

  console.log(journey);
});
</script>

<template>
  <div class="flex justify-center items-center font-nunito mt-20">
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
          translationKey="form.input.journey.dates"
        />

        <Divider type="solid" class="text-input-label border border-10" />

        <!--
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

        <div class="flex justify-between mb-5">
          <button
            type="button"
            class="px-7 py-1 text-text font-bold border-2 bg-input hover:bg-cancel-bg border-cancel-border rounded-xl"
          >
            <T keyName="common.button.cancel" />
          </button>

          <button
            type="submit"
            class="px-7 py-1 font-bold text-text border-2 bg-input hover:bg-cta-bg border-cta-border rounded-xl"
          >
            <T keyName="common.button.create" />
          </button>
        </div>
      </form>
    </fieldset>
  </div>
  <div>
    <div class="flex flex-row justify-between h-full items-end">
      <SvgPeopleBackpackMap class="" />
      <div class="flex flex-row items-end">
        <SvgWomanSuitcaseLeft class="" />
        <SvgWomanSuitcaseRight class="ml-10" />
      </div>
    </div>
    <Divider
      type="solid"
      class="text-[#CCCCCC] border-b"
      :pt="{
        root: { class: 'mt-0' },
      }"
    />
  </div>
</template>
