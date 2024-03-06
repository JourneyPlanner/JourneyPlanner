<script setup lang="ts">
import { Form, Field, ErrorMessage } from "vee-validate";
import { T } from "@tolgee/vue";
import Aircraft from "~/public/icons/Aircraft.vue";
import Clouds from "~/public/icons/Clouds.vue";

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
  return "Password must be at least 6 characters long";
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
    <div class="xl:w-1/2 md:w-1/3 sm:w-0">
      <Aircraft></Aircraft>
      <Clouds></Clouds>
    </div>
    <div class="flex xl:w-1/2 md:w-2/3 sm:w-full justify-center items-center">
      <div class="text-center mt-10">
        <fieldset
          id="outerBlock"
          class="block w-full px-3 py-2 pl-2 bg-teal-300 rounded-md border border-gray-300 shadow-sm focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"
        >
          <legend for="outerBlock">Registration</legend>
          <Form @submit="onSubmit">
            <div class="relative my-2">
              <Field
                type="text"
                id="email"
                name="email"
                value=""
                class="block rounded-lg px-2.5 pb-2.5 pt-5 w-full text-sm text-gray-900 bg-gray-50 dark:bg-gray-700 border-0 dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer"
                placeholder=" "
                :rules="requiredEmail"
              />
              <ErrorMessage name="email" />
              <label
                for="email"
                class="absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-4 scale-75 top-4 z-10 origin-[0] start-2.5 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-4 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto"
                ><T keyName="form.input.email"
              /></label>
            </div>

            <div class="relative my-2">
              <Field
                type="text"
                id="username"
                name="username"
                value=""
                class="block rounded-lg px-2.5 pb-2.5 pt-5 w-full text-sm text-gray-900 bg-gray-50 dark:bg-gray-700 border-0 dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer"
                placeholder=" "
                :rules="required"
              />
              <ErrorMessage name="username" />
              <label
                for="username"
                class="absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-4 scale-75 top-4 z-10 origin-[0] start-2.5 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-4 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto"
                ><T keyName="form.input.username"
              /></label>
            </div>

            <div class="relative my-2">
              <Field
                type="password"
                id="password"
                name="password"
                value=""
                class="block rounded-lg px-2.5 pb-2.5 pt-5 w-full text-sm text-gray-900 bg-gray-50 dark:bg-gray-700 border-0 dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer"
                placeholder=" "
                v-model="state.password"
                :rules="passwordRules"
              />
              <ErrorMessage name="password" />
              <label
                for="password"
                class="absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-4 scale-75 top-4 z-10 origin-[0] start-2.5 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-4 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto"
                ><T keyName="form.input.password"
              /></label>
            </div>

            <div class="relative my-2">
              <Field
                type="password"
                id="repeatPassword"
                name="repeatPassword"
                value=""
                class="block rounded-lg px-2.5 pb-2.5 pt-5 w-full text-sm text-gray-900 bg-gray-50 dark:bg-gray-700 border-0 dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer"
                placeholder=" "
                :rules="sameAsPassword"
              />
              <ErrorMessage name="repeatPassword" />
              <label
                for="repeatPassword"
                class="absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-4 scale-75 top-4 z-10 origin-[0] start-2.5 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-4 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto"
                ><T keyName="form.input.password.repeat"
              /></label>
            </div>
            <button class="rounded-2xl bg-yellow-400 py-1 px-4">
              <T keyName="form.button.register" />
            </button>
          </Form>
        </fieldset>
      </div>
    </div>
  </div>
</template>
