'use strict';
import API from './api';

export class Event {
    static suggest(name, host, begin, end, url, hashtag, nearestStation) {
        API.request('/event', 'POST', {
            name,
            host,
            begin,
            end,
            url,
            hashtag,
            nearestStation,
        }).then(API.handleDefaultResponse);
    }
}
