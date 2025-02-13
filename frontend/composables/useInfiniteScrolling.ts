/**
 * Composable to implement infinite scrolling
 * @param options {Object} - Necessary options for the composable
 * @param options.loader {Ref<HTMLElement | undefined>} - The loader element that will be observed for intersection
 * @param options.showMoreData {Ref<boolean>} - A boolean ref that toggles the infinite scrolling
 * @param options.showMoreDataText {string} - The text (or t.value(text)) to show when infinite scrolling is enabled
 * @param options.showLessDataText {string} - The text (or t.value(text)) to show when infinite scrolling is disabled
 * @param options.identifier {string} - The identifier for the useLazyAsyncData composable
 * @param options.apiEndpoint {string} - The API endpoint to fetch data from
 * @param options.params {Object} - The parameters to be sent to the API endpoint
 * @returns {data: Ref<T[]>, moreDataAvailable: Ref<boolean>} - Returns the data and a boolean ref that indicates if more data is available
 */
export async function useInfiniteScroll<T>(options: {
    loader: Ref<HTMLElement | undefined>;
    showMoreData: Ref<boolean>;
    showMoreDataText: string;
    showLessDataText: string;
    identifier: string;
    apiEndpoint: string;
    params: { [key: string]: string | number };
}) {
    const client = useSanctumClient();
    const observer = ref<IntersectionObserver | null>(null);

    const moreDataAvailable = ref<boolean>(false);
    const allData = ref<T[]>([]);
    const cursor = ref<string | null>(null);
    const nextCursor = ref<string | null>(null);

    watch(options.showMoreData, () => {
        if (options.showMoreData.value) {
            if (observer.value) {
                observer.value.disconnect();
            }

            observer.value = new IntersectionObserver((entries) => {
                if (entries.length === 0) {
                    return;
                }
                const target = entries[0];
                if (target.isIntersecting) {
                    if (moreDataAvailable.value && options.showMoreData.value) {
                        cursor.value = nextCursor.value;
                        refresh();
                    }
                }
            });

            if (options.loader.value) {
                observer.value.observe(options.loader.value);
            }
        }
    });

    const { data, refresh, status } = useLazyAsyncData(options.identifier, () =>
        client(options.apiEndpoint, {
            params: {
                ...options.params,
                cursor: cursor.value,
            },
        }),
    );

    watch(
        data,
        () => {
            if (data.value) {
                allData.value.push(...data.value.data);

                if (data?.value.next_cursor === null) {
                    moreDataAvailable.value = false;
                } else {
                    nextCursor.value = data.value.next_cursor;
                    moreDataAvailable.value = true;
                }
            }
        },
        { immediate: true },
    );

    function toggle() {
        options.showMoreData.value = !options.showMoreData.value;
    }

    const toggleText = computed(() =>
        options.showMoreData.value
            ? options.showLessDataText
            : options.showMoreDataText,
    );

    onUnmounted(() => {
        if (observer.value) {
            observer.value.disconnect();
        }
    });

    return { data: allData, moreDataAvailable, status, toggle, toggleText };
}
