export function useMicrosoftAuth() {
    const client = useSanctumClient();

    async function loginWithMicrosoft() {
        const response = await client("/auth/redirect/microsoft");
        navigateTo(response.url, {
            external: true,
        });
    }

    return { loginWithMicrosoft };
}
