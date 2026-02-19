import { trans } from 'laravel-vue-i18n';
import { Business, StatusVisibility } from '../../types/Api.gen';

const visibilityIcons = {
    0: 'fa-globe-americas',
    1: 'fa-lock-open',
    2: 'fa-user-friends',
    3: 'fa-lock',
    4: 'fa-user-check',
    5: 'fa-user-shield',
};

const businessIcons = {
    0: 'fa-user',
    1: 'fa-briefcase',
    2: 'fa-building',
};

const linkIcons: { [key: string]: string } = {
    website: 'fa-globe',
    instagram: 'fa-instagram',
    bluesky: 'fa-bluesky',
    facebook: 'fa-facebook',
    mastodon: 'fa-mastodon',
    tiktok: 'fa-tiktok',
    github: 'fa-github',
};

export class IconHelper {
    static getLinkIcon(linkType: string): string | null {
        if (Object.prototype.hasOwnProperty.call(linkIcons, linkType)) {
            return linkIcons[linkType] || null;
        }
        return null;
    }

    static getVisibilityIcon(visibility: StatusVisibility): string {
        if (Object.prototype.hasOwnProperty.call(visibilityIcons, visibility)) {
            return visibilityIcons[visibility];
        }
        return 'fa-question'; // Fallback icon for unknown visibility
    }

    static getVisibilityTooltip(visibility: StatusVisibility): string {
        return trans('status.visibility.' + visibility);
    }

    static getBusinessIcon(business: Business): string {
        if (Object.prototype.hasOwnProperty.call(businessIcons, business)) {
            return businessIcons[business];
        }
        return 'fa-question'; // Fallback icon for unknown business type
    }

    static getBusinessTitle(business: Business): string {
        switch (business) {
            case 0:
                return 'stationboard.business.private';
            case 1:
                return 'stationboard.business.business';
            case 2:
                return 'stationboard.business.commute';
            default:
                return 'unknown';
        }
    }

    static getBusinessDescription(business: Business): string {
        switch (business) {
            case 0:
                return '';
            case 1:
                return 'stationboard.business.business.detail';
            case 2:
                return 'stationboard.business.commute.detail';
            default:
                return 'unknown';
        }
    }
}
