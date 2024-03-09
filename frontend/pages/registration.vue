<script setup lang="ts">
import { useForm } from "vee-validate";
import { useTranslate, T } from "@tolgee/vue";
import Aircraft from "~/public/icons/Aircraft.vue";
import Clouds from "~/public/icons/Clouds.vue";
import Cloud from "~/public/icons/Cloud.vue";
import axios from "axios";
import * as yup from "yup";

const { t } = useTranslate();

const { handleSubmit } = useForm({
  validationSchema: yup.object({
    email: yup.string().email().required(),
    name: yup.string().required(),
    password: yup.string().min(8).required(),
    password_confirmation: yup
      .string()
      .oneOf([yup.ref("password")], "Passwords must match"),
  }),
});

const onSubmit = handleSubmit((values) => {
  registerUser(values);
});

function onInvalidSubmit() {
  console.log("Invalid submit");
}

async function registerUser(userData: Object) {
  axios.get("/sanctum/csrf-cookie").then((response) => {
    axios.post("/register", userData, {
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
    });
  });
}
</script>

<template>
  <div class="w-full flex justify-center items-center font-nunito">
    <div class="xl:w-1/3 md:w-1/4 sm:w-0 h-screen">
      <Aircraft
        class="xl:w-[230%] md:w-[350%] w-0 object-none -ml-[20vw] overflow-hidden mt-12 z-0"
      />
    </div>
    <div
      class="flex xl:w-1/3 md:w-2/4 sm:w-full items-center h-screen justify-center w-full"
    >
      <div class="text-center mt-6 sm:w-3/4 w-full h-3/4 z-20">
        <fieldset
          id="outerBlock"
          class="h-full px-3 py-2 pl-2 bg-surface rounded-3xl border-border border-2 shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 flex flex-col items-center"
        >
          <legend
            for="outerBlock"
            class="font-nunito font-bold text-3xl ml-2 px-3"
          >
            Registration
          </legend>
          <form @submit="onSubmit" class="w-4/5">
            <FormInput
              id="email"
              name="email"
              translationKey="form.input.email"
            />

            <FormInput
              id="name"
              name="name"
              translationKey="form.input.username"
            />

            <FormPassword
              id="password"
              name="password"
              :feedback="true"
              translationKey="form.input.password"
            />
            <div class="mt-4">
              <FormPassword
                id="password_confirmation"
                name="password_confirmation"
                :feedback="false"
                translationKey="form.input.password.repeat"
              />
            </div>

            <div class="flex items-center text-left">
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
              class="rounded-3xl bg-input border-cta border-2 py-2 px-10 font-nunito font-bold text-2xl hover:bg-cta"
            >
              <T keyName="form.button.register" />
            </button>
          </form>
        </fieldset>
      </div>
    </div>
    <div class="xl:w-1/3 md:w-1/4 sm:w-0 h-screen">
      <Cloud
        class="xl:w-[60%] md:w-[90%] w-0 object-none overflow-hidden mt-64 z-0"
      />
      <Cloud
        class="xl:w-[60%] md:w-[90%] w-0 object-none overflow-hidden mt-12 z-0"
      />
      <Cloud
        class="xl:w-[60%] md:w-[90%] w-0 object-none overflow-hidden mt-12 z-0"
      />
    </div>
  </div>
</template>
