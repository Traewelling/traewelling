import { trans } from 'laravel-vue-i18n';
import { StatusTagKey } from '../../types/Api.gen';

/** Keys whose values are constrained to a fixed set (not free-text). */
export const TagEnumValues: [{ key: StatusTagKey; values: string[] }] = [
    { key: StatusTagKey.TrwlSocialStatus, values: ['open', 'open_find_me', 'open_lets_hang', 'do_not_disturb'] },
];

export function getTitle(key: StatusTagKey | string) {
    const translate = trans('tag.title.' + key);

    if (translate === 'tag.title.' + key) {
        return key;
    }
    return translate;
}

export type TranslatedTagEnum = { value: string; label: string };

/**
 * Returns the allowed values for enum-style tags, each with label and value.
 * Returns null for free-text tags.
 */
export function getEnumValues(key: StatusTagKey): TranslatedTagEnum[] | null {
    const values = TagEnumValues.find((entry) => entry.key === key);
    if (!values) {
        return null;
    }
    return values.values.map((v: string): TranslatedTagEnum => {
        const translateKey = 'tag.value.' + key + '.' + v;
        const label = trans(translateKey);
        return { value: v, label: label === translateKey ? v : label };
    });
}
