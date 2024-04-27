import {trans} from "laravel-vue-i18n";

export const keys = [
    "trwl:ticket",
    "trwl:role",
    "trwl:passenger_rights",
    "trwl:locomotive_class",
    "trwl:travel_class",
    "trwl:seat",
    "trwl:wagon",
    "trwl:wagon_class",
    "trwl:vehicle_number"
];

export function getIcon(key) {
    switch (key) {
        case "trwl:seat":
            return "fa-couch";
        case "trwl:role":
            return "fa-briefcase";
        case "trwl:ticket":
            return "fa-qrcode";
        case "trwl:passenger_rights":
            return "fa-user-shield";
        case "trwl:locomotive_class":
            return "fa-train";
        case "trwl:travel_class":
            return "fa-1";
    }
    return "fa-fw";
}

export function getTitle(key) {
    let translate = trans("tag.title." + key);

    if (translate === "tag.title." + key) {
        return key;
    }
    return translate;
}
