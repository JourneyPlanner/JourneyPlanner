<script setup lang="ts">
import { useTranslate } from "@tolgee/vue";
import { format } from "date-fns";
import { v4 as uuidv4 } from "uuid";
import { useForm } from "vee-validate";
import * as yup from "yup";

const { t } = useTranslate();
const route = useRoute();
const router = useRouter();
const client = useSanctumClient();
const { isAuthenticated } = useSanctumAuth();
const toast = useToast();
const store = useJourneysStore();

console.log(route.query.creationType);

const cancel = ref("/dashboard");
const journeyInvite = ref(uuidv4());
const journeyInviteLink = ref("");
const loading = ref(false);
const openedTemplate = ref<Template>();
const isTemplatePopupVisible = ref(false);
const templateDestinationInput = ref("");
const templateDestinationName = ref("");
const suggestions = ref<Template[]>([]);
const journeyName = ref();
const journeyRange = ref();
const creationType = ref();

if (route.query.creationType == "template") {
    creationType.value = route.query.creationType;
    cancel.value = router.options.history.state.back as string;
}

const title = t.value("title.journey.create");
useHead({
    title: `${title} | JourneyPlanner`,
});

if (!isAuthenticated.value) {
    if (localStorage.getItem("JP_guest_journey_id") !== null) {
        toast.add({
            severity: "info",
            summary: t.value("journey.unlock.create.heading"),
            detail: t.value("journey.unlock.create.detail"),
            life: 2000,
        });
        await navigateTo(
            "/journey/" + localStorage.getItem("JP_guest_journey_id"),
        );
    }
    cancel.value = "/";
} else {
    journeyInviteLink.value =
        window.location.origin + "/invite/" + journeyInvite.value;
}

/**
 * Fetches all templates from the backend
 * stores response in templates ref
 */
const {
    data: templateData,
    status,
    refresh,
} = await useAsyncData("suggestions", () =>
    client(
        `/api/template?per_page=3&template_destination_input=${encodeURIComponent(templateDestinationInput.value)}&template_destination_name=${encodeURIComponent(templateDestinationName.value)}`,
    ),
);

watch(
    templateData,
    () => {
        if (templateData.value) {
            suggestions.value = templateData.value.data;
        }
    },
    { immediate: true },
);

/**
 * form validation
 * when submitting form, fields are checked for validation
 */
const { handleSubmit } = useForm({
    validationSchema: yup.object({
        journeyName: yup
            .string()
            .required(t.value("form.error.journey.name"))
            .matches(/^(?!\s+$).*/, t.value("form.error.journey.name"))
            .label(t.value("form.input.journey.name")),
        journeyDestination: yup
            .string()
            .min(1, t.value("form.error.journey.destination"))
            .required(t.value("form.error.journey.destination"))
            .matches(/^(?!\s+$).*/, t.value("form.error.journey.destination"))
            .label(t.value("form.input.journey.destination")),
        journeyRange: yup
            .array()
            .of(
                yup
                    .date()
                    .required(t.value("form.error.journey.dates"))
                    .label(t.value("form.input.journey.dates")),
            )
            .required(t.value("form.error.journey.dates"))
            .label(t.value("form.input.journey.dates")),
    }),
});

/**
 * form submit
 * when submitting the form, values are checked for validation with handleSubmit
 * and then a journey object is created and sent to the backend
 */
const onSubmit = handleSubmit(async (values) => {
    loading.value = true;
    toast.add({
        severity: "info",
        summary: t.value("common.toast.info.heading"),
        detail: t.value("form.journey.toast.info"),
        life: 6000,
    });

    const name = values.journeyName;
    const destination = values.journeyDestination;
    const from = format(values.journeyRange[0], "yyyy-MM-dd");
    const to = format(values.journeyRange[1], "yyyy-MM-dd");
    const invite = journeyInvite.value;
    const mapbox_full_address = values.mapbox?.properties.full_address;
    const mapbox_id = values.mapbox?.properties.mapbox_id;

    const journey = {
        name,
        destination,
        mapbox_full_address: mapbox_full_address,
        mapbox_id: mapbox_id,
        from,
        to,
        invite,
        role: 1,
    };

    await client("/api/journey", {
        method: "POST",
        body: journey,
        async onResponse({ response }) {
            if (response.ok) {
                toast.add({
                    severity: "success",
                    summary: t.value("form.journey.toast.success.heading"),
                    detail: t.value("form.journey.toast.success"),
                    life: 3000,
                });
                response._data.journey.role = 1;
                store.addJourney(response._data.journey);
                if (!isAuthenticated.value) {
                    localStorage.setItem(
                        "JP_guest_journey_id",
                        response._data.journey.id,
                    );
                }
                await navigateTo("/journey/" + response._data.journey.id);
                loading.value = false;
            }
        },
        async onResponseError() {
            toast.add({
                severity: "error",
                summary: t.value("common.toast.error.heading"),
                detail: t.value("common.error.unknown"),
                life: 6000,
            });
            loading.value = false;
        },
    });
});

function copyToClipboard() {
    navigator.clipboard.writeText(journeyInviteLink.value);
    toast.add({
        severity: "info",
        summary: t.value("common.toast.info.heading"),
        detail: t.value("common.invite.toast.info"),
        life: 2000,
    });
}

const openTemplateDialog = (template: Template) => {
    openedTemplate.value = template;
    isTemplatePopupVisible.value = true;
};

const changeAddress = debounce((inputValue: unknown) => {
    templateDestinationInput.value = inputValue as string;
    refresh();
});

function retrievedAddress(inputValue: string, name: string) {
    templateDestinationInput.value = inputValue;
    templateDestinationName.value = name;
    refresh();
}

function changeName(newName: string) {
    journeyName.value = newName;
}

function changeRange(newRange: Date[]) {
    journeyRange.value = [];
    newRange
        .filter((value) => value != null)
        .forEach((element: Date) => {
            journeyRange.value?.push(element.toISOString());
        });
}
</script>

<template>
    <div>
        <div>
            <SvgCloud
                class="invisible absolute left-[22%] top-20 h-20 overflow-hidden object-none md:visible"
            />
            <SvgCloud
                class="invisible absolute right-[20%] top-36 h-16 overflow-hidden object-none md:visible"
            />
        </div>
        <div class="absolute left-4 top-4 z-50">
            <NuxtLink :to="cancel">
                <SvgLogoHorizontalBlue class="w-44 lg:w-56" />
            </NuxtLink>
        </div>
        <div class="relative flex flex-col">
            <div
                class="mt-10 flex items-center justify-center px-4 font-nunito sm:mt-8"
            >
                <fieldset
                    id="create-journey"
                    class="w-full rounded-2xl border-2 border-calypso-300 bg-calypso-200 bg-opacity-30 px-2 shadow-sm dark:bg-gothic-300 dark:bg-opacity-20 xs:px-5 sm:w-1/4 md:w-1/3"
                >
                    <legend
                        for="create-journey"
                        class="text-center text-2xl font-bold text-text dark:text-natural-50 md:mb-5 lg:text-3xl xl:ml-4 xl:px-2 xl:text-left"
                    >
                        <T
                            :key-name="
                                creationType != 'template'
                                    ? 'form.header.journey.create'
                                    : 'form.header.template.create'
                            "
                        />
                    </legend>
                    <form class="px-1 lg:px-5" @submit="onSubmit">
                        <FormInput
                            id="journey-name"
                            name="journeyName"
                            :translation-key="
                                creationType != 'template'
                                    ? 'form.input.journey.name'
                                    : 'form.input.template.name'
                            "
                            @change-input="changeName"
                        />
                        <FormAddressInput
                            id="journey-destination"
                            name="journeyDestination"
                            :placeholder="
                                creationType != 'template'
                                    ? t('form.input.journey.destination')
                                    : t('form.input.template.destination')
                            "
                            class="relative mb-4"
                            custom-class=".SearchIcon {visibility: hidden;} .Input {height: fit-content; font-weight: 700; padding-right: 0.625rem; padding-top: 0.625rem; padding-bottom: 0.625rem; padding-left: 0.625rem;} .Input::placeholder {font-family: Nunito; font-weight: 400; font-size: 0.875rem; line-height: 1.25rem;}"
                            @change-address="changeAddress"
                            @retrieved-address="retrievedAddress"
                        />
                        <div class="w-full xs:w-5/6 xl:w-2/3">
                            <FormCalendarSelect
                                id="journey-range-calendar"
                                name="journeyRange"
                                class="w-full"
                                translation-key="form.input.journey.dates"
                                @change-input="changeRange"
                            />
                        </div>
                        <Divider
                            v-if="isAuthenticated && creationType != 'template'"
                            type="solid"
                            class="border-10 mt-2 border pt-0 text-calypso-300 dark:text-calypso-400"
                        />

                        <div
                            v-if="isAuthenticated && creationType != 'template'"
                            class="relative my-2 flex"
                        >
                            <input
                                id="journey-invite"
                                v-model="journeyInviteLink"
                                type="text"
                                name="journey-invite"
                                disabled
                                class="peer w-[90%] rounded-lg border-2 border-calypso-300 bg-natural-100 px-2.5 pb-1 pt-4 text-base font-medium text-natural-500 focus:outline-none focus:ring-1 dark:border-calypso-400 dark:bg-natural-700 dark:text-natural-300"
                                placeholder=" "
                            />
                            <label
                                for="journey-invite"
                                class="absolute left-0 ml-1.5 mt-1 -translate-y-0.5 px-1 text-xs text-calypso-500 transition-transform duration-100 ease-linear peer-placeholder-shown:translate-y-2.5 peer-placeholder-shown:text-sm peer-placeholder-shown:text-natural-400 peer-focus:ml-1.5 peer-focus:-translate-y-0.5 peer-focus:px-1 peer-focus:text-xs peer-focus:text-calypso-500 dark:text-calypso-300"
                            >
                                <T key-name="form.input.journey.invite" />
                            </label>
                            <div class="flex items-center justify-center">
                                <button
                                    type="button"
                                    class="ml-2 flex h-10 w-10 items-center justify-center rounded-full border-2 border-dandelion-300 bg-natural-50 hover:bg-dandelion-200 dark:bg-natural-800 dark:hover:bg-pesto-600"
                                    @click="copyToClipboard"
                                >
                                    <SvgCopy class="w-4" />
                                </button>
                            </div>
                        </div>
                        <div class="mb-3 mt-6 flex justify-between gap-5">
                            <NuxtLink :to="cancel">
                                <button
                                    type="button"
                                    class="rounded-xl border-2 border-mahagony-500 bg-natural-50 px-7 py-1 font-bold text-text hover:bg-mahagony-300 dark:bg-natural-800 dark:text-natural-50 dark:hover:bg-mahagony-500 dark:hover:bg-opacity-30"
                                >
                                    <T key-name="common.button.cancel" />
                                </button>
                            </NuxtLink>

                            <Button
                                :loading="loading"
                                type="submit"
                                :label="t('common.button.create')"
                                :pt="{
                                    root: {
                                        class: 'flex items-center justify-center',
                                    },
                                    label: {
                                        class: 'display-block flex-none font-bold font-nunito',
                                    },
                                }"
                                class="rounded-xl border-2 border-dandelion-300 bg-natural-50 px-7 py-1 font-bold text-text hover:bg-dandelion-200 dark:bg-natural-800 dark:text-natural-50 dark:hover:bg-pesto-600"
                            />
                        </div>
                    </form>
                </fieldset>
            </div>
            <div
                v-if="creationType != 'template'"
                class="mt-2 flex items-center justify-center px-4 font-nunito"
            >
                <div
                    id="template-section"
                    class="w-full rounded-xl border-2 border-natural-300 bg-natural-50 dark:border-natural-800 dark:bg-natural-900 sm:w-2/4 md:w-2/5"
                >
                    <h3
                        class="-m-0.5 ml-4 mt-0.5 text-lg font-semibold text-natural-800 dark:text-natural-200"
                    >
                        <T key-name="template.suggestions" />
                    </h3>
                    <div v-if="status === 'pending'" class="mt-1 flex w-full">
                        <ProgressSpinner class="w-10" />
                    </div>
                    <div
                        v-else-if="suggestions.length < 1"
                        class="mt-1 w-full text-center italic text-natural-800 dark:text-natural-200"
                    >
                        <T key-name="template.suggestions.none" />
                    </div>
                    <div
                        v-else
                        id="template-suggestions"
                        class="mb-1.5 mt-1 flex w-full flex-col"
                    >
                        <TemplateSuggestion
                            v-for="(template, index) in suggestions"
                            :key="template.id"
                            :index="index"
                            :template="template"
                            @open-template="openTemplateDialog(template)"
                        />
                    </div>
                    <div
                        v-if="isAuthenticated"
                        class="mb-0.5 mr-3 flex justify-end"
                    >
                        <NuxtLink
                            to="/dashboard?tab=templates"
                            class="group flex items-center gap-x-1 text-end text-natural-800 hover:text-calypso-600 dark:text-natural-200 dark:hover:text-calypso-300"
                        >
                            <span class="group-hover:underline">
                                <T key-name="template.all" />
                            </span>
                            <i
                                class="pi pi-chevron-right text-xs hover:no-underline"
                            />
                        </NuxtLink>
                    </div>
                </div>
            </div>
        </div>
        <SvgPeopleBackpackMap class="absolute bottom-0 hidden h-44 lg:block" />
        <SvgWomanSuitcaseLeft
            class="absolute bottom-0 hidden h-44 sm:block lg:right-44"
        />
        <SvgWomanSuitcaseRight
            class="absolute bottom-0 right-0 hidden h-44 sm:block"
        />
        <div class="z-50">
            <TemplatePopup
                v-if="openedTemplate"
                id="template-popup-create-new-journey"
                class="z-50"
                :template="openedTemplate"
                :is-template-dialog-visible="isTemplatePopupVisible"
                :name-prefill="journeyName"
                :date-prefill="journeyRange"
                @close="
                    isTemplatePopupVisible = false;
                    openedTemplate = undefined;
                "
            />
        </div>
    </div>
</template>
