import {trans} from "laravel-vue-i18n";

export const keys = [
    'trwl:seat',
    'trwl:ticket',
    'trwl:role',
    'trwl:passenger_rights',
    'trwl:wagon',
    'trwl:travel_class',
    'trwl:locomotive_class',
    'trwl:wagon_class',
    'trwl:vehicle_number'
];

export function getIcon(key) {
    switch (key) {
        case "trwl:seat":
            return 'fa-couch';
        case 'trwl:role':
            return 'fa-briefcase';
        case 'trwl:ticket':
            return 'fa-qrcode';
        case 'trwl:passenger_rights':
            return 'fa-user-shield';
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
