<script setup lang="ts">
const props = defineProps({
    isActivityDialogVisible: {
        type: Boolean,
        required: true,
    },
    currUser: {
        type: Object as PropType<User>,
        required: true,
    },
    journeyStart: { type: Date, required: true },
    journeyEnd: { type: Date, required: true },
});

const emit = defineEmits(["close"]);

const { isAuthenticated } = useSanctumAuth();

const journeyStore = useJourneyStore();
const activityStore = useActivityStore();
const isDialogVisible = ref(false);

watch(
    () => props.isActivityDialogVisible,
    (value) => {
        isDialogVisible.value = value;
    },
);

function close() {
    isDialogVisible.value = false;
    emit("close");
}
</script>

<template>
    <div>
        <div
            v-if="
                currUser?.role === 1 ||
                !isAuthenticated ||
                currUser?.id === 'TEMPLATE'
            "
            class="flex justify-center md:justify-start"
        >
            <div
                class="-mt-4 flex h-10 w-[90%] items-end sm:w-5/6 md:ml-[10%] md:h-20 md:w-[calc(50%+16rem)] md:justify-start lg:ml-10 lg:h-24 lg:w-[calc(33.33vw+38.5rem)] xl:ml-[10%] xl:w-[calc(33.33vw+44rem)]"
            >
                <div class="-mb-2.5 text-2xl font-semibold lg:mb-3">
                    <T key-name="journey.activities" />
                </div>
                <button
                    v-if="currUser?.id !== 'TEMPLATE'"
                    class="-mb-3 ml-auto flex items-center rounded-xl border-2 border-dandelion-300 bg-natural-50 px-1.5 py-1.5 pr-2.5 text-sm font-bold hover:bg-dandelion-200 dark:bg-natural-800 dark:text-natural-50 dark:hover:bg-pesto-600 sm:px-2 sm:py-1 sm:text-base lg:mb-4"
                    @click="isDialogVisible = !isDialogVisible"
                >
                    <SvgAddLocation class="h-6 w-6" />
                    <span class="text-base sm:hidden">
                        <T key-name="journey.button.create.activity.short" />
                    </span>
                    <span class="hidden sm:block">
                        <T key-name="journey.button.create.activity" />
                    </span>
                </button>
                <span
                    v-else
                    class="-mb-3.5 ml-auto text-base md:text-lg lg:mb-1"
                >
                    {{ activityStore.activityData.length }}
                    <T key-name="template.activities" />
                </span>
            </div>
        </div>
        <JourneyIdComponentsActivityPool
            v-if="
                currUser?.role === 1 ||
                !isAuthenticated ||
                currUser?.id === 'TEMPLATE'
            "
            :id="journeyStore.getID()"
            :journey-start="props.journeyStart"
            :journey-end="props.journeyEnd"
            :in-template="currUser?.id === 'TEMPLATE'"
            @open-new-activity-dialog="isDialogVisible = true"
        />
        <JourneyIdDialogsActivityDialog
            v-if="currUser?.role === 1 || !isAuthenticated"
            :id="journeyStore.getID()"
            :visible="isDialogVisible"
            :only-show="false"
            :journey-start="props.journeyStart"
            :journey-end="props.journeyEnd"
            :create="true"
            :create-address="true"
            @close="close"
        />
    </div>
</template>
