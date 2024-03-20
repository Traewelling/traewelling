export type Notification = {
    id: string;
    type: string;
    leadFormatted: string;
    lead: string;
    noticeFormatted: string;
    notice: string;
    link: string;
    data: StatusLikedNotification | any;
    readAt: null | string;
    createdAt: string;
    createdAtForHumans: string;
};

export type StatusLikedNotification = {
    like: {
        id: number;
    };
    status: {
        id: number;
    };
    trip: {
        origin: {
            id: number;
            ibnr: number;
            name: string;
        };
        destination: {
            id: number;
            ibnr: number;
            name: string;
        };
        plannedDeparture: string;
        plannedArrival: string;
        lineName: string;
    };
    liker: {
        id: number;
        username: string;
        name: string;
    };
};
