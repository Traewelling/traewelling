<?php

use App\Models\PrivacyPolicy;
use App\Models\PrivacyPolicyAcceptance;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration
{
    public function up(): void
    {
        $currentPolicy = PrivacyPolicy::orderByDesc('valid_at')->first();

        if (!$currentPolicy) {
            return;
        }

        DB::table('users')
            ->where('privacy_ack_at', '>=', $currentPolicy->valid_at)
            ->orderByDesc('privacy_ack_at')
            ->chunk(100, function ($users) use ($currentPolicy) {
                foreach ($users as $user) {
                    if ($user->privacy_ack_at >= $currentPolicy->valid_at) {
                        PrivacyPolicyAcceptance::create([
                            'privacy_policy_id' => $currentPolicy->id,
                            'user_id' => $user->uuid,
                            'accepted_at' => $user->privacy_ack_at,
                        ]);
                    }
                }
            });

        DB::table('users')
            ->where('privacy_ack_at', '<', $currentPolicy->valid_at)
            ->orderByDesc('privacy_ack_at')
            ->chunk(100, function ($users) use ($currentPolicy) {
                foreach ($users as $user) {
                    // get fitting ID
                    $policy = PrivacyPolicy::where('valid_at', '<=', $currentPolicy->valid_at)->orderByDesc('valid_at')->first();
                    PrivacyPolicyAcceptance::create([
                        'privacy_policy_id' => $policy->id,
                        'user_id' => $user->uuid,
                        'accepted_at' => $user->privacy_ack_at,
                    ]);
                }
            });
    }

    public function down(): void
    {
        DB::table('privacy_policy_acceptances')
            ->orderByDesc('accepted_at')
            ->groupBy('user_id')
            ->selectRaw('user_id, MAX(accepted_at) as accepted_at')
            ->chunk(100, function ($acks) {
                foreach ($acks as $ack) {
                    User::where('uuid', $ack->user_id)
                        ->update([
                            'privacy_ack_at' => $ack->accepted_at,
                        ]);
                }
            });
    }
};
