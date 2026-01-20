<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class MastodonServer extends Model
{
    protected $fillable = ['domain', 'client_id', 'client_secret'];

    protected $casts = [
        'id' => 'integer',
    ];

    protected $hidden = ['client_id', 'client_secret'];

    private const CACHE_TTL = 86400; // Cache TTL: 24 hours (MastodonServer data rarely changes)

    public function socialProfiles(): HasMany
    {
        return $this->hasMany(SocialLoginProfile::class, 'mastodon_server', 'id');
    }

    /**
     * Find a MastodonServer by ID with caching
     */
    public static function findCached(int $id): ?self
    {
        return Cache::remember(
            "mastodon_server_{$id}",
            self::CACHE_TTL,
            fn () => self::find($id)
        );
    }

    /**
     * Find a MastodonServer by domain with caching
     */
    public static function findByDomainCached(string $domain): ?self
    {
        return Cache::remember(
            "mastodon_server_domain_{$domain}",
            self::CACHE_TTL,
            fn () => self::where('domain', $domain)->first()
        );
    }

    /**
     * Clear cache for this server instance
     */
    public function clearCache(): void
    {
        Cache::forget("mastodon_server_{$this->id}");
        Cache::forget("mastodon_server_domain_{$this->domain}");
    }

    /**
     * Override save to clear cache
     */
    public function save(array $options = []): bool
    {
        $result = parent::save($options);
        if ($result) {
            $this->clearCache();
        }

        return $result;
    }

    /**
     * Override delete to clear cache
     */
    public function delete(): ?bool
    {
        $this->clearCache();

        return parent::delete();
    }
}
