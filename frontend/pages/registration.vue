<script setup lang="ts">
import { Form, Field, ErrorMessage } from "vee-validate";
import { useTranslate, T } from "@tolgee/vue";
import Aircraft from "~/public/icons/Aircraft.vue";
import Clouds from "~/public/icons/Clouds.vue";

const { t } = useTranslate();

const state = reactive({
  accepted: false,
  password: "",
  passwordRepeat: "",
  username: "",
  contact: {
    email: "",
  },
});

function onSubmit(values: JSON) {
  console.log(JSON.stringify(values));
  console.log(values);
  console.log(values.email);
  registerUser(values);
}

function passwordRules(value: string) {
  if (value.length >= 6) {
    return true;
  }
  return t("form.input.password");
}

function sameAsPassword(value: string) {
  if (value === state.password) {
    return true;
  }
  return "Passwords do not match";
}

function required(value: string) {
  if (value.length > 0) {
    return true;
  }
  return "This field is required";
}

function requiredEmail(value: string) {
  const regex = /^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i;
  if (value.length > 0 && regex.test(value)) {
    return true;
  }
  return "This field must be a valid email address";
}

async function registerUser(values: JSON) {
  const todo = await $fetch("http://127.0.0.1:8000/sanctum/csrf-cookie");
  console.log(todo);
}
</script>

<template>
  <div class="w-full flex justify-center items-center">
    <div class="xl:w-1/2 md:w-1/3 sm:w-0 h-screen">
      <Aircraft
        class="xl:w-[150%] md:w-[150%] w-0 object-none -ml-[25vh] overflow-hidden mt-20"
      />
      <Clouds
        class="xl:w-[200%] md:w-[200%] w-0 object-none -ml-[30vh] overflow-hidden"
      />
    </div>
    <div class="flex xl:w-1/2 md:w-2/3 sm:w-full items-center h-screen">
      <div class="text-center mt-10 w-4/5 h-4/5">
        <fieldset
          id="outerBlock"
          class="h-full px-3 py-2 pl-2 bg-surface rounded-2xl border-border border-2 shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 flex flex-col items-center"
        >
          <legend for="outerBlock" class="font-nunito font-bold text-3xl ml-2">
            Registration
          </legend>
          <Form @submit="onSubmit" class="w-3/4">
            <div class="relative my-8">
              <Field
                type="text"
                id="email"
                name="email"
                value=""
                class="block border-border bg-input py-4 rounded-xl px-2.5 pb-2.5 w-full text-2xl dark:bg-gray-700 border-2 dark:text-white dark:focus:border-blue-500 focus:outline-none focus:ring-0 peer"
                placeholder=" "
                :rules="requiredEmail"
              />
              <ErrorMessage name="email" />
              <label
                for="email"
                class="absolute text-2xl text-input-placeholder font-nunito dark:text-gray-400 duration-300 transform -translate-y-4 scale-75 top-4 z-10 origin-[0] start-2.5 peer-focus:text-input-label peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-50 peer-focus:-translate-y-4 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto"
                ><T keyName="form.input.email"
              /></label>
            </div>

            <div class="relative my-8">
              <Field
                type="text"
                id="username"
                name="username"
                value=""
                class="block border-border bg-input py-4 rounded-xl px-2.5 pb-2.5 w-full text-2xl dark:bg-gray-700 border-2 dark:text-white dark:focus:border-blue-500 focus:outline-none focus:ring-0 peer"
                placeholder=" "
                :rules="required"
              />
              <ErrorMessage name="username" />
              <label
                for="username"
                class="absolute text-2xl text-input-placeholder font-nunito dark:text-gray-400 duration-300 transform -translate-y-4 scale-75 top-4 z-10 origin-[0] start-2.5 peer-focus:text-input-label peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-50 peer-focus:-translate-y-4 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto"
                ><T keyName="form.input.username"
              /></label>
            </div>

            <div class="relative my-8">
              <Field
                type="password"
                id="password"
                name="password"
                value=""
                class="block border-border bg-input py-4 rounded-xl px-2.5 pb-2.5 w-full text-2xl dark:bg-gray-700 border-2 dark:text-white dark:focus:border-blue-500 focus:outline-none focus:ring-0 peer"
                placeholder=" "
                v-model="state.password"
                :rules="passwordRules"
              />
              <ErrorMessage name="password" />
              <label
                for="password"
                class="absolute text-2xl text-input-placeholder font-nunito dark:text-gray-400 duration-300 transform -translate-y-4 scale-75 top-4 z-10 origin-[0] start-2.5 peer-focus:text-input-label peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-50 peer-focus:-translate-y-4 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto"
                ><T keyName="form.input.password"
              /></label>
            </div>

            <div class="relative my-2">
              <Field
                type="password"
                id="repeatPassword"
                name="repeatPassword"
                value=""
                class="block border-border bg-input py-4 rounded-xl px-2.5 pb-2.5 w-full text-2xl dark:bg-gray-700 border-2 dark:text-white dark:focus:border-blue-500 focus:outline-none focus:ring-0 peer"
                placeholder=" "
                :rules="sameAsPassword"
              />
              <ErrorMessage name="repeatPassword" />
              <label
                for="repeatPassword"
                class="absolute text-2xl text-input-placeholder font-nunito dark:text-gray-400 duration-300 transform -translate-y-4 scale-75 top-4 z-10 origin-[0] start-2.5 peer-focus:text-input-label peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-50 peer-focus:-translate-y-4 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto"
                ><T keyName="form.input.password.repeat"
              /></label>
            </div>

            <div class="flex items-center">
              <input
                id="link-checkbox"
                type="checkbox"
                value=""
                class="w-4 h-4 bg-input border-border rounded-xl"
              />
              <label
                for="link-checkbox"
                class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300"
                >I agree with the
                <a
                  href="#"
                  class="text-blue-600 dark:text-blue-500 hover:underline"
                  >terms and conditions</a
                >.</label
              >
            </div>

            <button
              class="rounded-3xl bg-input border-cta border-2 py-4 px-12 font-nunito font-bold text-3xl hover:bg-cta"
            >
              <T keyName="form.button.register" />
            </button>
          </Form>
        </fieldset>
      </div>
    </div>
  </div>
</template>
