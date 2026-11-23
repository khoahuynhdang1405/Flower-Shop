@php
    /**
     * @var App\Models\Category $collection
     * @var Illuminate\Database\Eloquent\Collection<int, App\Models\Product> $members
     * @var Illuminate\Contracts\Pagination\LengthAwarePaginator<int, App\Models\Product>|null $candidates  null với Kiểu hoa (chỉ xem)
     * @var string|null $search
     */
    $canManage = $collection->type->allowsManualProducts();
    $price = fn (int $amount): string => number_format($amount, 0, '', __('storefront.thousands_separator'));
    $choiceError = $errors->first('product_ids') ?: $errors->first('product_ids.*');

    $panel = 'overflow-hidden rounded-md border border-lh-border bg-lh-surface';
    $headCell = 'px-4 py-5 font-normal whitespace-nowrap first:pl-6 last:pr-6';
    $cell = 'px-4 py-4 align-middle first:pl-6 last:pr-6';
    $productColumns = ['product', 'flower_type', 'price', 'status'];
@endphp

<x-layouts.admin :title="__('admin.collection_products.title', ['name' => $collection->name])">
    <x-slot:page-header>
        <x-admin.page-header :title="__('admin.collection_products.title', ['name' => $collection->name])"
            :description="__('admin.collection_products.description', ['type' => $collection->type->label(), 'count' => $members->count()])">
            <x-slot:actions>
                <a href="{{ route('admin.collections') }}"
                    class="inline-flex h-12 items-center gap-2 rounded border border-lh-border bg-lh-surface px-5 text-[15px] transition-colors hover:bg-lh-canvas focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lh-primary">
                    <x-lucide-arrow-left class="size-4 stroke-[1.5]" aria-hidden="true" />
                    {{ __('admin.collection_products.back') }}
                </a>
            </x-slot:actions>
        </x-admin.page-header>
    </x-slot:page-header>

    <x-admin.flash class="mt-8" />

    @unless ($canManage)
        <p class="mt-8 flex items-start gap-2 rounded-md bg-lh-sage-50 px-5 py-4 text-sm">
            <x-lucide-info class="mt-px size-4 shrink-0 stroke-[1.5]" aria-hidden="true" />
            {{ __('admin.collection_products.flower_type_note') }}
        </p>
    @endunless

    {{-- Sản phẩm đang có trong bộ sưu tập --}}
    <section aria-labelledby="members-title" class="{{ $panel }} mt-8">
        <h2 id="members-title" class="px-6 py-7 text-xl font-semibold">
            {{ __('admin.collection_products.members_title') }}
            <span class="font-normal text-lh-muted tabular-nums">({{ $members->count() }})</span>
        </h2>

        @if ($members->isEmpty())
            <p class="border-t border-lh-border px-6 py-10 text-center text-sm text-lh-muted">
                {{ $canManage ? __('admin.collection_products.members_empty') : __('admin.collection_products.flower_type_empty') }}
            </p>
        @else
            <div class="relative overflow-x-auto">
                <table class="w-full min-w-[760px] text-left">
                    <thead class="bg-lh-canvas text-[13px] text-lh-muted">
                        <tr>
                            @foreach ($productColumns as $column)
                                <th scope="col" class="{{ $headCell }}">{{ __('admin.collection_products.columns.'.$column) }}</th>
                            @endforeach
                            @if ($canManage)
                                <th scope="col" class="{{ $headCell }}"><span class="sr-only">{{ __('admin.collection_products.columns.actions') }}</span></th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($members as $product)
                            <tr class="border-t border-lh-border">
                                <td class="{{ $cell }}">
                                    <div class="flex items-center gap-4">
                                        <x-admin.product-thumb :product="$product" />
                                        <p class="text-[15px] font-semibold">{{ $product->name }}</p>
                                    </div>
                                </td>
                                <td class="{{ $cell }} text-[15px] whitespace-nowrap text-lh-muted">{{ $product->category?->name }}</td>
                                <td class="{{ $cell }} text-[15px] font-semibold tabular-nums whitespace-nowrap">{{ $price($product->display_price) }}</td>
                                <td class="{{ $cell }}">
                                    <x-admin.badge :tone="$product->is_active ? 'success' : 'neutral'">
                                        {{ __('admin.collection_products.statuses.'.($product->is_active ? 'active' : 'hidden')) }}
                                    </x-admin.badge>
                                </td>
                                @if ($canManage)
                                    <td class="{{ $cell }} text-right">
                                        <form method="POST" action="{{ route('admin.collections.products.destroy', [$collection, $product]) }}" class="inline"
                                            data-confirm="{{ __('admin.collection_products.confirm_remove', ['product' => $product->name, 'name' => $collection->name]) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" aria-label="{{ __('admin.collection_products.remove_item', ['product' => $product->name]) }}"
                                                class="inline-flex items-center gap-2 rounded text-sm whitespace-nowrap text-lh-muted transition-colors hover:text-lh-danger focus-visible:outline-2 focus-visible:outline-lh-primary">
                                                <x-lucide-circle-minus class="size-[18px] stroke-[1.5]" aria-hidden="true" />
                                                {{ __('admin.collection_products.remove') }}
                                            </button>
                                        </form>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    {{-- Thêm sản phẩm (chỉ Theo mùa / Theo dịp) --}}
    @if ($canManage)
        <section id="add-products" aria-labelledby="add-title" class="{{ $panel }} mt-8 scroll-mt-24">
            <div class="px-6 pt-7 pb-6">
                <h2 id="add-title" class="text-xl font-semibold">{{ __('admin.collection_products.add_title') }}</h2>
                <p class="mt-2 text-[13px] text-lh-muted">{{ __('admin.collection_products.add_description') }}</p>

                <form method="GET" action="{{ route('admin.collections.products', $collection) }}#add-products" role="search" class="mt-6 flex gap-3">
                    <label class="relative min-w-0 flex-1">
                        <span class="sr-only">{{ __('admin.list.search') }}</span>
                        <x-lucide-search class="pointer-events-none absolute inset-y-0 left-4 my-auto size-5 stroke-[1.5] text-lh-heading" aria-hidden="true" />
                        <input type="search" name="q" value="{{ $search }}" maxlength="100" autocomplete="off"
                            placeholder="{{ __('admin.collection_products.search_placeholder') }}"
                            class="h-12 w-full rounded border border-lh-border bg-lh-surface pr-4 pl-12 text-[15px] text-lh-heading placeholder:text-lh-muted focus:outline-2 focus:-outline-offset-1 focus:outline-lh-primary">
                    </label>
                    <button type="submit"
                        class="h-12 shrink-0 rounded border border-lh-primary px-5 text-[15px] text-lh-primary transition-colors hover:bg-lh-sage-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lh-primary">
                        {{ __('admin.collection_products.search_submit') }}
                    </button>
                </form>
            </div>

            @if ($candidates->isEmpty())
                <p class="border-t border-lh-border px-6 py-10 text-center text-sm text-lh-muted">
                    {{ $search ? __('admin.collection_products.candidates_none_found', ['search' => $search]) : __('admin.collection_products.candidates_empty') }}
                </p>
            @else
                <form method="POST" action="{{ route('admin.collections.products.store', $collection) }}" novalidate>
                    @csrf
                    <div class="relative overflow-x-auto">
                        <table class="w-full min-w-[760px] text-left">
                            <thead class="bg-lh-canvas text-[13px] text-lh-muted">
                                <tr>
                                    <th scope="col" class="{{ $headCell }} w-12"><span class="sr-only">{{ __('admin.collection_products.add_submit') }}</span></th>
                                    @foreach ($productColumns as $column)
                                        <th scope="col" class="{{ $headCell }}">{{ __('admin.collection_products.columns.'.$column) }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($candidates as $product)
                                    @php
                                        $checkboxId = 'add-product-'.$product->id;
                                    @endphp
                                    <tr class="border-t border-lh-border has-[:checked]:bg-lh-sage-50">
                                        <td class="{{ $cell }}">
                                            <input id="{{ $checkboxId }}" type="checkbox" name="product_ids[]" value="{{ $product->id }}"
                                                @checked(in_array((string) $product->id, (array) old('product_ids', []), true))
                                                aria-label="{{ __('admin.collection_products.select_item', ['product' => $product->name]) }}"
                                                class="size-[18px] cursor-pointer rounded-[3px] accent-lh-primary focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lh-primary">
                                        </td>
                                        <td class="{{ $cell }}">
                                            <label for="{{ $checkboxId }}" class="flex cursor-pointer items-center gap-4">
                                                <x-admin.product-thumb :product="$product" />
                                                <span class="text-[15px] font-semibold">{{ $product->name }}</span>
                                            </label>
                                        </td>
                                        <td class="{{ $cell }} text-[15px] whitespace-nowrap text-lh-muted">{{ $product->category?->name }}</td>
                                        <td class="{{ $cell }} text-[15px] font-semibold tabular-nums whitespace-nowrap">{{ $price($product->display_price) }}</td>
                                        <td class="{{ $cell }}">
                                            <x-admin.badge :tone="$product->is_active ? 'success' : 'neutral'">
                                                {{ __('admin.collection_products.statuses.'.($product->is_active ? 'active' : 'hidden')) }}
                                            </x-admin.badge>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="flex flex-col gap-4 border-t border-lh-border px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex flex-col gap-2">
                            <button type="submit"
                                class="inline-flex h-12 w-max items-center gap-3 rounded bg-lh-primary px-6 text-[15px] font-medium text-white transition-colors hover:bg-lh-primary-dark focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lh-primary">
                                <x-lucide-plus class="size-5 stroke-[1.5]" aria-hidden="true" />
                                {{ __('admin.collection_products.add_submit') }}
                            </button>
                            @if ($choiceError)
                                <p role="alert" class="text-xs text-lh-danger">{{ $choiceError }}</p>
                            @endif
                        </div>
                        <x-admin.pagination :paginator="$candidates" />
                    </div>
                </form>
            @endif
        </section>
    @endif
</x-layouts.admin>
