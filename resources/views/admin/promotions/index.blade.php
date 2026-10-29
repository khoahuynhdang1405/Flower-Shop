@php
    /*
     * Chỉ hiển thị các cột đang có trong bảng promotions. Tên chương trình, thời hạn, giới hạn lượt,
     * trạng thái Sắp diễn ra/Đã kết thúc và loại miễn phí giao trên thiết kế chưa có cột (docs/design-vs-db-gaps.md #8).
     */
    // Chữ thuần (cùng định dạng x-money) để chèn vào câu dịch.
    $money = fn (int $amount): string => number_format($amount, 0, '', __('storefront.thousands_separator')).' '.__('storefront.currency');
    $cell = 'px-4 py-7 first:pl-6 last:pr-6 whitespace-nowrap';
    $editLink = 'inline-flex items-center gap-2 rounded text-sm whitespace-nowrap transition-colors hover:text-lh-primary focus-visible:outline-2 focus-visible:outline-lh-primary';
@endphp

<x-layouts.admin :title="__('admin.menu.promotions')">
    <x-slot:page-header>
        <x-admin.page-header :title="__('admin.promotions.title')" :description="__('admin.promotions.description')">
            <x-slot:actions>
                <x-admin.create-button :href="route('admin.promotions.create')">{{ __('admin.promotions.create') }}</x-admin.create-button>
            </x-slot:actions>
        </x-admin.page-header>
    </x-slot:page-header>

    <x-admin.flash class="mt-8" />

    <div class="mt-8 grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
        <x-admin.stat-card :label="__('admin.promotions.stats.active')" :value="$stats['active']"
            :note="__('admin.promotions.stats.active_note', ['count' => $stats['active']])" />
        <x-admin.stat-card :label="__('admin.promotions.stats.usage')" :value="$stats['usage']"
            :note="__('admin.promotions.stats.usage_note')" />
        <x-admin.stat-card :label="__('admin.promotions.stats.paused')" :value="$stats['paused']"
            :note="__('admin.promotions.stats.paused_note')" />
        <x-admin.stat-card :label="__('admin.promotions.stats.codes')" :value="$stats['total']"
            :note="__('admin.promotions.stats.codes_note', ['active' => $stats['active'], 'paused' => $stats['paused']])" />
    </div>

    <section data-list-filter aria-labelledby="promotions-table-title" class="mt-8 overflow-hidden rounded-md border border-lh-border bg-lh-surface">
        <div class="px-6 pt-8">
            <h2 id="promotions-table-title" class="text-xl font-semibold">{{ __('admin.promotions.table_title') }}</h2>
            <p class="mt-2 text-[13px] text-lh-muted">{{ __('admin.promotions.table_description') }}</p>

            @if ($promotions->isNotEmpty())
                <x-admin.list-toolbar :placeholder="__('admin.promotions.search_placeholder')" class="mt-10 mb-6"
                    :filters="[
                        ['key' => 'type', 'label' => __('admin.promotions.filter_type'), 'all' => __('admin.promotions.all_types'), 'options' => App\Enums\DiscountType::options()],
                        ['key' => 'status', 'label' => __('admin.promotions.filter_status'), 'all' => __('admin.promotions.all_statuses'), 'options' => __('admin.promotions.statuses')],
                    ]" />
            @endif
        </div>

        @if ($promotions->isEmpty())
            <div class="border-t border-lh-border px-6 py-14 text-center">
                <p class="font-serif text-2xl">{{ __('admin.promotions.empty_title') }}</p>
                <p class="mt-2 text-sm text-lh-muted">{{ __('admin.promotions.empty_body') }}</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] text-left">
                    <thead class="bg-lh-canvas text-[13px] text-lh-muted">
                        <tr>
                            @foreach (['code', 'value', 'condition', 'usage', 'status', 'actions'] as $column)
                                <th scope="col" class="px-4 py-5 font-normal whitespace-nowrap first:pl-6 last:pr-6">{{ __('admin.promotions.columns.'.$column) }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody aria-live="polite">
                        @foreach ($promotions as $promotion)
                            @php
                                $status = $promotion->is_active ? 'active' : 'paused';
                                $isPercentage = $promotion->discount_type === App\Enums\DiscountType::Percentage;
                            @endphp
                            <tr data-list-item data-search="{{ $promotion->code }}" data-type="{{ $promotion->discount_type->value }}"
                                data-status="{{ $status }}" class="border-t border-lh-border">
                                <td class="{{ $cell }} text-sm font-semibold">{{ $promotion->code }}</td>
                                <td class="{{ $cell }}">
                                    <p class="text-[15px] font-semibold">
                                        @if ($isPercentage)
                                            {{ $promotion->discount_value }}%
                                        @else
                                            <x-money :amount="$promotion->discount_value" />
                                        @endif
                                    </p>
                                    <p class="mt-1.5 text-xs text-lh-muted">{{ $promotion->discount_type->label() }}</p>
                                    <p class="mt-1 text-xs text-lh-muted">
                                        @if (! $isPercentage)
                                            {{ __('admin.promotions.direct_discount') }}
                                        @elseif ($promotion->max_discount_amount)
                                            {{ __('admin.promotions.max_discount', ['amount' => $money($promotion->max_discount_amount)]) }}
                                        @else
                                            {{ __('admin.promotions.no_max') }}
                                        @endif
                                    </p>
                                </td>
                                <td class="{{ $cell }} text-sm">
                                    {{ $promotion->min_order_amount > 0
                                        ? __('admin.promotions.min_order', ['amount' => $money($promotion->min_order_amount)])
                                        : __('admin.promotions.any_order') }}
                                </td>
                                <td class="{{ $cell }} text-[15px] tabular-nums">{{ (int) $promotion->orders_count }}</td>
                                <td class="{{ $cell }}">
                                    <x-admin.badge :tone="$promotion->is_active ? 'success' : 'neutral'">{{ __('admin.promotions.statuses.'.$status) }}</x-admin.badge>
                                </td>
                                <td class="{{ $cell }}">
                                    <div class="flex items-center gap-5">
                                        <a href="{{ route('admin.promotions.edit', $promotion) }}" class="{{ $editLink }}"
                                            aria-label="{{ __('admin.list.edit_item', ['name' => $promotion->code]) }}">
                                            <x-lucide-pencil class="size-[18px] stroke-[1.5]" aria-hidden="true" />
                                            {{ __('admin.list.edit') }}
                                        </a>
                                        <x-admin.delete-button :action="route('admin.promotions.destroy', $promotion)"
                                            :label="__('admin.list.delete_item', ['name' => $promotion->code])"
                                            :confirm="__('admin.promotions.confirm_delete', ['code' => $promotion->code])" />
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <p data-list-empty hidden class="border-t border-lh-border px-6 py-10 text-center text-sm text-lh-muted">{{ __('admin.list.empty') }}</p>
            <p data-list-count data-all="{{ __('admin.promotions.showing_all', ['total' => ':total']) }}" data-some="{{ __('admin.promotions.showing_some', ['shown' => ':shown', 'total' => ':total']) }}"
                class="border-t border-lh-border px-6 py-6 text-[13px] text-lh-muted" aria-live="polite">{{ __('admin.promotions.showing_all', ['total' => $promotions->count()]) }}</p>
        @endif
    </section>

    <section aria-labelledby="checklist-title" class="mt-8 rounded-md border border-lh-border bg-lh-surface px-8 py-8 lg:w-1/2">
        <h2 id="checklist-title" class="text-xl font-semibold">{{ __('admin.promotions.checklist_title') }}</h2>
        <ul class="mt-6 flex flex-col gap-5 text-[15px] text-lh-muted">
            @foreach (__('admin.promotions.checklist') as $rule)
                <li class="flex items-start gap-2">
                    <x-lucide-check class="mt-1 size-3.5 shrink-0 stroke-[1.5]" aria-hidden="true" />
                    {{ $rule }}
                </li>
            @endforeach
        </ul>
    </section>
</x-layouts.admin>
