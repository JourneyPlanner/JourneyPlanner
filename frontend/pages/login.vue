<script setup lang="ts">
import { useForm } from "vee-validate";
import { useTranslate, T } from "@tolgee/vue";
import Toast from "primevue/toast";
import * as yup from "yup";

definePageMeta({
  middleware: ["sanctum:guest"],
});

const { t } = useTranslate();
const toast = useToast();
const { login } = useSanctumAuth();

const { handleSubmit } = useForm({
  validationSchema: yup.object({
    email: yup
      .string()
      .email(t.value("form.input.email.error"))
      .required(t.value("form.input.required")),
    password: yup.string().required(t.value("form.input.required")),
  }),
});

const onSubmit = handleSubmit((values) => {
  loginUser({
    email: values.email as string,
    password: values.password as string,
  });
});

interface User {
  email: string;
  password: string;
}
/**
 * use the sanctum client to login a user
 * shows toast messages if success/error
 * @param {User} userData
 */
async function loginUser(userData: User) {
  console.log(userData);
  const userCredentials = {
    password: userData.password,
    email: userData.email,
  };

  toast.add({
    severity: "info",
    summary: t.value("common.toast.info.heading"),
    detail: t.value("form.login.toast.info"),
    life: 3000,
  });
  try {
    await login(userCredentials);
    toast.add({
      severity: "success",
      summary: t.value("form.registration.toast.success.heading"),
      detail: t.value("form.registration.toast.success"),
      life: 3000,
    });
    await navigateTo("/dashboard");
  } catch (error: any) {
    if (error.response?.status === 422) {
      toast.add({
        severity: "error",
        summary: t.value("common.toast.error.heading"),
        detail: t.value("form.login.toast.error"),
        life: 3000,
      });
      return;
    }
  }
}
</script>

<template>
  <Toast />
  <div
    class="w-full flex justify-center items-center font-nunito dark:bg-background-dark"
  >
    <div class="xl:w-1/3 md:w-1/4 sm:w-0 w-0 h-[90vh] dark:background-dark">
      <SvgCloud
        class="xl:w-[50%] md:w-[80%] w-0 object-none overflow-hidden mt-[20vh] z-50 dark:fill-clouds-bg -ml-24"
      />
      <SvgCloud
        class="absolute xl:w-[20%] md:w-[25%] md:left-[18vh] left-[35vh] w-0 object-none overflow-hidden top-[49vh] z-30"
      />
      <div class="overflow-hidden">
        <SvgCloudReversed
          class="xl:w-[45%] md:w-[70%] w-0 object-none overflow-hidden mt-[55vh] z-50 dark:fill-clouds-bg"
        />
        <SvgBalloon
          class="absolute xl:w-[4%] md:w-[5%] md:left-[28vh] w-0 top-[45vh] left-[45vh] z-0"
        />
        <SvgBalloon
          class="absolute xl:w-[7%] md:w-[8%] left-[68vh] w-0 top-12"
        />
      </div>
    </div>
    <div
      class="relative overflow-hidden flex xl:w-1/3 md:w-2/4 sm:w-full items-center h-[90vh] justify-center w-full z-20"
    >
      <div
        class="xl:flex xl:items-center xl:justify-center md:flex md:items-center md:justify-center text-center mt-6 sm:w-3/4 w-full h-[75vh] z-20"
      >
        <fieldset
          id="outerBlock"
          class="w-full h-auto px-3 py-2 pl-2 bg-surface dark:bg-surface-dark rounded-3xl border-border border-2 shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 flex flex-col items-center"
        >
          <legend
            for="outerBlock"
            class="text-3xl text-center lg:text-left lg:ml-7 px-2 font-bold text-text dark:text-white"
          >
            Login
          </legend>
          <form @submit="onSubmit" class="w-5/6">
            <FormInput
              id="email"
              name="email"
              translationKey="form.input.email"
            />

            <FormPassword
              id="password"
              name="password"
              :feedbackStyle="true"
              translationKey="form.input.password"
            />

            <button
              class="rounded-2xl my-5 mt-4 bg-input border-cta-border border-2 py-2.5 px-6 font-nunito font-bold text-md hover:bg-cta-bg dark:bg-input-dark dark:text-white dark:hover:bg-cta-bg-dark"
            >
              <T keyName="form.button.login" />
            </button>
          </form>
          <NuxtLink
            to="/register"
            class="dark:text-white underline mt-auto font-nunito font-semibold my-1"
          >
            <T keyName="form.text.no_account" />
          </NuxtLink>
        </fieldset>
      </div>
      <SvgCloudReversed
        class="xl:w-0 md:w-0 sm:w-[30vh] w-[30vh] z-0 absolute top-[50vh] overflow-hidden -left-4"
      />
      <SvgBalloonWithPeople
        class="xl:w-0 md:w-0 sm:w-[30vh] w-[30vh] z-0 absolute sm:-right-12 -right-24 top-[30vh] overflow-hidden"
      />
    </div>
    <div
      class="xl:w-1/3 md:w-1/4 sm:w-0 w-0 h-[90vh] flex justify-center items-center"
    >
      <SvgBalloonWithPeople class="w-[60%]" />
    </div>
  </div>
</template>
