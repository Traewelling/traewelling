<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { DateTime } from 'luxon';
import { Notyf } from 'notyf';
import Papa from 'papaparse';
import { inject, ref, useTemplateRef } from 'vue';
import { Api, StationResource } from '../../../types/Api.gen';

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });

export type ImportedStop = {
    station: StationResource;
    arrivalPlanned: string;
    departurePlanned: string;
};

const props = withDefaults(defineProps<{ maxItems?: number }>(), { maxItems: 50 });

const emit = defineEmits<{ imported: [stops: ImportedStop[]] }>();

const notyf = inject('notyf') as Notyf;
const dialogEl = useTemplateRef<HTMLDialogElement>('dialogEl');
const csvText = ref('');
const busy = ref(false);

function open(): void {
    dialogEl.value?.showModal();
}

function close(): void {
    dialogEl.value?.close();
}

async function fetchStation(id: string): Promise<StationResource> {
    const res = await api.stations.showStation(id);
    const station = res.data?.data;
    if (!station) throw new Error(`Station ${id} not found`);
    return station;
}

async function importNow(): Promise<void> {
    if (busy.value) return;
    busy.value = true;

    const parseLocal = (val: unknown): string | null => {
        if (!val) return null;
        const dt = DateTime.fromFormat(String(val).trim(), 'yyyy-LL-dd HH:mm', { zone: 'local', setZone: true });
        return dt.isValid ? dt.toFormat("yyyy-LL-dd'T'HH:mm") : null;
    };

    try {
        const parsed = Papa.parse<string[]>(csvText.value ?? '', {
            delimiter: ',',
            skipEmptyLines: true,
            transform: (v) => (typeof v === 'string' ? v.trim() : v),
        });

        const rows = (parsed.data ?? []).filter((r) => Array.isArray(r) && r.length >= 1);

        if (rows.length < 2) {
            notyf.error(trans('trip_creation.csv_import.errors.min_two_rows'));
            return;
        }

        if (rows.length > props.maxItems) {
            notyf.error(trans('trip_creation.csv_import.errors.too_many_rows', { max: props.maxItems }));
            return;
        }

        const prepared: { stationId: string; arrivalPlanned: string; departurePlanned: string; sortKey: string }[] = [];
        const badRows: number[] = [];

        for (let i = 0; i < rows.length; i++) {
            const [idRaw = '', arrRaw = '', depRaw = ''] = rows[i];
            if (!idRaw || (arrRaw === '' && depRaw === '')) {
                badRows.push(i + 1);
                continue;
            }
            const arrival = parseLocal(arrRaw);
            const departure = parseLocal(depRaw);
            if (!arrival && !departure) {
                badRows.push(i + 1);
                continue;
            }
            prepared.push({
                stationId: String(idRaw).trim(),
                arrivalPlanned: arrival ?? departure!,
                departurePlanned: departure ?? arrival!,
                sortKey: departure ?? arrival!,
            });
        }

        if (badRows.length > 0) {
            notyf.error(trans('trip_creation.csv_import.errors.bad_row', { rows: badRows.join(', ') }));
        }

        prepared.sort((a, b) => {
            const da = DateTime.fromISO(a.sortKey, { zone: 'local' });
            const db = DateTime.fromISO(b.sortKey, { zone: 'local' });
            return da.isValid && db.isValid ? da.toMillis() - db.toMillis() : a.sortKey.localeCompare(b.sortKey);
        });

        const stops: ImportedStop[] = [];
        const skipped: number[] = [];

        for (let i = 0; i < prepared.length; i++) {
            const row = prepared[i];
            try {
                const station = await fetchStation(row.stationId);
                stops.push({ station, arrivalPlanned: row.arrivalPlanned, departurePlanned: row.departurePlanned });
            } catch {
                skipped.push(i + 1);
            }
        }

        if (stops.length === 0) {
            notyf.error(trans('trip_creation.csv_import.errors.station_not_found'));
            return;
        }

        if (skipped.length > 0) {
            notyf.error(trans('trip_creation.csv_import.errors.some_stations_failed', { count: skipped.length }));
        }

        emit('imported', stops);
        notyf.success(trans('trip_creation.csv_import.done'));
        csvText.value = '';
        close();
    } finally {
        busy.value = false;
    }
}

defineExpose({ open });
</script>

<template>
    <dialog ref="dialogEl" class="modal modal-bottom sm:modal-middle">
        <div class="modal-box max-w-lg">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-lg">{{ trans('trip_creation.csv_import.title') }}</h3>
                <form method="dialog">
                    <button class="btn btn-ghost btn-sm btn-circle">✕</button>
                </form>
            </div>

            <p class="text-base-content/60 text-sm mb-3">{{ trans('trip_creation.csv_import.subtitle') }}</p>

            <ul class="text-xs text-base-content/60 list-disc pl-4 mb-4 space-y-1">
                <li>{{ trans('trip_creation.csv_import.help.columns') }}</li>
                <li>{{ trans('trip_creation.csv_import.help.missing_time_rule') }}</li>
                <li>{{ trans('trip_creation.csv_import.help.duplicates_ok') }}</li>
                <li class="text-error">{{ trans('trip_creation.csv_import.help.overwrites_all') }}</li>
            </ul>

            <textarea
                v-model.trim="csvText"
                class="textarea textarea-bordered w-full font-mono text-xs"
                rows="8"
                placeholder="12345,1970-01-01 08:05,1970-01-01 08:10"
            />

            <div class="text-xs text-base-content/50 mt-2 mb-4 space-y-1">
                <div>
                    <strong>{{ trans('trip_creation.csv_import.help.example_title') }}</strong>
                </div>
                <pre class="bg-base-200 p-2 rounded text-xs overflow-x-auto">
12345,1970-01-01 08:05,1970-01-01 08:10
23456,1970-01-01 09:15,
34567,,1970-01-01 09:20</pre
                >
                <div>{{ trans('trip_creation.csv_import.help.limit', { max: maxItems }) }}</div>
                <a href="/debug/stations" target="_blank" rel="noopener" class="link link-hover">
                    {{ trans('trip_creation.csv_import.help.station_list_link') }}
                </a>
            </div>

            <div class="modal-action mt-0 gap-2">
                <button
                    class="btn btn-primary"
                    :class="{ loading: busy }"
                    :disabled="busy || !csvText"
                    @click="importNow"
                >
                    {{ trans('trip_creation.csv_import.action.import') }}
                </button>
                <form method="dialog">
                    <button class="btn btn-ghost">{{ trans('trip_creation.csv_import.action.cancel') }}</button>
                </form>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
    </dialog>
</template>
