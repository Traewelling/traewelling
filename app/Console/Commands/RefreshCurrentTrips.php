<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\DataProviders\Repositories\TripRepository;
use App\Enum\TripSource;
use App\Jobs\RefreshTrip;
use Illuminate\Console\Command;

class RefreshCurrentTrips extends Command
{
    protected $signature = 'trwl:refreshTrips';

    protected $description = 'Queue real-time refresh jobs for all currently active trips';

    public function __construct(private readonly TripRepository $tripRepository)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $trips = $this->tripRepository->getCurrentActiveTrips(TripSource::TRANSITOUS);

        if ($trips->isEmpty()) {
            $this->info('No trips to be refreshed');

            return 0;
        }

        $this->info('Queuing refresh jobs for ' . $trips->count() . ' trips...');

        foreach ($trips as $trip) {
            RefreshTrip::dispatch($trip);
        }

        return 0;
    }
}
