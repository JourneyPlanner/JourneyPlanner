<script lang="ts" setup>
import {reactive, ref} from "vue";
//@ts-ignore
import {supabase} from "@/lib/supabaseClient";
import {useVuelidate} from '@vuelidate/core'
import {required, email} from '@vuelidate/validators'
import Footer from "@/components/Footer.vue";
import PersonWalkingIllustration from "@/components/illustrations/PersonWalkingIllustration.vue";
import BackToHomeButton from "@/components/buttons/BackToHomeButton.vue";
import router from "@/router";
import {useRoute} from "vue-router";

import Toast from 'primevue/toast';
import {useToast} from 'primevue/usetoast';

const registered = ref(false);
const toast = useToast();

if (useRoute().query.registered == "true") {
  registered.value = true;
}

const unauthorized = ref(false);
if (useRoute().query.unauthorized == "true") {
  unauthorized.value = true;
}

const redirect = useRoute().query.redirect;
if (redirect) {
  unauthorized.value = true;
}

const state = reactive({
  password: '',
  contact: {
    email: ''
  }
})

const rules = {
  password: {required},
  contact: {
    email: {required, email: email} // Matches state.contact.email
  }
}

const v$ = useVuelidate(rules, state)

async function signIn() {
  const {data, error} = await supabase.auth.signInWithPassword(
      {
        email: state.contact.email,
        password: state.password
      }
  )
  if (error) {
    toast.add({
      severity: 'error',
      summary: 'Fehler beim einloggen',
      detail: 'Du hast einen Falschen Benutzernamen oder ein falsches Passwort angegben.',
      life: 5000
    });
  } else {
    toast.add({
      severity: 'success',
      summary: 'Angemeldet!',
      detail: 'Du wirst gleich weitergeleitet...',
      life: 1000
    });
  }

  if (error) {
    console.log(error);
  } else {
    if (redirect) {
      await router.push(redirect + '');
    }
    await router.push("/dashboard");
  }
}

</script>
<template>
  <Toast/>

  <div class="relative text-text-black">
    <div class="about bg-background flex">
      <div class="w-1/2 items-center justify-center overflow-x-hidden">
        <PersonWalkingIllustration class="h-[85vh] ml-[-25%] mt-[5%]"/>
      </div>
      <div id="firsHalf" class="xl:w-1/2 md:w-2/3 sm:w-[100%] items-center justify-center flex flex-col">
        <Dialog v-model:visible="registered" modal header="Bestätige noch deine E-Mail-Adresse!"
                :style="{ width: '50rem' }">
          <p>
            Deine Registrierung ist fast abgeschlossen. Bitte bestätige noch deine E-Mail-Adresse.
            Solltest du keine Mail von uns bekommen haben, schaue bitte in deinem Spamordner nach.
            Solltest du auf Probleme stoßen, kannst du uns unter
            <a class="underline" href="mailto:contact@journeyplanner.io">contact@journeyplanner.io</a> erreichen.
          </p>
        </Dialog>

        <Dialog v-model:visible="unauthorized" modal header="Bitte logge dich ein!"
                :style="{ width: '50rem' }" class="font-nunito text-text-black text-xl">
          <p class="font-nunito-sans text-text-black">
            Bitte melde dich an oder
            <RouterLink to="register" class="underline">erstelle ein Konto.</RouterLink>
            <br>
            Solltest du auf Probleme stoßen, kannst du uns unter
            <a class="underline" href="mailto:contact@journeyplanner.io">contact@journeyplanner.io</a> erreichen.
          </p>
        </Dialog>

        <BackToHomeButton class="absolute top-[2%] w-2/12 left-[82vw] pr-4"/>
        <div class="form xl:w-1/2 md:w-[80%] sm:w-[80%]">
          <h1 class="xl:text-3xl md:text-3xl sm:text-3xl pl-6.1538em font-nunito pt-[15%]">Login</h1>
          <form class="bg-primary rounded-2xl xl:w-[25vw] md:w-[50vw] sm:w-[75vw]">
            <div class="inside flex flex-col pl-8 ">
              <h2 class="col-start-1 text-xl font-nunito font-semibold">E-Mail</h2>
              <input v-model="v$.contact.email.$model" class="font-nunito text-xl" placeholder="E-Mail eingeben">
              <p v-if="v$.contact.email.$error" class="text-delete text-base font-nunito">Nicht das richtige Format</p>
              <h2 class="text-xl font-nunito font-semibold">Passwort</h2>
              <input v-model=v$.password.$model type="password" class="font-nunito text-xl"
                     placeholder="Passwort eingeben" @keyup.enter="signIn">
              <div class="my-3">
                <button :disabled="v$.$invalid" type="button"
                        class="disabled:opacity-50 bg-call-to-action border-call-to-action rounded-3xl font-nunito text-xl font-bold p-1.5 px-6 hover:opacity-80 shadow-lg"
                        @click="signIn">Login
                </button>
                <RouterLink
                    class="font-nunito text-base font-bold px-2 break-after-all underline underline-offset-2 lg:ml-14"
                    to="register">Noch kein Konto?
                </RouterLink>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
    <div class="justify-center text-center flex">
      <div class="w-1/2 h-1 items-center justify-center overflow-x-hidden">
      </div>
      <div class="w-1/2 items-center justify-center overflow-x-hidden">
        <Footer class=""/>
      </div>
    </div>
  </div>
</template>

<style scoped>
input {
  @apply flex w-[90%] border rounded border-none focus:outline-none focus:ring-2 focus:ring-call-to-action pl-1
}

h2 {
  @apply pt-[3%] text-left
}
</style>
