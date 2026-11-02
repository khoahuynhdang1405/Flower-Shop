@php
    /** @var App\Models\Product $product */
    $money = fn (int $amount): string => number_format($amount, 0, '', __('storefront.thousands_separator')).' '.__('storefront.currency');
    $sizeOptions = ['' => __('admin.product_images.shared')]
        + $product->sizes->mapWithKeys(fn ($size): array => [$size->id => $size->size_name.' · '.$money($size->price)])->all();
    // Ô chọn trên từng thẻ ảnh hẹp: chỉ tên size.
    $sizeNames = ['' => __('admin.product_images.shared')] + $product->sizes->pluck('size_name', 'id')->all();
    $imageErrors = collect($errors->get('images'))->merge(collect($errors->get('images.*'))->flatten())->unique();
    $smallButton = 'inline-flex h-9 items-center gap-2 rounded px-3 text-sm whitespace-nowrap transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lh-primary';
@endphp

<x-layouts.admin :title="__('admin.product_images.title', ['name' => $product->name])">
    <x-slot:page-header>
        <x-admin.page-header :title="__('admin.product_images.title', ['name' => $product->name])" :description="__('admin.product_images.description')">
            <x-slot:actions>
                <a href="{{ route('admin.products') }}"
                    class="inline-flex h-12 items-center gap-2 rounded border border-lh-border bg-lh-surface px-5 text-[15px] transition-colors hover:bg-lh-canvas focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lh-primary">
                    <x-lucide-arrow-left class="size-4 stroke-[1.5]" aria-hidden="true" />
                    {{ __('admin.product_images.back') }}
                </a>
            </x-slot:actions>
        </x-admin.page-header>
    </x-slot:page-header>

    <x-admin.flash class="mt-8" />

    <section aria-labelledby="upload-title" class="mt-8 rounded-md border border-lh-border bg-lh-surface">
        <form method="POST" action="{{ route('admin.products.images.store', $product) }}" enctype="multipart/form-data" novalidate data-busy-form>
            @csrf

            <div class="grid gap-6 px-6 py-8 sm:px-8 lg:grid-cols-[minmax(0,3fr)_minmax(0,2fr)]">
                <div>
                    <h2 id="upload-title" class="text-xl font-semibold">{{ __('admin.product_images.upload_title') }}</h2>
                    <p class="mt-2 text-[13px] text-lh-muted">
                        {{ __('admin.product_images.upload_description', ['max' => $maxFiles, 'mb' => $maxMegabytes]) }}
                    </p>

                    <label for="images"
                        @class([
                            'mt-6 flex cursor-pointer flex-col items-center gap-2 rounded border border-dashed bg-lh-cream px-6 py-8 text-center transition-colors hover:bg-lh-sage-50',
                            'has-[:focus-visible]:outline-2 has-[:focus-visible]:outline-offset-2 has-[:focus-visible]:outline-lh-primary',
                            'border-lh-sage-200' => $imageErrors->isEmpty(),
                            'border-lh-danger' => $imageErrors->isNotEmpty(),
                        ])>
                        <x-lucide-image-plus class="size-7 stroke-[1.5] text-lh-primary" aria-hidden="true" />
                        <span class="text-[15px] font-medium">{{ __('admin.product_images.choose_files') }}</span>
                        <span class="text-xs text-lh-muted">{{ __('admin.product_images.choose_hint') }}</span>
                        <input id="images" name="images[]" type="file" multiple accept="image/jpeg,image/png,image/webp" class="sr-only"
                            data-image-preview data-selected-template="{{ __('admin.product_images.selected', ['count' => ':count']) }}"
                            @if ($imageErrors->isNotEmpty()) aria-invalid="true" aria-describedby="images-error" @endif>
                    </label>

                    <p data-image-preview-count class="mt-3 text-sm text-lh-muted" aria-live="polite"></p>
                    <ul data-image-preview-list class="mt-3 grid grid-cols-4 gap-3 empty:hidden sm:grid-cols-6" role="list"></ul>

                    @if ($imageErrors->isNotEmpty())
                        <div id="images-error" class="mt-3 flex flex-col gap-1">
                            @foreach ($imageErrors as $message)
                                <p class="flex items-start gap-1.5 text-xs text-lh-danger">
                                    <x-lucide-circle-alert class="mt-px size-3.5 shrink-0 stroke-[1.5]" aria-hidden="true" />
                                    {{ $message }}
                                </p>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="lg:pt-[52px]">
                    <x-form.select name="product_size_id" :label="__('admin.product_images.size_label')" :options="$sizeOptions"
                        :hint="__('admin.product_images.size_hint')" />
                </div>
            </div>

            <div class="flex justify-end border-t border-lh-border px-6 py-5 sm:px-8">
                <button type="submit" data-busy-label="{{ __('auth.busy') }}"
                    class="inline-flex h-12 items-center justify-center gap-3 rounded bg-lh-primary px-6 text-[15px] font-medium text-white transition-colors hover:bg-lh-primary-dark focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lh-primary disabled:cursor-wait disabled:opacity-80">
                    <span data-button-label>{{ __('admin.product_images.submit') }}</span>
                    <x-lucide-upload class="size-5 stroke-[1.5]" aria-hidden="true" />
                </button>
            </div>
        </form>
    </section>

    <section aria-labelledby="gallery-title" class="mt-8 rounded-md border border-lh-border bg-lh-surface px-6 py-8 sm:px-8">
        <div class="flex items-baseline justify-between gap-4">
            <h2 id="gallery-title" class="text-xl font-semibold">{{ __('admin.product_images.gallery_title') }}</h2>
            <p class="text-[13px] text-lh-muted">{{ trans_choice('admin.product_images.gallery_count', $product->images->count()) }}</p>
        </div>

        @if ($product->images->isEmpty())
            <div class="mt-6 rounded border border-dashed border-lh-border px-6 py-12 text-center">
                <p class="font-serif text-2xl">{{ __('admin.product_images.empty_title') }}</p>
                <p class="mt-2 text-sm text-lh-muted">{{ __('admin.product_images.empty_body') }}</p>
            </div>
        @else
            <ul class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4" role="list">
                @foreach ($product->images as $image)
                    @php
                        $position = $loop->iteration;
                        $selectId = "image-{$image->id}-size";
                    @endphp
                    <li class="flex flex-col overflow-hidden rounded border border-lh-border">
                        <div class="relative aspect-[4/5] bg-lh-sage-100">
                            <img src="{{ $image->url }}" alt="{{ __('admin.product_images.image_alt', ['position' => $position, 'name' => $product->name]) }}"
                                class="size-full object-cover" loading="lazy">
                            <div class="absolute top-3 left-3 flex flex-wrap gap-2">
                                @if ($image->is_primary)
                                    <x-admin.badge tone="success">{{ __('admin.product_images.primary') }}</x-admin.badge>
                                @endif
                                <x-admin.badge tone="neutral">{{ $image->size?->size_name ?? __('admin.product_images.shared') }}</x-admin.badge>
                            </div>
                        </div>

                        <div class="flex flex-1 flex-col gap-4 p-4">
                            <form method="POST" action="{{ route('admin.products.images.update', [$product, $image]) }}" class="flex items-end gap-2">
                                @csrf
                                @method('PATCH')
                                <div class="min-w-0 flex-1">
                                    <label for="{{ $selectId }}" class="sr-only">{{ __('admin.product_images.size_of', ['position' => $position]) }}</label>
                                    <div class="relative">
                                        <select id="{{ $selectId }}" name="product_size_id"
                                            class="h-9 w-full appearance-none rounded border border-lh-border bg-lh-surface pr-9 pl-3 text-sm text-lh-heading focus:outline-2 focus:-outline-offset-1 focus:outline-lh-primary">
                                            @foreach ($sizeNames as $sizeId => $sizeLabel)
                                                <option value="{{ $sizeId }}" @selected((string) $sizeId === (string) $image->product_size_id)>{{ $sizeLabel }}</option>
                                            @endforeach
                                        </select>
                                        <x-lucide-chevron-down class="pointer-events-none absolute inset-y-0 right-3 my-auto size-4 stroke-[1.5] text-lh-muted" aria-hidden="true" />
                                    </div>
                                </div>
                                <button type="submit" class="{{ $smallButton }} border border-lh-border hover:bg-lh-canvas">{{ __('admin.product_images.save_size') }}</button>
                            </form>

                            <div class="mt-auto flex flex-wrap items-center justify-between gap-3">
                                @unless ($image->is_primary)
                                    <form method="POST" action="{{ route('admin.products.images.primary', [$product, $image]) }}">
                                        @csrf
                                        <button type="submit" class="{{ $smallButton }} -ml-3 text-lh-primary hover:bg-lh-sage-50">
                                            <x-lucide-star class="size-4 stroke-[1.5]" aria-hidden="true" />
                                            {{ __('admin.product_images.set_primary') }}
                                        </button>
                                    </form>
                                @endunless
                                <div class="ml-auto">
                                    <x-admin.delete-button :action="route('admin.products.images.destroy', [$product, $image])"
                                        :label="__('admin.product_images.delete_label', ['position' => $position])"
                                        :confirm="__('admin.product_images.confirm_delete')" />
                                </div>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
</x-layouts.admin>
