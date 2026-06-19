<?php

declare(strict_types=1);

namespace App\Services\PersonalDataSelection\Exporters;

use App\Models\User;
use App\Services\PersonalDataSelection\Exporters\Base\AbstractExporter;
use App\Services\PersonalDataSelection\Exporters\Base\RelationExportable;

class SocialProfileExporter extends AbstractExporter
{
    use RelationExportable;

    protected string $fileName = 'social_profiles.json';

    protected string $relation = 'socialProfile';

    protected array $with = [];

    protected array $columns = [
        'user_id',
        'mastodon_id',
        'created_at',
        'updated_at',
        'mastodon_visibility',
        'mastodon_username',
        'mastodon_server',
    ];

    public function __construct(User $user)
    {
        parent::__construct($user);

        $this->with = ['mastodonServer' => function ($query) {
            $query->select('id', 'domain', 'created_at', 'updated_at');
        }];
    }
}
