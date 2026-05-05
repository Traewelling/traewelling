//pinia.d.ts

import 'pinia';

declare module 'pinia' {
    export interface DefineStoreOptionsBase {
        persist?: boolean | { pick?: string[]; omit?: string[] };
    }
}
