<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\Customer\ListAdminCustomersAction;
use App\Actions\Customer\ShowAdminCustomerAction;
use App\Actions\Customer\UpdateCustomerAction;
use App\DTOs\Customer\AdminCustomerFilters;
use App\DTOs\Customer\UpdateCustomerData;
use App\Enums\CustomerGroup;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

/**
 * Nghiệp vụ Admin › Khách hàng, với repository giả (không chạm database).
 */
class CustomerActionsTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private UserRepositoryInterface&MockInterface $users;

    protected function setUp(): void
    {
        parent::setUp();

        $this->users = Mockery::mock(UserRepositoryInterface::class);
    }

    public function test_list_builds_stats_for_today_and_yesterday(): void
    {
        $now = CarbonImmutable::parse('2026-10-07 09:00:00');
        $filters = new AdminCustomerFilters(search: 'linh');
        $page = Mockery::mock(LengthAwarePaginator::class);

        $this->users->shouldReceive('paginateCustomersForAdmin')->once()->with($filters, 8)->andReturn($page);
        $this->users->shouldReceive('countAll')->once()->andReturn(320);
        $this->users->shouldReceive('countCreatedBetween')->once()
            ->withArgs(fn ($from, $to): bool => $from->equalTo($now->startOfDay()) && $to->equalTo($now->endOfDay()))->andReturn(12);
        $this->users->shouldReceive('countCreatedBetween')->once()
            ->withArgs(fn ($from, $to): bool => $from->equalTo($now->subDay()->startOfDay()) && $to->equalTo($now->subDay()->endOfDay()))->andReturn(8);
        $this->users->shouldReceive('countWithAtLeastOrders')->once()->with(2)->andReturn(96);
        $this->users->shouldReceive('countInGroup')->once()->with(CustomerGroup::BestFriend)->andReturn(28);

        $result = (new ListAdminCustomersAction($this->users))->execute($filters, $now);

        $this->assertSame($page, $result['customers']);
        $this->assertSame(
            ['total' => 320, 'newToday' => 12, 'newTodayChange' => 4, 'returning' => 96, 'returningPercent' => 30, 'bestFriends' => 28],
            $result['stats'],
        );
    }

    public function test_list_returns_zero_percent_when_there_are_no_customers(): void
    {
        $this->users->shouldReceive('paginateCustomersForAdmin')->andReturn(Mockery::mock(LengthAwarePaginator::class));
        $this->users->shouldReceive('countAll')->andReturn(0);
        $this->users->shouldReceive('countCreatedBetween')->andReturn(0);
        $this->users->shouldReceive('countWithAtLeastOrders')->andReturn(0);
        $this->users->shouldReceive('countInGroup')->andReturn(0);

        $result = (new ListAdminCustomersAction($this->users))->execute(new AdminCustomerFilters, CarbonImmutable::now());

        $this->assertSame(0, $result['stats']['returningPercent']);
    }

    public function test_show_loads_the_ten_most_recent_orders(): void
    {
        $customer = new User;
        $loaded = new User;
        $this->users->shouldReceive('loadOrderHistory')->once()->with($customer, 10)->andReturn($loaded);

        $this->assertSame($loaded, (new ShowAdminCustomerAction($this->users))->execute($customer));
    }

    public function test_update_passes_normalised_attributes_to_the_repository(): void
    {
        $customer = new User;
        $this->users->shouldReceive('update')->once()->with($customer, [
            'name' => 'Nguyễn Minh Anh',
            'phone' => '0901234567',
            'birthday' => '1998-03-08',
            'customer_group' => CustomerGroup::BestFriend,
        ]);

        (new UpdateCustomerAction($this->users))->execute($customer, new UpdateCustomerData(
            name: 'Nguyễn Minh Anh',
            phone: '0901234567',
            birthday: CarbonImmutable::parse('1998-03-08'),
            group: CustomerGroup::BestFriend,
        ));
    }
}
