<template>
    <div>
        <div class="flex justify-end">
            <button
                class="mr-2 mt-5 rounded-xl border-2 border-cta-border bg-input px-2 py-1 text-base font-semibold hover:bg-cta-bg dark:bg-input-dark dark:text-input dark:hover:bg-cta-bg-dark md:mr-10 md:px-6"
                @click="$router.back()"
            >
                <T key-name="common.back" />
            </button>
        </div>
        <div class="-mt-8 font-nunito text-text dark:text-input">
            <div class="mx-5 mt-7 flex flex-col sm:mx-20 lg:mx-52 xl:mx-96">
                <h4 class="mb-2 text-center text-3xl font-bold">
                    <T key-name="common.privacyPolicy" />
                </h4>
                <h2 class="mt-5 text-2xl font-semibold">
                    <T key-name="privacy.informationDuty.title" />
                </h2>
                <p class="mb-2 mt-1">
                    <T key-name="privacyPolicy.informationDuty" />
                </p>
                <h2 class="mt-5 text-2xl font-semibold">
                    <T key-name="privacy.contact.title" />
                </h2>
                <p class="mb-2 mt-1">
                    <T key-name="privacyPolicy.contact" />
                </p>
                <h2 class="mt-5 text-2xl font-semibold">
                    <T key-name="privacy.mapBox.title" />
                </h2>
                <p class="mb-2 mt-1">
                    <T key-name="privacyPolicy.mapBox" />
                </p>
                <a
                    href="https://www.mapbox.com/legal/privacy#product-privacy-policy. "
                    target="_blank"
                    class="hover:text-link hover:underline"
                >
                    https://www.mapbox.com/legal/privacy#product-privacy-policy.
                </a>
                <p class="mb-2 mt-1">
                    <T key-name="privacyPolicy.mapBox.EU" />
                </p>
                <a
                    href="https://www.dataprivacyframework.gov "
                    target="_blank"
                    class="hover:text-link hover:underline"
                >
                    https://www.dataprivacyframework.gov
                </a>
                <h2 class="mt-5 text-2xl font-semibold">
                    <T key-name="privacy.cloudFlare.title" />
                </h2>
                <p class="mb-2 mt-1">
                    <T key-name="privacyPolicy.cloudFlare" />
                </p>
                <a
                    href="https://www.mapbox.com/legal/privacy#product-privacy-policy. "
                    target="_blank"
                    class="hover:text-link hover:underline"
                >
                    https://www.mapbox.com/legal/privacy#product-privacy-policy.
                </a>
                <p class="mb-2 mt-1">
                    <T key-name="privacyPolicy.cloudFlare.EU" />
                </p>
                <a
                    href="https://www.dataprivacyframework.gov "
                    target="_blank"
                    class="hover:text-link hover:underline"
                >
                    https://www.dataprivacyframework.gov
                </a>

                <p class="mt-1">
                    <b> <T key-name="imprint.operator.title" />: </b>
                    <T key-name="imprint.operator.value" />
                </p>
                <p class="mt-1">
                    <b> <T key-name="imprint.address.title" />: </b>
                    <T key-name="imprint.address.value" />
                </p>
                <p class="mt-1">
                    <b> <T key-name="imprint.contact.title" />: </b> <br />
                    <T key-name="imprint.contact.phone.title" />:
                    <a
                        class="hover:text-link hover:underline"
                        href="tel:+43 681 20301077"
                    >
                        +43 681 20301077
                    </a>
                    <br />
                    <T key-name="imprint.contact.email.title" />:
                    <a
                        class="hover:text-link hover:underline"
                        href="mailto:raven.burkard@journeyplanner.io"
                    >
                        raven.burkard@journeyplanner.io
                    </a>
                </p>

                <p class="mt-1">
                    <b> <T key-name="imprint.legislation.title" />: </b>
                    <a
                        href="https://www.ris.bka.gv.at"
                        target="_blank"
                        class="hover:text-link hover:underline"
                    >
                        https://www.ris.bka.gv.at
                    </a>
                </p>
                <p class="mt-1">
                    <b> <T key-name="imprint.dispute.title" />: </b><br />
                    <T key-name="imprint.dispute.value" />:
                    <a
                        href="https://ec.europa.eu/odr"
                        target="_blank"
                        class="hover:text-link hover:underline"
                        >https://ec.europa.eu/odr
                    </a>
                </p>
                <p class="mt-1">
                    <b> <T key-name="imprint.disclaimer.title" />: </b><br />
                    <T key-name="imprint.disclaimer.value" />
                </p>
                <p class="mt-1">
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
