export default async function resendConfirmationMail(
    email: string,
    t: { value: (key: string) => string },
): Promise<void> {
    const toast = useToastService();
    const client = useSanctumClient();

    try {
        toast.add({
            severity: "info",
            summary: t.value("email.resending.start.toast.summary"),
            detail: t.value("email.resending.start.toast.detail"),
            life: 3000,
        });

        const response = await client("/email/verification-notification", {
            method: "POST",
            body: {
                email: email,
            },
        });

        if (response.status === "verification-link-sent") {
            toast.add({
                severity: "success",
                summary: t.value("email.resending.success.toast.summary"),
                detail: t.value("email.resending.success.toast.detail"),
                life: 10000,
            });
        } else {
            throw new Error("Error resending mail");
        }
    } catch {
        toast.add({
            severity: "error",
            summary: t.value("common.toast.error.heading"),
            detail: t.value("email.resending.error.toast.detail"),
            life: 10000,
        });
    }
}
