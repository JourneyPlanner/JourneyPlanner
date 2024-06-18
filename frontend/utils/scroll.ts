export function scroll(target: {
    scrollIntoView: (options: { behavior: string; block: string }) => void;
}) {
    target.scrollIntoView({
        behavior: "smooth",
        block: "start",
    });
}
