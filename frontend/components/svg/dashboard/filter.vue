<script setup lang="ts">
defineProps({ isActive: { type: Boolean, required: true } });
</script>

<template>
    <svg
        xmlns="http://www.w3.org/2000/svg"
        width="28"
        height="28"
        viewBox="0 0 28 28"
        fill="none"
        class="group"
    >
        <circle
            cx="14"
            cy="14"
            r="13.25"
            class="stroke-dandelion-300 group-hover:fill-dandelion-200 dark:group-hover:fill-pesto-600"
            :class="
                isActive
                    ? 'fill-dandelion-200 dark:fill-pesto-600'
                    : 'fill-natural-50 dark:fill-natural-800'
            "
            stroke-width="1.5"
        />
        <g clip-path="url(#clip0_49_2755)">
            <path
                fill-rule="evenodd"
                clip-rule="evenodd"
                d="M7.27251 8.33846C7.37772 8.13084 7.59071 8 7.82346 8H20.1764C20.4092 8 20.6221 8.13084 20.7273 8.33846C20.8326 8.54608 20.8121 8.79521 20.6745 8.9829L16.2646 14.9963V21.3824C16.2646 21.7235 15.9881 22 15.647 22H12.3529C12.0118 22 11.7352 21.7235 11.7352 21.3824V14.9963L7.32539 8.9829C7.18775 8.79521 7.16731 8.54608 7.27251 8.33846ZM9.04233 9.23529L12.8509 14.4289C12.9286 14.5348 12.9705 14.6627 12.9705 14.7941V20.7647H15.0293V14.7941C15.0293 14.6627 15.0712 14.5348 15.1489 14.4289L18.9575 9.23529H9.04233Z"
                class="fill-text dark:fill-natural-50"
            />
        </g>
        <defs>
            <clipPath id="clip0_49_2755">
                <rect x="7" y="8" width="14" height="14" />
            </clipPath>
        </defs>
    </svg>
</template>
