import { trans, transChoice } from 'laravel-vue-i18n';
import { StatusResource } from '../../types/Api.gen';
import { getStationRIL100 } from '../../types/Station';
import { getDepartureForStatus } from './DateTimeHelper';

export class StatusHelper {
    private readonly status: StatusResource;

    constructor(status: StatusResource) {
        this.status = status;
    }

    private generateBaseText(): string {
        return transChoice(
            'controller.transport.social-post',
            RegExp(/\s/).exec(this.status.train.lineName)?.length ?? 0,
            {
                lineName: this.status.train.lineName,
                destination: this.status.train.destination.name,
            },
        );
    }

    private generateEventText(): string {
        return transChoice(
            'controller.transport.social-post-with-event',
            RegExp(/\s/).exec(this.status.train.lineName)?.length ?? 0,
            {
                lineName: this.status.train.lineName,
                destination: this.status.train.destination.name,
                hashtag: this.status.event!.hashtag,
            },
        );
    }

    private generateAppendix(): string {
        const hashtag = this.status.event?.hashtag
            ? ' ' + trans('controller.transport.social-post-for', { hashtag: this.status.event.hashtag })
            : '';
        const lineName = this.status.train.lineName;
        const destination = this.status.train.destination.name;

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
        const originRil = getStationRIL100(this.status.train.origin)
            ? ` (${getStationRIL100(this.status.train.origin)})`
            : '';
        const destinationRil = getStationRIL100(this.status.train.destination)
            ? ` (${getStationRIL100(this.status.train.destination)})`
            : '';
        const departure = getDepartureForStatus(this.status);

        return trans('description.status', {
            username: this.status.userDetails.username,
            origin: this.status.train.origin.name + originRil,
            destination: this.status.train.destination.name + destinationRil,
            date: departure.toLocaleString({
                year: 'numeric',
                month: 'numeric',
                day: 'numeric',
                hour: 'numeric',
                minute: '2-digit',
            }),
            lineName: this.status.train.lineName,
        });
    }

    public getShareUrl() {
        return `${window.location.origin}/status/${this.status.id}`;
    }
}
