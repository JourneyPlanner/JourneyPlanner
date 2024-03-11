<script setup lang="ts">
import { useForm } from "vee-validate";
import { useTranslate, T, useTolgee } from "@tolgee/vue";
import Toast from "primevue/toast";
import * as yup from "yup";

const { t } = useTranslate();
const toast = useToast();

const { handleSubmit } = useForm({
  validationSchema: yup.object({
    email: yup
      .string()
      .email(t.value("form.input.email.error"))
      .required(t.value("form.input.required")),
    name: yup.string().required(t.value("form.input.required")),
    password: yup
      .string()
      .min(8, t.value("form.input.password.error"))
      .required(t.value("form.input.required")),
    password_confirmation: yup
      .string()
      .oneOf([yup.ref("password")], t.value("form.input.password.repeat.error"))
      .required(t.value("form.input.required")),
    terms: yup
      .boolean()
      .oneOf([true], t.value("form.input.terms.error"))
      .required(t.value("form.input.terms.error")),
  }),
});

const onSubmit = handleSubmit((values) => {
  registerUser(values);
});

async function registerUser(userData: Object) {
  const client = useSanctumClient();
  const { error, status } = await useAsyncData("users", () =>
    client("/register", {
      method: "POST",
      body: userData,
    })
  );
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
    <div class="xl:w-1/3 md:w-1/4 sm:w-0 h-[90vh] dark:background-dark">
      <SvgAircraft
        class="xl:w-[230%] md:w-[350%] w-0 object-none -ml-[20vw] overflow-hidden mt-12 z-0"
      />
    </div>
    <div
      class="flex xl:w-1/3 md:w-2/4 sm:w-full items-center h-[90vh] justify-center w-full"
    >
      <div class="text-center mt-6 sm:w-3/4 w-full h-3/4 z-20">
        <fieldset
          id="outerBlock"
          class="h-auto px-3 py-2 pl-2 bg-surface dark:bg-surface-dark rounded-3xl border-border border-2 shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 flex flex-col items-center"
        >
          <legend
            for="outerBlock"
            class="font-nunito font-bold text-3xl ml-2 px-3 dark:text-white"
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
            <FormCheckbox id="terms" name="terms" />

            <button
              class="rounded-2xl my-3 bg-input border-cta-border border-2 py-2 px-10 font-nunito font-bold text-2xl hover:bg-cta-bg dark:bg-input-dark dark:text-white dark:hover:bg-cta-bg-dark"
            >
              <T keyName="form.button.register" />
            </button>
          </form>
          <NuxtLink
            to="/login"
            class="dark:text-white underline mt-auto font-nunito font-semibold my-1"
          >
            <T keyName="form.text.already_account" />
          </NuxtLink>
        </fieldset>
      </div>
    </div>
    <div class="xl:w-1/3 md:w-1/4 sm:w-0 w-0 h-[90vh]">
      <SvgCloud
        class="xl:w-[60%] md:w-[90%] w-0 object-none overflow-hidden mt-64 z-0 dark:fill-clouds-bg"
      />
      <SvgCloud
        class="xl:w-[50%] md:w-[80%] w-0 object-none overflow-hidden mt-32 z-0 -ml-[12vw]"
      />
      <div class="overflow-hidden">
        <SvgCloud
          class="xl:w-[60%] md:w-[90%] w-0 object-none overflow-hidden mt-16 z-0 ml-[20vw] overflow-x-hidden"
        />
      </div>
    </div>
  </div>
</template>
