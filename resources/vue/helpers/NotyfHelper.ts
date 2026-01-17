// eslint-disable-next-line @typescript-eslint/no-explicit-any
export function showApiValidationErrors(notyf: any, errors: Record<string, string[]>): void {
    // foreach error and show it
    Object.keys(errors).forEach((key: string) => {
        errors[key].forEach((error: string) => {
            notyf.error(error);
        });
    });
}
