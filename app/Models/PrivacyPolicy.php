<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $body_md_de
 * @property string $body_md_en
 * @property Carbon $valid_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PrivacyPolicy newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PrivacyPolicy newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PrivacyPolicy query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PrivacyPolicy whereBodyMdDe($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PrivacyPolicy whereBodyMdEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PrivacyPolicy whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PrivacyPolicy whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PrivacyPolicy whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PrivacyPolicy whereValidAt($value)
 *
 * @mixin \Eloquent
 */
class PrivacyPolicy extends Model
{
    use HasFactory;

    protected $fillable = ['body_md_de', 'body_md_en', 'valid_at'];

    protected $casts = [
        'id' => 'integer',
        'valid_at' => 'datetime',
    ];

    public function privacyPolicyAcceptance(): HasMany
    {
        return $this->hasMany(PrivacyPolicyAcceptance::class);
    }
}
