<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $body_md_de
 * @property string $body_md_en
 * @property Carbon $valid_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PrivacyAgreement newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PrivacyAgreement newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PrivacyAgreement query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PrivacyAgreement whereBodyMdDe($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PrivacyAgreement whereBodyMdEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PrivacyAgreement whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PrivacyAgreement whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PrivacyAgreement whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PrivacyAgreement whereValidAt($value)
 *
 * @mixin \Eloquent
 */
class PrivacyAgreement extends Model
{
    protected $fillable = ['body_md_de', 'body_md_en', 'valid_at'];

    protected $casts = [
        'id' => 'integer',
        'valid_at' => 'datetime',
    ];
}
