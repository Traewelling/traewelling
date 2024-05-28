<?php

namespace App\Console\Commands\DatabaseCleaner;

use App\Models\MastodonServer;
use App\Models\User;
use App\Notifications\InvalidMastodonServer;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MastodonServers extends Command
{
    protected $signature = 'trwl:clean-up-mastodon-servers';

    protected $description = 'Clean up Mastodon servers...';
    private const string DIVIDER       = '====================';
    private const string SMALL_DIVIDER = '-------------------';


    public function handle(): void {
        $this->info('Cleaning up Mastodon servers...');

        // Check for servers without users
        $this->checkServersWithoutUsers();
        // Check for servers where the client credentials are invalid
        $this->checkInvalidServers();
        // Check for servers without users again
        $this->checkServersWithoutUsers();

        $this->info('Finished cleaning up Mastodon servers.');
    }

    private function checkServersWithoutUsers(): void {
        $this->warn(self::DIVIDER);
        $this->info('Checking for servers without users...');
        $result = DB::table('mastodon_servers')
                    ->leftJoin(
                        'social_login_profiles',
                        'mastodon_servers.id',
                        '=',
                        'social_login_profiles.mastodon_server'
                    )
                    ->whereNull('social_login_profiles.mastodon_server')
                    ->limit(1000)
                    ->delete();
        $this->info('Deleted ' . $result . ' servers');
    }

    private function checkInvalidServers(): void {
        $this->warn(self::DIVIDER);
        $this->info('Checking for invalid servers...');
        $servers = MastodonServer::all();

        foreach ($servers as $server) {
            if (!str_starts_with($server->domain, 'http')) {
                $this->info(self::SMALL_DIVIDER);
                $this->info('Server is already inactive: ' . $server->domain);
                continue;
            }

            try {
                $client   = new Client(['timeout' => 10]);
                $response = $client->post($server->domain . '/oauth/token', [
                    'json'        => [
                        'client_id'     => $server->client_id,
                        'client_secret' => $server->client_secret,
                        'grant_type'    => 'client_credentials',
                        'redirect_uri'  => config('trwl.mastodon_redirect'),
                    ],
                    'http_errors' => false,
                ]);

                $status = $response->getStatusCode();
                $body   = json_decode($response->getBody()->getContents(), true);
                //check for error invalid_client
                if ($status === 401 && $body['error'] === 'invalid_client') {
                    $this->info(self::SMALL_DIVIDER);
                    $this->info('Server has invalid credentials: ' . $server->domain . ' (' . $server->id . ')');
                    $this->invalidateServer($server);
                    continue;
                }
                if ($status !== 200) {
                    $this->info(self::SMALL_DIVIDER);
                    $this->alertServerNotOk($status, $server, $response->getBody()->getContents() ?? '');
                    continue;
                }

            } catch (GuzzleException $e) {
                $this->alertServerNotOk($e->getCode(), $server, $e->getMessage());
                continue;
            }
        }
    }

    private function alertServerNotOk(int $statusCode, MastodonServer $server, string $body): void {
        $this->notifyUsers($this->fetchUsers($server), $server);
        $this->warn('Server response is not 200: ' . $server->domain . ' (' . $server->id . ')');
        $this->info('Status: ' . $statusCode);
        $this->info('Body: ' . $body);
        $this->info('Skipping server...');
    }

    private function invalidateServer(MastodonServer $server): void {
        $users = $this->fetchUsers($server);
        $this->notifyUsers($users, $server);
        $unverified = $users->where('email_verified_at', null);
        $verified   = $users->where('email_verified_at', '!=', null);

        foreach ($verified as $user) {
            $user->socialProfile->update([
                                             'mastodon_id'     => null,
                                             'mastodon_token'  => null,
                                             'mastodon_server' => null,
                                         ]);
        }

        $this->info('Found ' . $unverified->count() . ' users without email for server: ' . $server->domain);
        $this->info('Found ' . $verified->count() . ' users with email for server: ' . $server->domain);
        if ($unverified->count() > 0) {
            $this->info('Invalidating server...');
            $server->update([
                                'domain'        => 'Inactive: ' . $server->domain,
                                'client_id'     => '',
                                'client_secret' => '',
                            ]);
        } else {
            $this->info('Deleting server...');
            $server->delete();
        }
    }

    private function fetchUsers(MastodonServer $server): Collection {
        return User::leftJoin('social_login_profiles', 'users.id', '=', 'social_login_profiles.user_id')
                   ->where('social_login_profiles.mastodon_server', $server->id)
                   ->get();
    }

    private function notifyUsers(Collection $users, MastodonServer $server): void {
        foreach ($users as $user) {
            $user->notify(new InvalidMastodonServer($server->domain));
        }
    }
}
