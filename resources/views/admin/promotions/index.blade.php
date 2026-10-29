@php
    /*
     * TODO: dữ liệu mẫu theo docs/designs/admin/07-uu-dai.png — chỉ dựng giao diện.
     * Mã chào mừng dùng CHAOHOA đã chốt (docs/design-vs-db-gaps.md mục 2-#5) thay cho CHAOBAN10 trên ảnh.
     * Khi làm backend: tên chương trình, thời hạn, giới hạn/đã dùng, trạng thái, loại phí giao và điều kiện
     * chưa có trong bảng promotions (docs/design-vs-db-gaps.md #8).
     */
    $promotions = [
        ['code' => 'CHAOHOA', 'name' => 'Chào người bạn mới', 'type' => 'percent', 'value' => 10, 'max' => 100000, 'condition' => 'Đơn đầu từ 500.000 VNĐ', 'period' => '01/10–31/10/2026', 'used' => 36, 'limit' => 200, 'status' => 'active'],
        ['code' => 'THUYEUTHUONG', 'name' => 'Dịu dàng mùa thu', 'type' => 'fixed', 'value' => 50000, 'condition' => 'Bộ sưu tập mùa thu · từ 800.000 VNĐ', 'period' => '01/10–30/11/2026', 'used' => 18, 'limit' => 100, 'status' => 'active'],
        ['code' => 'GIAOHOA0D', 'name' => 'Gửi thương gần hơn', 'type' => 'shipping', 'value' => 0, 'condition' => 'Đơn từ 800.000 VNĐ · TP.HCM', 'period' => '01/10–31/12/2026', 'used' => 42, 'limit' => 500, 'status' => 'active'],
        ['code' => 'THUONGME20', 'name' => 'Gửi mẹ một lời thương', 'type' => 'percent', 'value' => 15, 'max' => 100000, 'min' => 600000, 'condition' => 'Đơn từ 600.000 VNĐ', 'period' => '15/10–20/10/2026', 'starts' => '15/10', 'ends' => '20/10/2026', 'used' => 0, 'limit' => 150, 'status' => 'upcoming'],
        ['code' => 'LAHE2026', 'name' => 'Chút nắng mùa hè', 'type' => 'fixed', 'value' => 30000, 'condition' => 'Đơn từ 500.000 VNĐ', 'period' => '01/08–31/08/2026', 'used' => 85, 'limit' => 100, 'status' => 'ended'],
        ['code' => 'BANCU50', 'name' => 'Dành cho bạn của Lá', 'type' => 'fixed', 'value' => 50000, 'condition' => 'Khách thân thiết · từ 700.000 VNĐ', 'period' => '01/10–31/10/2026', 'used' => 0, 'limit' => 100, 'status' => 'paused'],
    ];

    $all = collect($promotions);
    $active = $all->where('status', 'active');
    $upcoming = $all->firstWhere('status', 'upcoming');
    // Chữ thuần (cùng định dạng x-money) để chèn vào câu dịch.
    $money = fn (int $amount): string => number_format($amount, 0, '', __('storefront.thousands_separator')).' '.__('storefront.currency');
    $statusTone =['active' => 'success', 'upcoming' => 'warning', 'ended' => 'neutral', 'paused' => 'neutral'];
    $editLink = 'inline-flex items-center gap-2 rounded text-sm whitespace-nowrap transition-colors hover:text-lh-primary focus-visible:outline-2 focus-visible:outline-lh-primary';
@endphp

<x-layouts.admin :title="__('admin.menu.promotions')">
    <x-slot:page-header>
        <x-admin.page-header :title="__('admin.promotions.title')" :description="__('admin.promotions.description')">
            <x-slot:actions>
                {{-- TODO: form thêm ưu đãi. --}}
                <x-admin.create-button>{{ __('admin.promotions.create') }}</x-admin.create-button>
            </x-slot:actions>
        </x-admin.page-header>
    </x-slot:page-header>

    <div class="mt-8 grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
        <x-admin.stat-card :label="__('admin.promotions.stats.active')" :value="$active->count()"
            :note="__('admin.promotions.stats.active_note', ['count' => $active->count()])" />
        <x-admin.stat-card :label="__('admin.promotions.stats.usage')" :value="$active->sum('used')"
            :note="__('admin.promotions.stats.usage_note')" />
        <x-admin.stat-card :label="__('admin.promotions.stats.upcoming')" :value="$all->where('status', 'upcoming')->count()"
            :note="$upcoming ? __('admin.promotions.stats.upcoming_note', ['name' => $upcoming['name'], 'date' => $upcoming['starts']]) : null" />
        <x-admin.stat-card :label="__('admin.promotions.stats.codes')" :value="$all->count()"
            :note="__('admin.promotions.stats.codes_note', ['ended' => $all->where('status', 'ended')->count(), 'paused' => $all->where('status', 'paused')->count()])" />
    </div>

    <section data-list-filter aria-labelledby="promotions-table-title" class="mt-8 overflow-hidden rounded-md border border-lh-border bg-lh-surface">
        <div class="px-6 pt-8">
            <h2 id="promotions-table-title" class="text-xl font-semibold">{{ __('admin.promotions.table_title') }}</h2>
            <p class="mt-2 text-[13px] text-lh-muted">{{ __('admin.promotions.table_description') }}</p>

            <x-admin.list-toolbar :placeholder="__('admin.promotions.search_placeholder')" class="mt-10 mb-6"
                :filters="[
                    ['key' => 'type', 'label' => __('admin.promotions.filter_type'), 'all' => __('admin.promotions.all_types'), 'options' => __('admin.promotions.types')],
                    ['key' => 'status', 'label' => __('admin.promotions.filter_status'), 'all' => __('admin.promotions.all_statuses'), 'options' => __('admin.promotions.statuses')],
                ]" />
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[1080px] text-left">
                <thead class="bg-lh-canvas text-[13px] text-lh-muted">
                    <tr>
                        @foreach (['code', 'value', 'condition', 'period', 'usage', 'status', 'actions'] as $column)
                            <th scope="col" class="px-4 py-5 font-normal first:pl-6 last:pr-6 whitespace-nowrap">{{ __('admin.promotions.columns.'.$column) }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody aria-live="polite">
                    @foreach ($promotions as $promotion)
                        <tr data-list-item data-search="{{ $promotion['code'] }} {{ $promotion['name'] }}"
                            data-type="{{ $promotion['type'] }}" data-status="{{ $promotion['status'] }}"
                            class="border-t border-lh-border">
                            <td class="px-4 py-7 first:pl-6 last:pr-6 whitespace-nowrap">
                                <p class="text-sm font-semibold">{{ $promotion['code'] }}</p>
                                <p class="mt-1.5 text-[13px] text-lh-muted">{{ $promotion['name'] }}</p>
                            </td>
                            <td class="px-4 py-7 first:pl-6 last:pr-6 whitespace-nowrap">
                                <p class="text-[15px] font-semibold">
                                    @if ($promotion['type'] === 'percent')
                                        {{ $promotion['value'] }}%
                                    @else
                                        <x-money :amount="$promotion['value']" />
                                    @endif
                                </p>
                                <p class="mt-1.5 text-xs text-lh-muted">{{ __('admin.promotions.types.'.$promotion['type']) }}</p>
                                <p class="mt-1 text-xs text-lh-muted">
                                    @switch($promotion['type'])
                                        @case('percent')
                                            {{ __('admin.promotions.max_discount', ['amount' => $money($promotion['max'])]) }}
                                            @break
                                        @case('shipping')
                                            {{ __('admin.promotions.free_inner_city') }}
                                            @break
                                        @default
                                            {{ __('admin.promotions.direct_discount') }}
                                    @endswitch
                                </p>
                            </td>
                            <td class="px-4 py-7 first:pl-6 last:pr-6 text-sm whitespace-nowrap">{{ $promotion['condition'] }}</td>
                            <td class="px-4 py-7 first:pl-6 last:pr-6 text-[13px] whitespace-nowrap tabular-nums">{{ $promotion['period'] }}</td>
                            <td class="px-4 py-7 first:pl-6 last:pr-6 text-[15px] whitespace-nowrap tabular-nums">{{ $promotion['used'] }} / {{ $promotion['limit'] }}</td>
                            <td class="px-4 py-7 first:pl-6 last:pr-6 whitespace-nowrap">
                                <x-admin.badge :tone="$statusTone[$promotion['status']]">{{ __('admin.promotions.statuses.'.$promotion['status']) }}</x-admin.badge>
                            </td>
                            <td class="px-4 py-7 first:pl-6 last:pr-6 whitespace-nowrap">
                                {{-- TODO: form sửa ưu đãi. --}}
                                <a href="#" class="{{ $editLink }}" aria-label="{{ __('admin.list.edit_item', ['name' => $promotion['code']]) }}">
                                    <x-lucide-pencil class="size-[18px] stroke-[1.5]" aria-hidden="true" />
                                    {{ __('admin.list.edit') }}
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <p data-list-empty hidden class="border-t border-lh-border px-6 py-10 text-center text-sm text-lh-muted">{{ __('admin.list.empty') }}</p>
        <p data-list-count data-all="{{ __('admin.promotions.showing_all', ['total' => ':total']) }}" data-some="{{ __('admin.promotions.showing_some', ['shown' => ':shown', 'total' => ':total']) }}"
            class="border-t border-lh-border px-6 py-6 text-[13px] text-lh-muted" aria-live="polite">{{ __('admin.promotions.showing_all', ['total' => $all->count()]) }}</p>
    </section>

    <div class="mt-8 grid gap-8 lg:grid-cols-2">
        @if ($upcoming)
            <section aria-labelledby="upcoming-title" class="rounded-md bg-lh-sage-50 px-8 py-8">
                <x-admin.badge tone="warning">{{ __('admin.promotions.upcoming_chip', ['date' => $upcoming['starts']]) }}</x-admin.badge>
                <h2 id="upcoming-title" class="mt-5 font-serif text-[32px] leading-tight font-normal">{{ $upcoming['name'] }}</h2>
                <p class="mt-4 text-[15px] leading-relaxed text-lh-muted">
                    {{ __('admin.promotions.upcoming_body', [
                        'code' => $upcoming['code'],
                        'value' => $upcoming['value'].'%',
                        'min' => $money($upcoming['min']),
                        'max' => $money($upcoming['max']),
                        'limit' => $upcoming['limit'],
                        'end' => $upcoming['ends'],
                    ]) }}
                </p>
                {{-- TODO: form sửa chương trình. --}}
                <a href="#"
                    class="mt-7 inline-flex h-14 items-center rounded border border-lh-primary-dark px-6 text-[15px] font-medium transition-colors hover:bg-lh-surface focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lh-primary">
                    {{ __('admin.promotions.edit_program') }}
                </a>
            </section>
        @endif

        <section aria-labelledby="checklist-title" class="rounded-md border border-lh-border bg-lh-surface px-8 py-8">
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
    </div>
</x-layouts.admin>
