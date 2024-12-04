export default function debounce<T extends (...args: unknown[]) => unknown>(
    fn: T,
    ms = 300,
) {
    let timeoutId: ReturnType<typeof setTimeout>;
    return function (this: ThisParameterType<T>, ...args: Parameters<T>): void {
        console.log(args);

        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => fn.apply(this, args), ms);
    };
}
