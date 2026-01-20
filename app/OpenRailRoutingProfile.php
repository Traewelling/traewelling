<?php

namespace App;

enum OpenRailRoutingProfile: string
{
    case ALL_TRACKS = 'all_tracks';
    case ALL_TRACKS_1435 = 'all_tracks_1435';
    case NON_TGV = 'non_tgv';
    case TGV_ALL = 'tgv_all';
    case TRAM_TRAIN = 'tramtrain';
}
