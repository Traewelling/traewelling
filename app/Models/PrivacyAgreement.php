<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * 
 *
 * @property int $id
 * @property string $body_md_de
 * @property string $body_md_en
 * @property Carbon $valid_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder<static>|PrivacyAgreement newModelQuery()
 * @method static Builder<static>|PrivacyAgreement newQuery()
 * @method static Builder<static>|PrivacyAgreement query()
 * @method static Builder<static>|PrivacyAgreement whereBodyMdDe($value)
 * @method static Builder<static>|PrivacyAgreement whereBodyMdEn($value)
 * @method static Builder<static>|PrivacyAgreement whereCreatedAt($value)
 * @method static Builder<static>|PrivacyAgreement whereId($value)
 * @method static Builder<static>|PrivacyAgreement whereUpdatedAt($value)
 * @method static Builder<static>|PrivacyAgreement whereValidAt($value)
 * @mixin Eloquent
 */
class PrivacyAgreement extends Model
{
    protected $fillable = ['body_md_de', 'body_md_en', 'valid_at'];
    protected $casts    = [
        'id'       => 'integer',
        'valid_at' => 'datetime',
    ];
}
