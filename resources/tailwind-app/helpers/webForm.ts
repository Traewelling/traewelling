import { trans } from 'laravel-vue-i18n';

export type FormErrors = Record<string, string[]>;

export type WebFormResult<T> = { ok: true; data: T } | { ok: false; errors: FormErrors };

function generalError(): WebFormResult<never> {
    return { ok: false, errors: { form: [trans('messages.exception.general')] } };
}

export async function postWebForm<T = unknown>(url: string, body: Record<string, unknown>): Promise<WebFormResult<T>> {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

    let response: Response;
    try {
        response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': token,
            },
            body: JSON.stringify(body),
        });
    } catch {
        return generalError();
    }

    const data = await response.json().catch(() => ({}));
    if (response.ok) {
        return { ok: true, data: data as T };
    }
    if (data.errors) {
        return { ok: false, errors: data.errors as FormErrors };
    }

    return generalError();
}
