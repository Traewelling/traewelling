<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Dto\RouteMap\RouteMapFilterDto;
use App\Enum\Business;
use App\Enum\HafasTravelType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class RouteMapRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'from' => ['nullable', 'date'],
            'until' => ['nullable', 'date', 'after_or_equal:from'],
            'travelTypes' => ['nullable', 'array'],
            'travelTypes.*' => ['string', Rule::in(array_column(HafasTravelType::cases(), 'value'))],
            'travelPurposes' => ['nullable', 'array'],
            'travelPurposes.*' => ['integer', Rule::in(array_column(Business::cases(), 'value'))],
            'includeApproximated' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Both `travelTypes[]=bus&travelTypes[]=tram` and `travelTypes=bus,tram` are accepted
     */
    protected function prepareForValidation(): void
    {
        foreach (['travelTypes', 'travelPurposes'] as $key) {
            $value = $this->input($key);

            if (is_string($value)) {
                $this->merge([$key => array_values(array_filter(array_map('trim', explode(',', $value)), 'strlen'))]);
            }
        }

        $includeApproximated = $this->input('includeApproximated');
        if (is_string($includeApproximated)) {
            $normalized = filter_var($includeApproximated, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($normalized !== null) {
                $this->merge(['includeApproximated' => $normalized]);
            }
        }

        if (is_array($this->input('travelPurposes'))) {
            $this->merge([
                'travelPurposes' => array_map(
                    static fn ($purpose) => is_numeric($purpose) ? (int) $purpose : $purpose,
                    $this->input('travelPurposes')
                ),
            ]);
        }
    }

    public function toFilter(): RouteMapFilterDto
    {
        $validated = $this->validated();

        return new RouteMapFilterDto(
            from: isset($validated['from']) ? Carbon::parse($validated['from']) : null,
            until: isset($validated['until']) ? Carbon::parse($validated['until']) : null,
            travelTypes: array_map(
                static fn (string $type) => HafasTravelType::from($type),
                $validated['travelTypes'] ?? []
            ),
            travelPurposes: array_map(
                static fn (int $purpose) => Business::from($purpose),
                $validated['travelPurposes'] ?? []
            ),
            includeApproximated: $this->boolean('includeApproximated', true),
        );
    }
}
