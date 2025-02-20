/**
 * generate gravatar url with hash and display name
 * @param hash: email hash
 * @param displayName: display name
 * @returns {avatarUrl: ComputedRef<string>, refreshAvatar: () => string} - Returns the avatar url and a function to refresh the avatar
 * see: https://docs.gravatar.com/api/avatars/hash/
 * s - size
 * d - default image mode
 * name - gravatar extracts initials from the name
 * t - timestamp to force the browser to refresh the image
 */
export const useGravatar = (hash: string, displayName: string) => {
    const timestamp = ref(Date.now());

    const avatarUrl = computed(() => {
        return `https://gravatar.com/avatar/${hash}?s=200&d=initials&name=${encodeURIComponent(displayName)}&t=${timestamp.value}`;
    });

    const refreshAvatar = () => {
        timestamp.value = Date.now();
        return avatarUrl.value;
    };

    return { avatarUrl, refreshAvatar };
};
