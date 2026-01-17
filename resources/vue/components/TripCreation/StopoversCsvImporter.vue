<script>
import Papa from 'papaparse';
import { DateTime } from 'luxon';
import { trans } from 'laravel-vue-i18n';

export default {
    name: 'StopoversCsvImporter',
    props: {
        offcanvasId: {
            type: String,
            default: 'stopoversCsvImporterOffcanvas',
        },
        maxItems: {
            type: Number,
            default: 50,
        },
    },
    emits: ['imported'],
    data() {
        return {
            csvText: '',
            busy: false,
        };
    },
    methods: {
        trans,
        async fetchStationById(id) {
            const resp = await fetch(`/api/v1/stations/${id}`, {
                method: 'GET',
                headers: { 'Content-Type': 'application/json' },
            });
            if (!resp.ok) {
                throw new Error(`Station ${id} not found`);
            }
            const json = await resp.json();
            return json.data;
        },
        closeOffcanvas() {
            const el = this.$refs.offcanvasEl;
            if (!el) return;
            const bs = window?.bootstrap;
            const instance = bs?.Offcanvas?.getOrCreateInstance ? bs.Offcanvas.getOrCreateInstance(el) : null;
            if (instance?.hide) {
                instance.hide();
                return;
            }
            el.querySelector('[data-bs-dismiss="offcanvas"]')?.click();
        },
        async importNow() {
            if (this.busy) return;
            this.busy = true;

            const parseLocal = val => {
                if (!val) return null;
                const s = String(val).trim();
                const dt = DateTime.fromFormat(s, 'yyyy-LL-dd HH:mm', { zone: 'local', setZone: true });
                return dt.isValid ? dt.toFormat("yyyy-LL-dd'T'HH:mm") : null;
            };

            try {
                const parsed = Papa.parse(this.csvText || '', {
                    delimiter: ',',
                    skipEmptyLines: true,
                    transform: v => (typeof v === 'string' ? v.trim() : v),
                });

                const rows = (parsed?.data || []).filter(r => Array.isArray(r) && r.length >= 1);

                if (rows.length < 2) {
                    window?.notyf?.error?.(this.trans('trip_creation.csv_import.errors.min_two_rows'));
                    this.busy = false;
                    return;
                }

                if (rows.length > this.maxItems) {
                    window?.notyf?.error?.(
                        this.trans('trip_creation.csv_import.errors.too_many_rows', { max: this.maxItems }),
                    );
                    this.busy = false;
                    return;
                }

                const prepared = [];
                const badRows = [];

                for (let i = 0; i < rows.length; i++) {
                    const [idRaw, arrRaw = '', depRaw = ''] = rows[i];

                    if (!idRaw || (arrRaw === '' && depRaw === '')) {
                        badRows.push(i + 1);
                        continue;
                    }

                    const stationId = String(idRaw).trim();
                    const arrival = parseLocal(arrRaw);
                    const departure = parseLocal(depRaw);

                    if (!arrival && !departure) {
                        badRows.push(i + 1);
                        continue;
                    }

                    const finalArrival = arrival || departure;
                    const finalDeparture = departure || arrival;

                    prepared.push({
                        stationId,
                        arrivalPlanned: finalArrival,
                        departurePlanned: finalDeparture,
                        sortKey: finalDeparture,
                    });
                }

                if (badRows.length > 0) {
                    window?.notyf?.error?.(
                        this.trans('trip_creation.csv_import.errors.bad_row', { rows: badRows.join(', ') }),
                    );
                }

                // sort by departure time (or arrival if departure missing)
                prepared.sort((a, b) => {
                    const da = DateTime.fromISO(a.sortKey, { zone: 'local' });
                    const db = DateTime.fromISO(b.sortKey, { zone: 'local' });
                    if (da.isValid && db.isValid) return da.toMillis() - db.toMillis();
                    return String(a.sortKey).localeCompare(String(b.sortKey));
                });

                const stops = [];
                const skipped = [];

                for (let i = 0; i < prepared.length; i++) {
                    const row = prepared[i];
                    try {
                        const station = await this.fetchStationById(row.stationId);
                        stops.push({
                            station,
                            arrivalPlanned: row.arrivalPlanned,
                            departurePlanned: row.departurePlanned,
                        });
                    } catch {
                        skipped.push({ index: i + 1, id: row.stationId });
                    }
                }

                if (stops.length === 0) {
                    window?.notyf?.error?.(this.trans('trip_creation.csv_import.errors.station_not_found'));
                    this.busy = false;
                    return;
                }

                if (skipped.length > 0) {
                    window?.notyf?.error?.(
                        this.trans('trip_creation.csv_import.errors.some_stations_failed', { count: skipped.length }),
                    );
                }

                this.$emit('imported', stops);
                window?.notyf?.success?.(this.trans('trip_creation.csv_import.done'));
                this.closeOffcanvas();
                this.csvText = '';
            } finally {
                this.busy = false;
            }
        },
    },
};
</script>

<template>
    <div
        :id="offcanvasId"
        ref="offcanvasEl"
        class="offcanvas offcanvas-end"
        tabindex="-1"
        :aria-labelledby="offcanvasId + '-label'"
    >
        <div class="offcanvas-header">
            <h5 :id="offcanvasId + '-label'" class="offcanvas-title">
                {{ trans('trip_creation.csv_import.title') }}
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close" />
        </div>

        <div class="offcanvas-body">
            <p class="text-muted mb-2">
                {{ trans('trip_creation.csv_import.subtitle') }}
            </p>

            <ul class="small text-muted mb-3 ps-3">
                <li>{{ trans('trip_creation.csv_import.help.columns') }}</li>
                <li>{{ trans('trip_creation.csv_import.help.missing_time_rule') }}</li>
                <li>{{ trans('trip_creation.csv_import.help.duplicates_ok') }}</li>
                <li class="text-danger">
                    {{ trans('trip_creation.csv_import.help.overwrites_all') }}
                </li>
            </ul>

            <div class="mb-2">
                <textarea v-model.trim="csvText" class="form-control" rows="10" />
            </div>

            <div class="small text-muted mb-3">
                <div class="mb-1">
                    <strong>{{ trans('trip_creation.csv_import.help.example_title') }}</strong>
                </div>
                <pre class="bg-light p-2 rounded border mb-2" style="white-space: pre-wrap">
12345,1970-01-01 08:05,1970-01-01 08:10
23456,1970-01-01 09:15,
34567,,1970-01-01 09:20</pre
                >
                <div>
                    {{ trans('trip_creation.csv_import.help.limit', { max: maxItems }) }}
                </div>

                <a href="/debug/stations" target="_blank" rel="noopener">
                    {{ trans('trip_creation.csv_import.help.station_list_link') }}
                </a>
            </div>

            <div class="d-flex gap-2">
                <button class="btn btn-primary" :disabled="busy || !csvText" @click="importNow">
                    <i class="fa-solid fa-file-csv me-1" aria-hidden="true" />
                    {{ trans('trip_creation.csv_import.action.import') }}
                </button>
                <button class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">
                    {{ trans('trip_creation.csv_import.action.cancel') }}
                </button>
            </div>
        </div>
    </div>
</template>
