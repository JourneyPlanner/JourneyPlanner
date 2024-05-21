<template>
    <div>
        <div class="flex justify-end">
            <button
                class="mr-2 mt-5 rounded-xl border-2 border-dandelion-300 bg-natural-50 px-2 py-1 text-base font-semibold hover:bg-dandelion-200 dark:bg-natural-900 dark:text-natural-50 dark:hover:bg-pesto-600 md:mr-10 md:px-6"
                @click="$router.back()"
            >
                <T key-name="common.back" />
            </button>
        </div>
        <div class="font-nunito text-text dark:text-natural-50">
            <div class="mx-5 mt-2 flex flex-col sm:mx-20 lg:mx-52 xl:mx-96">
                <h4 class="mb-5 text-center text-3xl font-bold">
                    <T key-name="common.privacypolicy" />
                </h4>
                <h2 class="mt-1 text-2xl font-semibold">
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
                    href="https://www.mapbox.com/legal/privacy#product-privacy-policy"
                    target="_blank"
                    class="hover:text-calypso-400 hover:underline"
                >
                    https://www.mapbox.com/legal/privacy#product-privacy-policy
                </a>
                <p class="mb-2 mt-1">
                    <T key-name="privacyPolicy.mapBox.EU" />
                </p>
                <a
                    href="https://www.dataprivacyframework.gov "
                    target="_blank"
                    class="hover:text-calypso-400 hover:underline"
                >
                    https://www.dataprivacyframework.gov
                </a>
                <h2 class="mt-5 text-2xl font-semibold">
                    <T key-name="privacy.cloudFlare.title" />
                </h2>
                <p class="mb-2 mt-1">
                    <T key-name="privacyPolicy.cloudFlare" />
                </p>
                <ol>
                    <li>
                        <T key-name="privacy.cloudflare.ip" />
                    </li>
                    <li>
                        <T key-name="privacy.cloudflare.website" />
                    </li>
                    <li>
                        <T key-name="privacy.cloudflare.browser" />
                    </li>
                    <li>
                        <T key-name="privacy.cloudflare.os" />
                    </li>
                    <li>
                        <T key-name="privacy.cloudflare.referrer" />
                    </li>
                    <li>
                        <T key-name="privacy.cloudflare.time" />
                    </li>
                    <li>
                        <T key-name="privacy.cloudflare.frequency" />
                    </li>
                </ol>
                <p class="mb-1 mt-2">
                    <T key-name="privacy.cloudflare.cookies" />
                </p>
                <a
                    href="https://www.cloudflare.com/privacypolicy/"
                    target="_blank"
                    class="mb-2 hover:text-calypso-400 hover:underline"
                >
                    https://www.cloudflare.com/privacypolicy/
                </a>
                <p>
                    <T key-name="privacy.cloudflare.cookies.extended" />
                </p>
                <p class="mb-2 mt-1">
                    <T key-name="privacy.cloudflare.eu" />
                </p>
                <a
                    href="https://www.dataprivacyframework.gov "
                    target="_blank"
                    class="hover:text-calypso-400 hover:underline"
                >
                    https://www.dataprivacyframework.gov
                </a>
                <h2 class="mt-5 text-2xl font-semibold">
                    <T key-name="privacy.rights.title" />
                </h2>
                <p class="mb-2 mt-1">
                    <T key-name="privacy.rights.text" />
                </p>
                <ol>
                    <li>
                        <T key-name="privacy.rights.information" />
                    </li>
                    <li>
                        <T key-name="privacy.rights.deletion" />
                    </li>
                    <li>
                        <T key-name="privacy.rights.correction" />
                    </li>
                    <li>
                        <T key-name="privacy.rights.portability" />
                    </li>
                    <li>
                        <T key-name="privacy.rights.revocation" />
                    </li>
                    <li>
                        <T key-name="privacy.rights.restriction" />
                    </li>
                </ol>
                <p class="mt-2">
                    <T key-name="privacy.rights.complaint" />
                </p>
                <h2 class="mt-5 text-2xl font-semibold">
                    <T key-name="privacy.contact.title" />
                </h2>
                <p class="mt-1">
                    <b> <T key-name="imprint.operator.title" />: </b>
                    <T key-name="imprint.operator.value" />
                </p>
                <p>
                    <b> <T key-name="imprint.contact.phone.title" />: </b>
                    <a
                        class="hover:text-calypso-400 hover:underline"
                        href="tel:+43 681 20301077"
                    >
                        +43 681 20301077
                    </a>
                    <br />
                    <b> <T key-name="imprint.contact.email.title" />: </b>
                    <a
                        class="hover:text-calypso-400 hover:underline"
                        href="mailto:raven.burkard@journeyplanner.io"
                    >
                        raven.burkard@journeyplanner.io
                    </a>
                </p>
                <p class="mt-5 text-sm">
                    <T key-name="privacy.source" />
                    <a
                        href="https://www.ortner-rechtsanwalt.at/strafrecht-festnahme-verhaftung/"
                        target="_blank"
                        class="ml-1 hover:text-calypso-400 hover:underline"
                        >Rechtsanwalt Wien Strafrecht</a
                    >
                </p>
            </div>
        </div>
    </div>
</template>
