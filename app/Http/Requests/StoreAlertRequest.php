<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property string $type
 * @property string $active_from
 * @property string $active_until
 * @property string $title_de
 * @property string $content_de
 * @property string $title_en
 * @property string $content_en
 * @property string $url_de
 * @property string $url_en
 * @property string $url
 */
class StoreAlertRequest extends FormRequest
{
    private const string MAX_255 = 'max:255';

    public function rules(): array
    {
        return [
            'type' => ['required', 'string', self::MAX_255],
            'active_from' => ['required', 'date'],
            'active_until' => ['nullable', 'date', 'after_or_equal:active_from'],
            'title_de' => ['required', 'string', self::MAX_255],
            'content_de' => ['required', 'string', self::MAX_255],
            'title_en' => ['required', 'string', self::MAX_255],
            'content_en' => ['required', 'string', self::MAX_255],
            'url_de' => ['nullable', 'url', self::MAX_255],
            'url_en' => ['nullable', 'url', self::MAX_255],
            'url' => ['nullable', 'url', self::MAX_255],
        ];
    }
}
