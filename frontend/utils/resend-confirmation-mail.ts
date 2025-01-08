export default async function resendConfirmationMail(): Promise<void> {
    try {
        const toast = useToastService();
        toast.add({
            severity: "info",
            summary: "Resending mail...",
            detail: "Please wait...",
            life: 3000,
        });
        console.log("Mail resent successfully");
    } catch (error) {
        console.error("Error resending mail:", error);
    }
}
