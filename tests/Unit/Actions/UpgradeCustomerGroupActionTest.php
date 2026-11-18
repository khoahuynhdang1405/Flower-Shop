<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\Customer\UpgradeCustomerGroupAction;
use App\Enums\CustomerGroup;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tự nâng nhóm khách theo tổng chi tiêu, với repository giả.
 */
class UpgradeCustomerGroupActionTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private UserRepositoryInterface&MockInterface $users;

    protected function setUp(): void
    {
        parent::setUp();

        $this->users = Mockery::mock(UserRepositoryInterface::class);
    }

    /**
     * @return array<string, array{CustomerGroup, int, CustomerGroup}>
     */
    public static function upgrades(): array
    {
        return [
            'đúng 10 triệu lên thân thiết' => [CustomerGroup::NewFriend, 10_000_000, CustomerGroup::Loyal],
            'trên 10 triệu lên thân thiết' => [CustomerGroup::NewFriend, 15_500_000, CustomerGroup::Loyal],
            'đúng 20 triệu lên bạn thân' => [CustomerGroup::NewFriend, 20_000_000, CustomerGroup::BestFriend],
            'thân thiết qua 20 triệu lên bạn thân' => [CustomerGroup::Loyal, 21_000_000, CustomerGroup::BestFriend],
        ];
    }

    #[DataProvider('upgrades')]
    public function test_upgrades_by_completed_spending(CustomerGroup $current, int $spent, CustomerGroup $expected): void
    {
        $customer = $this->customer($current);
        $this->users->expects('findById')->with(5)->andReturn($customer);
        $this->users->expects('sumCompletedSpending')->with(5)->andReturn($spent);
        $this->users->expects('assignGroup')->with($customer, $expected);

        $this->assertSame($expected, (new UpgradeCustomerGroupAction($this->users))->execute(5));
    }

    /**
     * @return array<string, array{CustomerGroup, int}>
     */
    public static function unchanged(): array
    {
        return [
            'dưới 10 triệu giữ khách mới' => [CustomerGroup::NewFriend, 9_999_999],
            'thân thiết chưa đủ 20 triệu' => [CustomerGroup::Loyal, 19_000_000],
            'bạn thân do admin gán không bị hạ' => [CustomerGroup::BestFriend, 2_000_000],
        ];
    }

    #[DataProvider('unchanged')]
    public function test_never_downgrades_or_upgrades_below_threshold(CustomerGroup $current, int $spent): void
    {
        $this->users->expects('findById')->andReturn($this->customer($current));
        $this->users->expects('sumCompletedSpending')->andReturn($spent);
        $this->users->expects('assignGroup')->never();

        $this->assertNull((new UpgradeCustomerGroupAction($this->users))->execute(5));
    }

    public function test_missing_customer_is_ignored(): void
    {
        $this->users->expects('findById')->with(5)->andReturn(null);
        $this->users->expects('sumCompletedSpending')->never();

        $this->assertNull((new UpgradeCustomerGroupAction($this->users))->execute(5));
    }

    private function customer(CustomerGroup $group): User
    {
        return (new User)->forceFill(['id' => 5, 'customer_group' => $group]);
    }
}
