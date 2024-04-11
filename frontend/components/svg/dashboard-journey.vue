<script setup>
const props = defineProps({
  link: { type: String, required: true },
});
</script>

<template>
  <NuxtLink :to="link">
    <svg
      xmlns="http://www.w3.org/2000/svg"
      class="w-72"
      viewBox="0 0 225 119"
      fill="none"
    >
      <rect
        x="8.4707"
        y="0.942383"
        width="215.58"
        height="117.115"
        rx="10"
        fill="#C0DEEB"
      />
      <rect x="25" y="14.5" width="187" height="90" rx="7" fill="#F8F8F8" />
      <rect
        x="22.5"
        y="12"
        width="192"
        height="95"
        rx="7.5"
        stroke="#75929F"
        stroke-opacity="0.28"
        stroke-dasharray="4 4"
      />
      <path
        d="M8 68.5C10.1217 68.5 12.1566 67.5518 13.6569 65.864C15.1571 64.1761 16 61.8869 16 59.5C16 57.1131 15.1571 54.8239 13.6569 53.136C12.1566 51.4482 10.1217 50.5 8 50.5L8 59.5L8 68.5Z"
        fill="#FCFCFC"
      />
    </svg>
  </NuxtLink>
</template>
