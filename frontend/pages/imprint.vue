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
                <h4 class="text-center text-3xl font-bold">
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
                        href="tel:+43 681 20301077"
                    >
                        <T key-name="imprint.contact.phone.value" />
                    </a>
                    <br />
                    <T key-name="imprint.contact.email.title" />:
                    <a
                        class="hover:text-link hover:underline"
                        href="mailto:raven.burkard@journeyplanner.io"
                    >
                        <T key-name="imprint.contact.email.value" />
                    </a>
                </p>

                <p>
                    <b> <T key-name="imprint.legislation.title" />: </b>
                    <a
                        href="www.ris.bka.gv.at"
                        target="_blank"
                        class="hover:text-link hover:underline"
                    >
                        <T key-name="www.ris.bka.gv.at" />
                    </a>
                </p>
                <p>
                    <b> <T key-name="imprint.dispute.title" />: </b><br />
                    <T key-name="imprint.dispute.value" />:
                    <a
                        href="http://ec.europa.eu/odr"
                        target="_blank"
                        class="hover:text-link hover:underline"
                        >http://ec.europa.eu/odr
                    </a>
                </p>
                <p>
                    <b> <T key-name="imprint.disclaimer.title" />: </b><br />
                    <T key-name="imprint.disclaimer.value" />
                </p>
                <p>
                    <b> <T key-name="imprint.purpose.title" /> </b>:
                    <T key-name="imprint.purpose.value" />
                </p>
                <p class="mt-3">
                    <T key-name="imprint.thirdparty.fonts" />
                    <a
                        href="https://fonts.google.com/"
                        target="_blank"
                        class="hover:text-link hover:underline"
                        >Google Fonts
                    </a>
                </p>
                <p>
                    <T key-name="imprint.thirdparty.icons" />
                    <a
                        href="https://github.com/primefaces/primeicons"
                        target="_blank"
                        class="hover:text-link hover:underline"
                        >PrimeIcons
                    </a>
                </p>
                <p>
                    <T key-name="imprint.thirdparty.illustrations" />
                    <a
                        href="https://undraw.co/"
                        target="_blank"
                        class="hover:text-link hover:underline"
                        >undraw.co
                    </a>
                </p>
            </div>
        </div>
    </div>
</template>
