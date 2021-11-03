<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrivacyAgreement extends Model
{
    protected $fillable = ['body_md_de', 'body_md_en', 'valid_at'];
    protected $casts    = [
        'id'       => 'integer',
        'valid_at' => 'datetime',
    ];
}
