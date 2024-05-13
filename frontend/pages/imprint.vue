<script setup lang="ts">
import { useTranslate } from "@tolgee/vue";

const { t } = useTranslate();
</script>

<template>
    <div>
        <div class="flex justify-end">
            <button
                class="mr-10 mt-5 rounded-xl border-2 border-cta-border bg-input px-2 py-1 text-base font-semibold hover:bg-cta-bg dark:bg-input-dark dark:text-input dark:hover:bg-cta-bg-dark"
                @click="$router.back()"
            >
                <T key-name="common.back" />
            </button>
        </div>
        <div class="-mt-8 font-nunito text-text dark:text-input">
            <div class="mx-5 mt-10 flex flex-col sm:mx-20 lg:mx-52 xl:mx-96">
                <h4 class="text-center text-3xl">
                    <T key-name="common.imprint" />
                </h4>
                <p class="mt-4">
                    <T key-name="imprint.info" />
                </p>
                <p>
                    <b> <T key-name="imprint.operator.title" />: </b>
                    <T key-name="imprint.operator.value" />
                </p>
                <p>
                    <b> <T key-name="imprint.address.title" />: </b>
                    <T key-name="imprint.address.value" />
                </p>
                <p>
                    <b> <T key-name="imprint.contact.title" />: </b> <br />
                    <T key-name="imprint.contact.phone.title" />:
                    <a
                        class="hover:text-link hover:underline"
                        :href="'tel:' + t('imprint.contact.phone.value')"
                    >
                        <T key-name="imprint.contact.phone.value" />
                    </a>
                    <br />
                    <T key-name="imprint.contact.email.title" />:
                    <a
                        class="hover:text-link hover:underline"
                        :href="'mailto:' + t('imprint.contact.email.value')"
                    >
                        <T key-name="imprint.contact.email.value" />
                    </a>
                </p>

                <p>
                    <b> <T key-name="imprint.legislation.title" />: </b>
                    <a
                        :href="t('imprint.legislation.value')"
                        class="hover:text-link hover:underline"
                    >
                        <T key-name="imprint.legislation.value" />
                    </a>
                </p>
                <p>
                    <b> <T key-name="imprint.dispute.title" />: </b><br />
                    <T key-name="imprint.dispute.value" />:
                    <a
                        :href="t('imprint.dispute.link')"
                        class="hover:text-link hover:underline"
                    >
                        <T key-name="imprint.dispute.link" />
                    </a>
                </p>
                <p>
                    <b> <T key-name="imprint.disclaimer.title" />: </b><br />
                    <T key-name="imprint.disclaimer.value" />
                </p>
                <p>
                    <b>
                        <T key-name="imprint.purpose.title" /> </b
                    >:
                    <T key-name="imprint.purpose.value" />
                </p>
            </div>
        </div>
    </div>
</template>
