import {
    Briefcase,
    Building,
    CircleQuestionMark,
    Earth,
    ExternalLink,
    Globe,
    Lock,
    LockOpen,
    LucideIcon,
    User,
    UserRoundKey,
    UsersRound,
    UserStar,
} from '@lucide/vue';
import { trans } from 'laravel-vue-i18n';
import { Component, FunctionalComponent } from 'vue';
import { Business, StatusVisibility } from '../../types/Api.gen';
import BlueSky from '../components/Icons/BlueSky.vue';
import Facebook from '../components/Icons/Facebook.vue';
import GitHub from '../components/Icons/GitHub.vue';
import Instagram from '../components/Icons/Instagram.vue';
import Mastodon from '../components/Icons/Mastodon.vue';
import TikTok from '../components/Icons/TikTok.vue';

const visibilityIcons: { [key: number]: LucideIcon } = {
    0: Earth,
    1: LockOpen,
    2: UsersRound,
    3: Lock,
    4: UserRoundKey,
    5: UserStar,
};

const businessIcons: { [key: number]: LucideIcon } = {
    0: User,
    1: Briefcase,
    2: Building,
};

const linkIcons: { [key: string]: FunctionalComponent | Component } = {
    website: Globe,
    instagram: Instagram,
    bluesky: BlueSky,
    facebook: Facebook,
    mastodon: Mastodon,
    tiktok: TikTok,
    github: GitHub,
};

export class IconHelper {
    static getLinkIcon(linkType: string): FunctionalComponent | Component {
        if (Object.prototype.hasOwnProperty.call(linkIcons, linkType)) {
            return linkIcons[linkType] || null;
        }
        return ExternalLink;
    }

    static getVisibilityIcon(visibility: StatusVisibility): LucideIcon {
        if (Object.prototype.hasOwnProperty.call(visibilityIcons, visibility)) {
            return visibilityIcons[visibility];
        }
        return CircleQuestionMark; // Fallback icon for unknown visibility
    }

    static getVisibilityTooltip(visibility: StatusVisibility): string {
        return trans('status.visibility.' + visibility);
    }

    static getBusinessIcon(business: Business): LucideIcon {
        if (Object.prototype.hasOwnProperty.call(businessIcons, business)) {
            return businessIcons[business];
        }
        return CircleQuestionMark; // Fallback icon for unknown business type
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
