<script setup lang="ts">
import { Form, Field, ErrorMessage } from "vee-validate";
import { T } from "@tolgee/vue";

// const state = reactive({
//   accepted: false,
//   password: "",
//   passwordRepeat: "",
//   username: "",
//   contact: {
//     email: "",
//   },
// });

// const rules = {
//   accepted: { checked: sameAs(true), $autoDirty: true },
//   username: { required }, // Matches state.firstName
//   password: { required, minlength: minLength(6) },
//   passwordRepeat: { sameAsPassword: sameAs(computed(() => state.password)) },
//   contact: {
//     email: { required, email: email }, // Matches state.contact.email
//   },
// };

// const v$ = useVuelidate(rules, state);

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

async function registerUser() {
  const todo = await $fetch("localhost:8000", {
    method: "POST",
    body: {
      // My todo data
    },
  });
}
</script>

<template>
  <div class="w-full flex justify-center items-center">
    <div class="xl:w-1/2 md:w-1/3 sm:w-0"></div>
    <div class="flex xl:w-1/2 md:w-2/3 sm:w-full justify-center items-center">
      <div class="text-center mt-10">
        <fieldset
          id="outerBlock"
          class="block w-full px-3 py-2 pl-2 bg-teal-300 rounded-md border border-gray-300 shadow-sm focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"
        >
          <legend for="outerBlock">Registration</legend>
          <Form @submit="onSubmit">
            <div class="relative">
              <Field
                type="text"
                id="email"
                name="email"
                value=""
                class="block w-full px-3 py-2 pl-2 rounded-md border border-gray-300 shadow-sm focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"
                placeholder=" "
                :rules="requiredEmail"
              />
              <ErrorMessage name="email" />
              <label
                for="email"
                class="absolute text-sm text-gray-500 dark:text-gray-400 -translate-y-4 scale-75 top-2 z-10 origin-[0] bg-white dark:bg-gray-900 px-2 peer-focus:px-2 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-75 peer-focus:-translate-y-4 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto start-1"
                ><T keyName="register.email.input.label"
              /></label>
            </div>

            <div class="relative">
              <Field
                type="text"
                id="username"
                name="username"
                value=""
                class="my-3 block w-full px-3 py-2 pl-2 rounded-md border border-gray-300 shadow-sm focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"
                placeholder=" "
                :rules="required"
              />
              <ErrorMessage name="username" />
              <label
                for="username"
                class="absolute text-sm text-gray-500 dark:text-gray-400 -translate-y-4 scale-75 top-2 z-10 origin-[0] bg-white dark:bg-gray-900 px-2 peer-focus:px-2 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-75 peer-focus:-translate-y-4 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto start-1"
                ><T keyName="register.username.input.label"
              /></label>
            </div>

            <div class="relative">
              <Field
                id="password"
                name="password"
                type="password"
                value=""
                class="my-3 block w-full px-3 py-2 pl-2 rounded-md border border-gray-300 shadow-sm focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"
                v-model="state.password"
                placeholder=""
                :rules="passwordRules"
              />
              <ErrorMessage name="password" />
              <label
                for="password"
                class="absolute text-sm text-gray-500 dark:text-gray-400 -translate-y-4 scale-75 top-2 z-10 origin-[0] bg-white dark:bg-gray-900 px-2 peer-focus:px-2 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-75 peer-focus:-translate-y-4 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto start-1"
                ><T keyName="register.password.input.label"
              /></label>
            </div>

            <div class="relative">
              <Field
                type="password"
                id="repeatPassword"
                name="repeatPassword"
                value=""
                class="my-3 block w-full px-3 py-2 pl-2 rounded-md border border-gray-300 shadow-sm focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"
                placeholder=" "
                :rules="sameAsPassword"
              />
              <ErrorMessage name="repeatPassword" />
              <label
                for="repeatPassword"
                class="absolute text-sm text-gray-500 dark:text-gray-400 -translate-y-4 scale-75 top-2 z-10 origin-[0] bg-white dark:bg-gray-900 px-2 peer-focus:px-2 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-75 peer-focus:-translate-y-4 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto start-1"
                ><T keyName="register.repeatPassword.input.label"
              /></label>
            </div>
            <button class="rounded-2xl bg-yellow-400 py-1 px-4">
              <T keyName="register.submit.button" />
            </button>
          </Form>
        </fieldset>
      </div>
    </div>
  </div>
</template>
