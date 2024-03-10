<script setup lang="ts">
import { useForm } from "vee-validate";
import { useTranslate, T } from "@tolgee/vue";
import * as yup from "yup";

const { t } = useTranslate();
const client = useSanctumClient();
const terms = ref(false);
const { value } = useField("terms");
const termsError = ref(false);

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
      .oneOf(
        [yup.ref("password")],
        t.value("form.input.password.repeat.error")
      ),
    terms: yup.boolean().oneOf([true]),
  }),
});

const onSubmit = handleSubmit((values) => {
  if (terms.value === false) {
    termsError.value = true;
    return;
  }
  registerUser(values);
});

function onInvalidSubmit() {
  console.log("Invalid submit");
}

async function registerUser(userData: Object) {
  const { data, pending, error, refresh } = await useAsyncData("users", () =>
    client("/register", {
      method: "POST",
      body: userData,
    })
  );
}
</script>

<template>
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
            <div class="flex w-full items-center text-left mt-2">
              <div class="">
                <label
                  class="relative flex cursor-pointer items-center p-1 rounded-md"
                >
                  <input
                    type="checkbox"
                    id="terms"
                    name="terms"
                    v-model="terms"
                    class="peer cursor-pointer appearance-none relative h-5 w-5 bg-input dark:bg-input-dark border-2 border-border transition-all checked:border-border checked:bg-border checked:dark:bg-border rounded-md"
                  />
                  <div
                    class="pointer-events-none absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-white opacity-0 transition-opacity peer-checked:opacity-100"
                  >
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      class="h-3.5 w-3.5 fill-background"
                      viewBox="0 0 20 20"
                      stroke-width="1"
                    >
                      <path
                        fill-rule="evenodd"
                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                        clip-rule="evenodd"
                      ></path>
                    </svg>
                  </div>
                </label>
              </div>

              <label
                for="link-checkbox"
                class="ms-2 text-sm text-gray-900 dark:text-gray-300 select-none dark:text-white font-nunito font-light"
                ><T keyName="form.input.text.privacypolicy" />
                <NuxtLink
                  to="/privacypolicy"
                  class="text-input-label underline dark:text-border"
                  ><T keyName="common.privacypolicy"
                /></NuxtLink>
              </label>
            </div>
            <div class="h-3 text-left">
              <span
                v-if="termsError"
                class="text-error dark:text-error-dark text-left text-xs"
                >Terms not accepted
              </span>
            </div>

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
