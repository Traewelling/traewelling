import { ref } from 'vue';
import { Api, ExportableColumn } from '../../../../types/Api.gen';

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });

function triggerDownload(blob: Blob, disposition: string | null): void {
    const filename = new RegExp(/filename="?([^";\r\n]+)"?/).exec(disposition)?.[1] ?? 'export';
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    a.remove();
    URL.revokeObjectURL(url);
}

export interface DownloadResult {
    ok: boolean;
    errorMessage?: string;
}

type ExportFiletype = Parameters<typeof api.export.generateStatusExport>[0]['filetype'];

export function useExportDownload() {
    const loading = ref(false);

    async function download(params: {
        from: string;
        until: string;
        columns?: ExportableColumn[];
        filetype: ExportFiletype;
    }): Promise<DownloadResult> {
        loading.value = true;
        try {
            const response = await api.export.generateStatusExport(params, {
                format: 'blob',
            });

            const blob = response.data as unknown as Blob;
            triggerDownload(blob, response.headers.get('Content-Disposition'));
            return { ok: true };
        } catch (err: unknown) {
            const message = await extractErrorMessage(err);
            return { ok: false, errorMessage: message };
        } finally {
            loading.value = false;
        }
    }

    return { loading, download };
}

async function extractErrorMessage(err: unknown): Promise<string> {
    if (err && typeof err === 'object' && 'error' in err) {
        const e = err as { error?: unknown };
        if (e.error instanceof Blob) {
            try {
                const text = await e.error.text();
                const json = JSON.parse(text);
                return json.error ?? json.message ?? 'Unknown error';
            } catch {
                //
            }
        }
        if (typeof e.error === 'string') return e.error;
    }
    return 'Network error';
}
