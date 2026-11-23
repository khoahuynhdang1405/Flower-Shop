@php
    /** @var \App\Models\User $customer */
    use App\Actions\Customer\ShowAdminCustomerAction;
    use App\Enums\CustomerGroup;

    $money = fn (int $amount): string => number_format($amount, 0, '', __('storefront.thousands_separator'));
    $address = collect([$customer->default_shipping_address, $customer->default_shipping_district])->filter()->implode(', ');
    $cell = 'px-4 py-5 align-middle first:pl-6 last:pr-6';
@endphp

<x-layouts.admin :title="$customer->name">
    <x-slot:page-header>
        <x-admin.page-header :title="$customer->name"
            :description="__('admin.customers.show.description', ['code' => $customer->customer_code, 'month' => $customer->created_at->format('m/Y')])">
            <x-slot:actions>
                <a href="{{ route('admin.customers') }}"
                    class="inline-flex h-12 items-center gap-2 rounded border border-lh-border bg-lh-surface px-5 text-[15px] transition-colors hover:bg-lh-canvas focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lh-primary">
                    <x-lucide-arrow-left class="size-4 stroke-[1.5]" aria-hidden="true" />
                    {{ __('admin.customers.show.back') }}
                </a>
            </x-slot:actions>
        </x-admin.page-header>
    </x-slot:page-header>

    <x-admin.flash class="mt-8" />

    <div class="mt-8 grid gap-5 sm:grid-cols-3">
        <x-admin.stat-card :label="__('admin.customers.show.orders')" :value="$customer->orders_count" />
        <x-admin.stat-card :label="__('admin.customers.show.spent')" :value="$money($customer->spent)" :note="__('admin.customers.table_description')" />
        <x-admin.stat-card :label="__('admin.customers.show.last_order')"
            :value="$customer->last_order_at?->format('d/m/Y') ?? '—'" />
    </div>

    <div class="mt-8 grid items-start gap-8 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
        <x-admin.form-card id="edit" class="mt-0! scroll-mt-28" :action="route('admin.customers.update', $customer)" method="PUT"
            :cancel="route('admin.customers')" :submit="__('admin.list.save')">
            <h2 class="text-xl font-semibold md:col-span-2">{{ __('admin.customers.show.edit_title') }}</h2>

            <x-form.input name="name" :label="__('admin.customers.form.name')" marker :value="$customer->name"
                autocomplete="off" required maxlength="255" />
            <x-form.input name="phone" type="tel" :label="__('admin.customers.form.phone')" :value="$customer->phone" inputmode="tel" />
            <x-form.input name="birthday" type="date" :label="__('admin.customers.form.birthday')"
                :value="$customer->birthday?->format('Y-m-d')" min="1900-01-02" max="{{ now()->subDay()->format('Y-m-d') }}" />
            <x-form.select name="customer_group" :label="__('admin.customers.form.group')" marker :options="CustomerGroup::options()"
                :value="$customer->customer_group" :hint="__('admin.customers.form.group_hint')" required />
        </x-admin.form-card>

        <section aria-labelledby="contact-title" class="rounded-md border border-lh-border bg-lh-surface px-6 py-8">
            <h2 id="contact-title" class="text-xl font-semibold">{{ __('admin.customers.show.contact_title') }}</h2>
            <dl class="mt-6 flex flex-col gap-5 text-sm">
                <div>
                    <dt class="text-[13px] text-lh-muted">{{ __('admin.customers.show.email') }}</dt>
                    <dd class="mt-1 break-all">{{ $customer->email }}</dd>
                </div>
                <div>
                    <dt class="text-[13px] text-lh-muted">{{ __('admin.customers.form.phone') }}</dt>
                    <dd class="mt-1 tabular-nums">{{ $customer->phone ?: __('admin.customers.no_phone') }}</dd>
                </div>
                <div>
                    <dt class="text-[13px] text-lh-muted">{{ __('admin.customers.show.default_address') }}</dt>
                    <dd @class(['mt-1', 'text-lh-muted' => $address === ''])>{{ $address ?: __('admin.customers.show.no_address') }}</dd>
                </div>
                <div>
                    <dt class="text-[13px] text-lh-muted">{{ __('admin.customers.form.group') }}</dt>
                    <dd class="mt-1.5"><x-admin.badge :tone="$customer->customer_group->badgeTone()">{{ $customer->customer_group->label() }}</x-admin.badge></dd>
                </div>
            </dl>
        </section>
    </div>

    <section aria-labelledby="orders-title" class="mt-8 overflow-hidden rounded-md border border-lh-border bg-lh-surface">
        <div class="px-6 pt-8 pb-6">
            <h2 id="orders-title" class="text-xl font-semibold">{{ __('admin.customers.show.orders_title') }}</h2>
            <p class="mt-2 text-[13px] text-lh-muted">{{ __('admin.customers.show.orders_description', ['count' => ShowAdminCustomerAction::RECENT_ORDERS]) }}</p>
        </div>

        @if ($customer->orders->isEmpty())
            <p class="border-t border-lh-border px-6 py-12 text-center text-sm text-lh-muted">{{ __('admin.customers.show.no_orders') }}</p>
        @else
            <div class="relative overflow-x-auto">
                <table class="w-full min-w-[720px] text-left">
                    <thead class="bg-lh-canvas text-[13px] text-lh-muted">
                        <tr>
                            @foreach (['code', 'flowers', 'total', 'status'] as $column)
                                <th scope="col" class="px-4 py-4 font-normal whitespace-nowrap first:pl-6 last:pr-6">{{ __('admin.customers.show.order_columns.'.$column) }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($customer->orders as $order)
                            <tr class="border-t border-lh-border">
                                <td class="{{ $cell }} whitespace-nowrap">
                                    <p class="text-[15px] font-semibold">{{ $order->order_code }}</p>
                                    <p class="mt-1 text-xs text-lh-muted">{{ $order->created_at->format('d/m/Y') }}</p>
                                </td>
                                <td class="{{ $cell }} text-sm">{{ $order->items->pluck('item_name')->implode(', ') }}</td>
                                <td class="{{ $cell }} text-[15px] tabular-nums whitespace-nowrap">{{ $money($order->total_amount) }}</td>
                                <td class="{{ $cell }}">
                                    <x-admin.badge :tone="$order->order_status->badgeTone()">{{ $order->order_status->adminLabel() }}</x-admin.badge>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</x-layouts.admin>
