<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Query string của trang Lịch cắm hoa: ?date=Y-m-d (bỏ trống = hôm nay).
 */
class ShowFloristScheduleRequest extends FormRequest
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
            'date' => ['nullable', 'date_format:Y-m-d'],
        ];
    }

    public function deliveryDate(): CarbonImmutable
    {
        $date = $this->validated('date');

        return $date === null ? CarbonImmutable::today() : CarbonImmutable::createFromFormat('!Y-m-d', $date);
    }
}
