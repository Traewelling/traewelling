import { trans, transChoice } from 'laravel-vue-i18n';
import { StatusResource } from '../../types/Api.gen';
import { getDepartureForStatus } from './DateTimeHelper';

export class StatusHelper {
    private readonly status: StatusResource;

    constructor(status: StatusResource) {
        this.status = status;
    }

    private generateBaseText(): string {
        return transChoice(
            'controller.transport.social-post',
            new RegExp(/\s/).exec(this.status.checkin.lineName)?.length ?? 0,
            {
                lineName: this.status.checkin.lineName,
                destination: this.status.checkin.destination.name,
            },
        );
    }

    private generateEventText(): string {
        return transChoice(
            'controller.transport.social-post-with-event',
            new RegExp(/\s/).exec(this.status.checkin.lineName)?.length ?? 0,
            {
                lineName: this.status.checkin.lineName,
                destination: this.status.checkin.destination.name,
                hashtag: this.status.event!.hashtag || '',
            },
        );
    }

    private generateAppendix(): string {
        const hashtag = this.status.event?.hashtag
            ? ' ' + trans('controller.transport.social-post-for', { hashtag: this.status.event.hashtag })
            : '';
        const lineName = this.status.checkin.lineName;
        const destination = this.status.checkin.destination.name;

        return ` (@ ${lineName} ➜ ${destination}${hashtag}) #NowTräwelling`;
    }

    public generateSocialText(): string {
        if (this.status.body) {
            const body = this.status.body.trim();
            const appendix = this.generateAppendix();
            let postText = body.slice(0, 500 - appendix.length + 30);

            if (postText.length !== body.length) {
                postText += '…';
            }
            return postText + appendix;
        }

        if (this.status.event?.hashtag) {
            return this.generateEventText();
        }

        return this.generateBaseText();
    }

    public getDescription(): string {
        const departure = getDepartureForStatus(this.status);

        return trans('description.status', {
            username: this.status.user.username,
            origin: this.status.checkin.origin.name,
            destination: this.status.checkin.destination.name,
            date: departure.toLocaleString({
                year: 'numeric',
                month: 'numeric',
                day: 'numeric',
                hour: 'numeric',
                minute: '2-digit',
            }),
            lineName: this.status.checkin.lineName,
        });
    }

    public getShareUrl() {
        return `${window.location.origin}/status/${this.status.id}`;
    }
}
