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

export function getIcon(key) {
    switch (key) {
        case 'trwl:seat':
            return 'fa-couch';
        case 'trwl:role':
            return 'fa-briefcase';
        case 'trwl:ticket':
            return 'fa-qrcode';
        case 'trwl:passenger_rights':
            return 'fa-user-shield';
        case 'trwl:locomotive_class':
            return 'fa-train';
        case 'trwl:travel_class':
            return 'fa-1';
        case 'trwl:journey_number':
            return 'fa-route';
        case 'trwl:price':
            return 'fa-money-bill-wave';
        case 'trwl:social_status':
            return 'fa-comments';
        case 'trwl:wagon':
            return 'fa-arrow-up-1-9';
        case 'trwl:wagon_class':
            return 'fa-list-check';
        case 'trwl:vehicle_number':
            return 'fa-tag';
    }
    return 'fa-fw';
}

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
