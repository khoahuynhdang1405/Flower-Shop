<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Customer\ListAdminCustomersAction;
use App\Actions\Customer\ShowAdminCustomerAction;
use App\Actions\Customer\UpdateCustomerAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ListCustomersRequest;
use App\Http\Requests\Admin\UpdateCustomerRequest;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Admin › Khách hàng: danh sách, xem chi tiết (kèm lịch sử đơn) và sửa thông tin khách.
 */
class CustomerController extends Controller
{
    public function index(ListCustomersRequest $request, ListAdminCustomersAction $listCustomers): View
    {
        $filters = $request->toFilters();

        return view('admin.customers.index', [
            ...$listCustomers->execute($filters, CarbonImmutable::now()),
            'filters' => $filters,
        ]);
    }

    public function show(User $customer, ShowAdminCustomerAction $showCustomer): View
    {
        return view('admin.customers.show', ['customer' => $showCustomer->execute($customer)]);
    }

    public function update(UpdateCustomerRequest $request, User $customer, UpdateCustomerAction $updateCustomer): RedirectResponse
    {
        $updateCustomer->execute($customer, $request->toData());

        return redirect()->route('admin.customers.show', $customer)
            ->with('status', __('admin.customers.flash.updated', ['name' => $customer->name]));
    }
}
