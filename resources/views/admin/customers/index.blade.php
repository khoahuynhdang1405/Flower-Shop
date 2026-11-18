@php
    /**
     * @var \Illuminate\Contracts\Pagination\LengthAwarePaginator<int, \App\Models\User> $customers
     * @var array{total: int, newToday: int, newTodayChange: int, returning: int, returningPercent: int, bestFriends: int} $stats
     * @var \App\DTOs\Customer\AdminCustomerFilters $filters
     */
    use App\Enums\CustomerGroup;
    use App\Enums\CustomerSort;

    $isFiltered = $filters->search !== null || $filters->group !== null;
    $change = $stats['newTodayChange'];
    $cell = 'px-4 py-6 align-middle first:pl-6 last:pr-6';
    $link = 'rounded text-sm transition-colors hover:text-lh-primary focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lh-primary';
@endphp

<x-layouts.admin :title="__('admin.menu.customers')">
    <x-slot:page-header>
        <x-admin.page-header :title="__('admin.customers.title')" :description="__('admin.customers.description')" />
    </x-slot:page-header>

    <x-admin.flash class="mt-8" />

    <div class="mt-8 grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
        <x-admin.stat-card :label="__('admin.customers.stats.total')" :value="$stats['total']"
            :note="__('admin.customers.stats.total_note')" />
        <x-admin.stat-card :label="__('admin.customers.stats.new_today')" :value="$stats['newToday']"
            :note="__('admin.customers.stats.new_today_note', [
                'date' => now()->format('d/m/Y'),
                'change' => ($change > 0 ? '+' : '').$change,
            ])" />
        <x-admin.stat-card :label="__('admin.customers.stats.returning')" :value="$stats['returning']"
            :note="__('admin.customers.stats.returning_note', ['percent' => $stats['returningPercent']])" />
        <x-admin.stat-card :label="__('admin.customers.stats.best_friends')" :value="$stats['bestFriends']"
            :note="__('admin.customers.stats.best_friends_note')" />
    </div>

    <section aria-labelledby="customers-table-title" class="mt-8 overflow-hidden rounded-md border border-lh-border bg-lh-surface">
        <div class="px-6 pt-8">
            <h2 id="customers-table-title" class="text-xl font-semibold">{{ __('admin.customers.table_title') }}</h2>
            <p class="mt-2 text-[13px] text-lh-muted">{{ __('admin.customers.table_description') }}</p>

            <form method="GET" action="{{ route('admin.customers') }}" role="search" class="mt-10 mb-6">
                <x-admin.list-toolbar server :search="$filters->search" :placeholder="__('admin.customers.search_placeholder')"
                    :filters="[
                        [
                            'key' => 'group',
                            'label' => __('admin.customers.filter_group'),
                            'all' => __('admin.customers.all_groups'),
                            'options' => CustomerGroup::options(),
                            'value' => $filters->group?->value,
                        ],
                        [
                            'key' => 'sort',
                            'label' => __('admin.customers.sort_label'),
                            'all' => CustomerSort::RecentlyUpdated->label(),
                            'options' => collect(CustomerSort::options())->except(CustomerSort::RecentlyUpdated->value)->all(),
                            'value' => $filters->sort === CustomerSort::RecentlyUpdated ? null : $filters->sort->value,
                        ],
                    ]" />
            </form>
        </div>

        @if ($customers->isEmpty())
            <div class="border-t border-lh-border px-6 py-14 text-center">
                @if ($isFiltered)
                    <p class="text-sm text-lh-muted">{{ __('admin.customers.empty_filtered') }}</p>
                    <a href="{{ route('admin.customers') }}"
                        class="mt-4 inline-block rounded text-sm font-medium text-lh-primary underline-offset-4 hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lh-primary">
                        {{ __('admin.customers.clear_filters') }}
                    </a>
                @else
                    <p class="font-serif text-2xl">{{ __('admin.customers.empty_title') }}</p>
                    <p class="mt-2 text-sm text-lh-muted">{{ __('admin.customers.empty_body') }}</p>
                @endif
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1040px] text-left">
                    <thead class="bg-lh-canvas text-[13px] text-lh-muted">
                        <tr>
                            @foreach (['customer', 'contact', 'orders', 'spent', 'group', 'last_order', 'actions'] as $column)
                                <th scope="col" class="px-4 py-5 font-normal whitespace-nowrap first:pl-6 last:pr-6">{{ __('admin.customers.columns.'.$column) }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($customers as $customer)
                            <tr class="border-t border-lh-border">
                                <td class="{{ $cell }}">
                                    <p class="text-[15px] font-semibold">{{ $customer->name }}</p>
                                    <p class="mt-1.5 text-xs text-lh-muted">{{ $customer->customer_code }}</p>
                                </td>
                                <td class="{{ $cell }}">
                                    <p @class(['text-[15px] tabular-nums', 'text-lh-muted' => blank($customer->phone)])>
                                        {{ $customer->phone ?: __('admin.customers.no_phone') }}
                                    </p>
                                    <p class="mt-1.5 text-[13px] break-all text-lh-muted">{{ $customer->email }}</p>
                                </td>
                                <td class="{{ $cell }} text-[15px] tabular-nums">{{ $customer->orders_count }}</td>
                                <td class="{{ $cell }} text-[15px] tabular-nums whitespace-nowrap">
                                    {{ number_format($customer->spent, 0, '', __('storefront.thousands_separator')) }}
                                </td>
                                <td class="{{ $cell }}">
                                    <x-admin.badge :tone="$customer->customer_group->badgeTone()">{{ $customer->customer_group->label() }}</x-admin.badge>
                                </td>
                                <td @class([$cell, 'text-[15px] tabular-nums whitespace-nowrap', 'text-lh-muted' => ! $customer->last_order_at])>
                                    {{ $customer->last_order_at?->format('d/m/Y') ?? __('admin.customers.no_order') }}
                                </td>
                                <td class="{{ $cell }} whitespace-nowrap">
                                    <a href="{{ route('admin.customers.show', $customer) }}" class="{{ $link }}"
                                        aria-label="{{ __('admin.customers.view_label', ['name' => $customer->name]) }}">{{ __('admin.customers.view') }}</a>
                                    <span aria-hidden="true" class="text-lh-muted">/</span>
                                    <a href="{{ route('admin.customers.show', $customer) }}#edit" class="{{ $link }}"
                                        aria-label="{{ __('admin.customers.edit_label', ['name' => $customer->name]) }}">{{ __('admin.customers.edit') }}</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex flex-col gap-4 border-t border-lh-border px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-[13px] text-lh-muted">{{ __('admin.customers.showing', ['shown' => $customers->count(), 'total' => $customers->total()]) }}</p>
                <x-admin.pagination :paginator="$customers" />
            </div>
        @endif
    </section>

    <aside class="mt-8 rounded-md bg-lh-sage-50 px-6 py-6">
        <p class="text-[15px] font-semibold">{{ __('admin.customers.reminder.title') }}</p>
        <p class="mt-2 text-sm text-lh-muted">{{ __('admin.customers.reminder.body') }}</p>
    </aside>
</x-layouts.admin>
