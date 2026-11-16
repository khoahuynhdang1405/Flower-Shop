@php
    /**
     * @var \Carbon\CarbonImmutable $date
     * @var list<array{slot: string, orders: \Illuminate\Support\Collection<int, \App\Models\Order>, arranged: int}> $slots
     */
    use App\Enums\CartItemType;
    use App\Enums\PaymentMethod;
    use App\Enums\PaymentStatus;

    $total = array_sum(array_map(fn (array $group): int => $group['orders']->count(), $slots));
    $arranged = array_sum(array_column($slots, 'arranged'));
    $control = 'h-12 rounded border border-lh-border bg-lh-surface px-4 text-[15px] text-lh-heading focus:outline-2 focus:-outline-offset-1 focus:outline-lh-primary';
    $secondaryButton = 'inline-flex h-12 items-center gap-2 rounded border border-lh-primary px-5 text-[15px] text-lh-primary transition-colors
        hover:bg-lh-primary hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lh-primary';
@endphp

<x-layouts.admin :title="__('admin.orders.schedule.title')">
    <x-slot:page-header>
        <x-admin.page-header :title="__('admin.orders.schedule.title')"
            :description="__('admin.orders.schedule.summary', ['date' => $date->format('d/m/Y'), 'total' => $total, 'arranged' => $arranged])">
            <x-slot:actions>
                <a href="{{ route('admin.orders') }}"
                    class="inline-flex h-12 items-center gap-2 rounded text-[15px] text-lh-muted transition-colors hover:text-lh-heading focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lh-primary print:hidden">
                    <x-lucide-arrow-left class="size-4 stroke-[1.5]" aria-hidden="true" />
                    {{ __('admin.orders.schedule.back') }}
                </a>
                <button type="button" data-print class="{{ $secondaryButton }} print:hidden">
                    <x-lucide-printer class="size-4 stroke-[1.5]" aria-hidden="true" />
                    {{ __('admin.orders.schedule.print') }}
                </button>
            </x-slot:actions>
        </x-admin.page-header>
    </x-slot:page-header>

    <x-admin.flash class="mt-8" />

    <form method="GET" action="{{ route('admin.orders.schedule') }}" class="mt-8 flex flex-col gap-3 sm:flex-row sm:items-end print:hidden">
        <label class="flex flex-col gap-2">
            <span class="text-[13px] text-lh-muted">{{ __('admin.orders.schedule.date') }}</span>
            <input type="date" name="date" value="{{ $date->toDateString() }}" required class="{{ $control }} sm:w-56">
        </label>
        <button type="submit" class="{{ $secondaryButton }} justify-center">{{ __('admin.orders.schedule.view') }}</button>
    </form>
    @error('date')
        <p class="mt-2 text-sm text-lh-danger" role="alert">{{ $message }}</p>
    @enderror

    @if ($total === 0)
        <div class="mt-8 rounded-md border border-lh-border bg-lh-surface px-6 py-14 text-center">
            <p class="font-serif text-2xl">{{ __('admin.orders.schedule.empty_title') }}</p>
            <p class="mt-2 text-sm text-lh-muted">{{ __('admin.orders.schedule.empty_body') }}</p>
        </div>
    @else
        <div class="mt-8 flex flex-col gap-8">
            @foreach ($slots as $group)
                @php
                    $slotId = 'slot-'.$loop->index;
                @endphp
                <section aria-labelledby="{{ $slotId }}" class="rounded-md border border-lh-border bg-lh-surface break-inside-avoid-page">
                    <div class="flex flex-wrap items-baseline justify-between gap-2 border-b border-lh-border px-6 py-5">
                        <h2 id="{{ $slotId }}" class="font-serif text-2xl tabular-nums">{{ str_replace('-', ' – ', $group['slot']) }}</h2>
                        @if ($group['orders']->isNotEmpty())
                            <p class="text-[13px] text-lh-muted">
                                {{ __('admin.orders.schedule.slot_progress', ['arranged' => $group['arranged'], 'total' => $group['orders']->count()]) }}
                            </p>
                        @endif
                    </div>

                    @if ($group['orders']->isEmpty())
                        <p class="px-6 py-6 text-sm text-lh-muted">{{ __('admin.orders.schedule.slot_empty') }}</p>
                    @else
                        <ul class="grid gap-px bg-lh-border lg:grid-cols-2" role="list">
                            @foreach ($group['orders'] as $order)
                                @php
                                    $detail = $order->deliveryDetail;
                                    [$flowers, $addons] = $order->items->partition(fn ($item) => $item->item_type === CartItemType::Flower);
                                    $next = $order->order_status->next();
                                    $action = $next ? __('admin.orders.advance.'.$order->order_status->value) : null;
                                @endphp
                                <li class="flex flex-col gap-4 bg-lh-surface px-6 py-5 break-inside-avoid">
                                    <div class="flex flex-wrap items-center justify-between gap-3">
                                        <p class="text-[15px] font-semibold">{{ $order->order_code }}</p>
                                        <x-admin.badge :tone="$order->order_status->badgeTone()">{{ $order->order_status->adminLabel() }}</x-admin.badge>
                                    </div>

                                    <ul class="flex flex-col gap-1.5" role="list">
                                        @foreach ($flowers as $item)
                                            <li class="text-[15px]">
                                                <span class="font-medium">{{ $item->item_name }}</span>
                                                @if ($item->size_name)
                                                    <span class="text-lh-muted">· {{ $item->size_name }}</span>
                                                @endif
                                                <span class="tabular-nums text-lh-muted">{{ __('admin.orders.schedule.quantity', ['count' => $item->quantity]) }}</span>
                                            </li>
                                        @endforeach
                                    </ul>

                                    @if ($addons->isNotEmpty())
                                        <p class="text-[13px] text-lh-muted">
                                            <span class="text-lh-heading">{{ __('admin.orders.schedule.addons') }}:</span>
                                            {{ $addons->map(fn ($item) => $item->item_name.' '.__('admin.orders.schedule.quantity', ['count' => $item->quantity]))->join(', ') }}
                                        </p>
                                    @endif

                                    @if ($detail?->card_message)
                                        <figure class="rounded bg-lh-canvas px-4 py-3">
                                            <figcaption class="text-xs text-lh-muted">
                                                {{ __('admin.orders.schedule.card') }}
                                                @if ($detail->card_receiver)
                                                    · {{ __('admin.orders.schedule.card_to', ['name' => $detail->card_receiver]) }}
                                                @endif
                                            </figcaption>
                                            <blockquote class="mt-1.5 font-serif text-[15px] whitespace-pre-line italic">{{ $detail->card_message }}</blockquote>
                                            @if ($detail->card_sender)
                                                <p class="mt-1.5 text-right text-xs text-lh-muted">{{ __('admin.orders.schedule.card_from', ['name' => $detail->card_sender]) }}</p>
                                            @endif
                                        </figure>
                                    @endif

                                    @if ($detail)
                                        <dl class="grid gap-1 text-[13px]">
                                            <div>
                                                <dt class="sr-only">{{ __('admin.orders.schedule.receiver') }}</dt>
                                                <dd>{{ $detail->receiver_name }} · {{ $detail->receiver_phone }} · {{ $detail->shipping_district }}</dd>
                                            </div>
                                            @if ($detail->delivery_notes)
                                                <div class="text-lh-muted">
                                                    <dt class="inline">{{ __('admin.orders.schedule.notes') }}:</dt>
                                                    <dd class="inline">{{ $detail->delivery_notes }}</dd>
                                                </div>
                                            @endif
                                        </dl>
                                    @endif

                                    @if ($order->payment_method === PaymentMethod::Cod && $order->payment_status !== PaymentStatus::Paid)
                                        <p class="text-[13px] text-lh-amber">
                                            {{ __('admin.orders.schedule.unpaid_cod', ['amount' => number_format($order->total_amount, 0, '', __('storefront.thousands_separator')).' '.__('storefront.currency')]) }}
                                        </p>
                                    @endif

                                    @if ($next)
                                        {{-- Bấm hai lần vẫn an toàn: chỉ lần đầu đổi được trạng thái (AdvanceOrderStatusAction). --}}
                                        <form method="POST" action="{{ route('admin.orders.advance', $order) }}" class="print:hidden">
                                            @csrf
                                            <button type="submit" aria-label="{{ __('admin.orders.advance_label', ['action' => $action, 'code' => $order->order_code]) }}"
                                                class="inline-flex h-9 items-center gap-1.5 rounded border border-lh-primary px-3.5 text-sm text-lh-primary transition-colors
                                                    hover:bg-lh-primary hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lh-primary">
                                                {{ $action }}
                                                <x-lucide-arrow-right class="size-4 stroke-[1.5]" aria-hidden="true" />
                                            </button>
                                        </form>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>
            @endforeach
        </div>
    @endif
</x-layouts.admin>
