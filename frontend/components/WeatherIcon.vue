<script setup lang="ts">
const props = defineProps({
    weatherCode: { type: Number, required: true },
});
</script>
<template>
    <div>
        <SvgWeatherCodeSunny
            v-if="props.weatherCode == 0"
            class="block dark:hidden"
        />
        <SvgWeatherCodeCloudy
            v-if="
                props.weatherCode == 1 ||
                props.weatherCode == 2 ||
                props.weatherCode == 3
            "
            class="block dark:hidden"
        />
        <SvgWeatherCodeFoggy
            v-if="props.weatherCode == 45 || props.weatherCode == 48"
            class="block dark:hidden"
        />
        <SvgWeatherCodeDrizzle
            v-if="
                props.weatherCode == 51 ||
                props.weatherCode == 53 ||
                props.weatherCode == 55
            "
            class="block dark:hidden"
        />
        <SvgWeatherCodeDrizzleFreezing
            v-if="props.weatherCode == 56 || props.weatherCode == 57"
            class="block dark:hidden"
        />
        <SvgWeatherCodeRain
            v-if="
                props.weatherCode == 61 ||
                props.weatherCode == 63 ||
                props.weatherCode == 65
            "
            class="block dark:hidden"
        />
        <SvgWeatherCodeRainFreezing
            v-if="props.weatherCode == 66 || props.weatherCode == 67"
            class="block dark:hidden"
        />
        <SvgWeatherCodeSnowFall
            v-if="
                props.weatherCode == 71 ||
                props.weatherCode == 73 ||
                props.weatherCode == 75
            "
            class="block dark:hidden"
        />
        <SvgWeatherCodeSnowGrains
            v-if="weatherCode == 77"
            class="block dark:hidden"
        />
        <SvgWeatherCodeRainShower
            v-if="
                props.weatherCode == 80 ||
                props.weatherCode == 81 ||
                props.weatherCode == 82
            "
            class="block dark:hidden"
        />
        <SvgWeatherCodeSnowShower
            v-if="props.weatherCode == 85 || props.weatherCode == 86"
            class="block dark:hidden"
        />
        <SvgWeatherCodeThunderstorm
            v-if="props.weatherCode == 95"
            class="block dark:hidden"
        />
        <SvgWeatherCodeThunderstormHail
            v-if="props.weatherCode == 96 || props.weatherCode == 99"
            class="block dark:hidden"
        />

        <SvgWeatherCodeSunyDark
            v-if="props.weatherCode == 0"
            class="hidden dark:block"
        />
        <SvgWeatherCodeCloudyDark
            v-if="
                props.weatherCode == 1 ||
                props.weatherCode == 2 ||
                props.weatherCode == 3
            "
            class="hidden dark:block"
        />
        <SvgWeatherCodeFoggyDark
            v-if="props.weatherCode == 45 || props.weatherCode == 48"
            class="hidden dark:block"
        />
        <SvgWeatherCodeDrizzleDark
            v-if="
                props.weatherCode == 51 ||
                props.weatherCode == 53 ||
                props.weatherCode == 55
            "
            class="hidden dark:block"
        />
        <SvgWeatherCodeDrizzleFreezingDark
            v-if="props.weatherCode == 56 || props.weatherCode == 57"
            class="hidden dark:block"
        />
        <SvgWeatherCodeRainDark
            v-if="
                props.weatherCode == 61 ||
                props.weatherCode == 63 ||
                props.weatherCode == 65
            "
            class="hidden dark:block"
        />
        <SvgWeatherCodeRainFreezingDark
            v-if="props.weatherCode == 66 || props.weatherCode == 67"
            class="hidden dark:block"
        />
        <SvgWeatherCodeSnowFallDark
            v-if="
                props.weatherCode == 71 ||
                props.weatherCode == 73 ||
                props.weatherCode == 75
            "
            class="hidden dark:block"
        />
        <SvgWeatherCodeSnowGrainsDark
            v-if="weatherCode == 77"
            class="hidden dark:block"
        />
        <SvgWeatherCodeRainShowerDark
            v-if="
                props.weatherCode == 80 ||
                props.weatherCode == 81 ||
                props.weatherCode == 82
            "
            class="hidden dark:block"
        />
        <SvgWeatherCodeSnowShowerDark
            v-if="props.weatherCode == 85 || props.weatherCode == 86"
            class="hidden dark:block"
        />
        <SvgWeatherCodeThunderstormDark
            v-if="props.weatherCode == 95"
            class="hidden dark:block"
        />
        <SvgWeatherCodeThunderstormHailDark
            v-if="props.weatherCode == 96 || props.weatherCode == 99"
            class="hidden dark:block"
        />
    </div>
</template>
