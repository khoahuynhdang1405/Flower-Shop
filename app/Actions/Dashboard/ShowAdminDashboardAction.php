<?php

declare(strict_types=1);

namespace App\Actions\Dashboard;

use App\DTOs\Dashboard\RevenueChart;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Database\Eloquent\Collection;

/**
 * Số liệu trang Tổng quan: 4 thẻ số liệu, biểu đồ doanh thu, việc cần làm, đơn gần đây, sản phẩm nổi bật.
 */
final class ShowAdminDashboardAction
{
    public const LOW_STOCK_THRESHOLD = 5;

    public const RECENT_ORDERS = 5;

    public const FEATURED_PRODUCTS = 3;

    /**
     * Khung giờ kết thúc không muộn hơn mốc này được tính là "cần giao trước 12:00".
     */
    public const MORNING_DEADLINE = '12:00';

    public function __construct(
        private OrderRepositoryInterface $orders,
        private ProductRepositoryInterface $products,
        private UserRepositoryInterface $users,
        private BuildRevenueChartAction $buildRevenueChart,
        private Config $config,
    ) {}

    /**
     * @return array{
     *     revenue: RevenueChart,
     *     newOrders: array{today: int, yesterday: int},
     *     newCustomers: array{today: int, yesterday: int},
     *     products: array{active: int, lowStock: int, total: int},
     *     awaitingConfirmation: int,
     *     flowersBeforeNoon: int,
     *     recentOrders: Collection<int, Order>,
     *     featuredProducts: Collection<int, Product>,
     * }
     */
    public function execute(CarbonImmutable $today): array
    {
        $todayStart = $today->startOfDay();
        $yesterdayStart = $todayStart->subDay();
        $lowStock = $this->products->countActiveWithStockAtMost(self::LOW_STOCK_THRESHOLD);

        return [
            'revenue' => $this->buildRevenueChart->execute($today),
            'newOrders' => [
                'today' => $this->orders->countCreatedBetween($todayStart, $todayStart->endOfDay()),
                'yesterday' => $this->orders->countCreatedBetween($yesterdayStart, $yesterdayStart->endOfDay()),
            ],
            'newCustomers' => [
                'today' => $this->users->countCreatedBetween($todayStart, $todayStart->endOfDay()),
                'yesterday' => $this->users->countCreatedBetween($yesterdayStart, $yesterdayStart->endOfDay()),
            ],
            'products' => [
                'active' => $this->products->countActive(),
                'lowStock' => $lowStock,
                'total' => $this->products->countAll(),
            ],
            'awaitingConfirmation' => $this->orders->countInStatus(OrderStatus::Received),
            'flowersBeforeNoon' => $this->orders->sumFlowersToDeliver($today, $this->morningTimeSlots()),
            'recentOrders' => $this->orders->findLatestWithSummary(self::RECENT_ORDERS),
            'featuredProducts' => $this->products->findFeaturedForSale(self::FEATURED_PRODUCTS),
        ];
    }

    /**
     * @return list<string>
     */
    private function morningTimeSlots(): array
    {
        $slots = $this->config->get('shop.delivery_time_slots');

        return array_values(array_filter(
            $slots,
            fn (string $slot): bool => substr($slot, -5) <= self::MORNING_DEADLINE,
        ));
    }
}
