export default function (target: {
    scrollIntoView: (options: { behavior: string; block: string }) => void;
}): void {
    target.scrollIntoView({
        behavior: "smooth",
        block: "start",
    });
}
