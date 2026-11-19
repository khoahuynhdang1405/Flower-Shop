<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\ChartRange;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Query string của API biểu đồ Tổng quan: ?range=7|30 (số ngày, mặc định 7).
 */
class ShowRevenueChartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('admin')->check();
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'range' => ['nullable', Rule::enum(ChartRange::class)],
        ];
    }

    public function range(): ChartRange
    {
        return ChartRange::tryFrom((int) $this->validated('range')) ?? ChartRange::Week;
    }
}
