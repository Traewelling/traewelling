import { trans } from 'laravel-vue-i18n';

export const keys = [
    'trwl:journey_number',
    'trwl:ticket',
    'trwl:price',
    'trwl:role',
    'trwl:passenger_rights',
    'trwl:locomotive_class',
    'trwl:travel_class',
    'trwl:seat',
    'trwl:wagon',
    'trwl:wagon_class',
    'trwl:vehicle_number',
    'trwl:social_status',
];

/** Keys whose values are constrained to a fixed set (not free-text). */
export const enumKeys = {
    'trwl:social_status': ['open', 'open_find_me', 'open_lets_hang', 'do_not_disturb'],
};

export function getTitle(key) {
    let translate = trans('tag.title.' + key);

    if (translate === 'tag.title.' + key) {
        return key;
    }
    return translate;
}

/**
 * Returns the allowed values for enum-style tags, each with label and value.
 * Returns null for free-text tags.
 *
 * @param {string} key
 * @returns {{ value: string, label: string }[]|null}
 */
export function getEnumValues(key) {
    const values = enumKeys[key];
    if (!values) {
        return null;
    }
    return values.map((v) => {
        const translateKey = 'tag.value.' + key + '.' + v;
        const label = trans(translateKey);
        return { value: v, label: label === translateKey ? v : label };
    });
}
