<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Enums\Season;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class SeasonTest extends TestCase
{
    /**
     * @return array<string, array{int, Season}>
     */
    public static function monthProvider(): array
    {
        return [
            'tháng 2 là xuân' => [2, Season::Spring],
            'tháng 4 là xuân' => [4, Season::Spring],
            'tháng 5 là hạ' => [5, Season::Summer],
            'tháng 7 là hạ' => [7, Season::Summer],
            'tháng 8 là thu' => [8, Season::Autumn],
            'tháng 10 là thu' => [10, Season::Autumn],
            'tháng 11 là đông' => [11, Season::Winter],
            'tháng 12 là đông' => [12, Season::Winter],
            'tháng 1 là cuối đông' => [1, Season::Winter],
        ];
    }

    #[DataProvider('monthProvider')]
    public function test_each_season_spans_three_months(int $month, Season $expected): void
    {
        $this->assertSame($expected, Season::fromMonth($month));
    }
}
