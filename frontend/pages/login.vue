<script setup lang="ts">
import { useForm } from "vee-validate";
import { useTranslate, T, useTolgee } from "@tolgee/vue";
import Toast from "primevue/toast";
import * as yup from "yup";

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
  registerUser(values);
});

async function registerUser(userData: Object) {
  const userCredentials = {
    email: userData.email,
    password: userData.password,
  };

  await login(userCredentials);

  if (status.value === "success") {
    toast.add({
      severity: "success",
      summary: t.value("form.registration.toast.success.heading"),
      detail: t.value("form.registration.toast.success"),
      life: 3000,
    });
  } else if (error.value.statusCode === 422) {
    toast.add({
      severity: "error",
      summary: t.value("form.registration.toast.error.heading"),
      detail: t.value("form.registration.toast.error"),
      life: 3000,
    });
  } else {
    toast.add({
      severity: "error",
      summary: t.value("form.registration.toast.error.heading"),
      detail: T.value("common.error.unknown"),
      life: 3000,
    });
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
        class="xl:w-[60%] md:w-[90%] w-0 object-none overflow-hidden mt-40 z-10 dark:fill-clouds-bg -ml-28"
      />
      <SvgCloud
        class="xl:w-[50%] md:w-[80%] w-0 object-none overflow-hidden mt-32 z-10 ml-[12vw]"
      />
      <div class="overflow-hidden">
        <SvgCloudReversed
          class="xl:w-[45%] md:w-[70%] w-0 object-none overflow-hidden mt-40 z-10 dark:fill-clouds-bg"
        />
        <SvgBalloon
          class="absolute xl:w-[5%] md:w-[5%] w-0 top-[42vh] left-[30vh] -z-10"
        />
        <SvgBalloon
          class="absolute xl:w-[7%] md:w-[8%] left-[68vh] w-0 top-12 -z-50"
        />
      </div>
    </div>
    <div
      class="relative overflow-hidden flex xl:w-1/3 md:w-2/4 sm:w-full items-center h-[90vh] justify-center w-full"
    >
      <div
        class="xl:flex xl:items-center xl:justify-center md:flex md:items-center md:justify-center text-center mt-6 sm:w-3/4 w-full h-3/4 z-20"
      >
        <fieldset
          id="outerBlock"
          class="w-full h-auto px-3 py-2 pl-2 bg-surface dark:bg-surface-dark rounded-3xl border-border border-2 shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 flex flex-col items-center"
        >
          <legend
            for="outerBlock"
            class="font-nunito font-bold text-3xl ml-4 px-3 dark:text-white"
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
              :feedback="false"
              translationKey="form.input.password"
            />

            <button
              class="rounded-2xl my-6 bg-input border-cta-border border-2 py-2 px-10 font-nunito font-bold text-2xl hover:bg-cta-bg dark:bg-input-dark dark:text-white dark:hover:bg-cta-bg-dark"
            >
              <T keyName="form.button.login" />
            </button>
          </form>
          <NuxtLink
            to="/registration"
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
        class="xl:w-0 md:w-0 sm:w-[30vh] w-[30vh] z-0 absolute sm:-right-12 -right-24 top-72 overflow-hidden"
      />
    </div>
    <div
      class="xl:w-1/3 md:w-1/4 sm:w-0 w-0 h-[90vh] flex justify-center items-center"
    >
      <SvgBalloonWithPeople class="w-[60%]" />
    </div>
  </div>
</template>
