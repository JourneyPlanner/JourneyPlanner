<script setup lang="ts">
import { useRoute } from "vue-router";

// Get the code from the URL
const route = useRoute();
const code = route.query.code as string;
// Send the code to the other window
window.opener.postMessage({ code }, window.location.origin);
// Close the window
window.close();
</script>
