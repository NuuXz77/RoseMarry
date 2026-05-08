@use('Illuminate\Support\Facades\Storage')
<div class="flex flex-col gap-4"
    wire:key="pos-root-container"
    x-data="{
        cart: {},
        customerId: @js($customer_id),
        guestName: @js($guest_name),
        statusOrder: @js($status_order ?? 'Take away'),
        tableNumber: @js($table_number),
        isSubmitting: false,
        clientError: '',
        selectedCategory: '',

        init() {
            this.hydrateFromStorage();

            this.$watch('cart', () => this.persistState());
            this.$watch('customerId', () => this.persistState());
            this.$watch('guestName', () => this.persistState());
            this.$watch('statusOrder', () => {
                if (this.statusOrder !== 'Dine in') {
                    this.tableNumber = '';
                }
                this.persistState();
            });
            this.$watch('tableNumber', () => this.persistState());
        },

        hydrateFromStorage() {
            try {
                const savedCart = sessionStorage.getItem('pos:cart:v1');
                if (savedCart) {
                    this.cart = JSON.parse(savedCart) || {};
                }

                const savedMeta = sessionStorage.getItem('pos:meta:v1');
                if (savedMeta) {
                    const meta = JSON.parse(savedMeta) || {};
                    this.customerId = meta.customerId ?? this.customerId;
                    this.guestName = meta.guestName ?? this.guestName;
                    this.statusOrder = meta.statusOrder ?? this.statusOrder;
                    this.tableNumber = meta.tableNumber ?? this.tableNumber;
                }
            } catch (_) {
                this.cart = {};
            }
        },

        persistState() {
            sessionStorage.setItem('pos:cart:v1', JSON.stringify(this.cart));
            sessionStorage.setItem('pos:meta:v1', JSON.stringify({
                customerId: this.customerId,
                guestName: this.guestName,
                statusOrder: this.statusOrder,
                tableNumber: this.tableNumber,
            }));
        },

        qtyInCart(productId) {
            return this.cart[productId]?.qty || 0;
        },

        addToCart(product) {
            if (!product || product.stock <= 0) {
                return;
            }

            const existing = this.cart[product.id];
            const nextQty = (existing?.qty || 0) + 1;

            if (nextQty > product.stock) {
                this.clientError = `Stok ${product.name} tidak mencukupi.`;
                return;
            }

            this.clientError = '';
            this.cart[product.id] = {
                id: product.id,
                name: product.name,
                price: Number(product.price),
                stock: Number(product.stock),
                image: product.image || null,
                qty: nextQty,
                subtotal: Number(product.price) * nextQty,
            };
        },

        decreaseQty(productId) {
            const item = this.cart[productId];
            if (!item) {
                return;
            }

            const nextQty = item.qty - 1;
            if (nextQty <= 0) {
                delete this.cart[productId];
                return;
            }

            item.qty = nextQty;
            item.subtotal = item.price * nextQty;
            this.cart[productId] = item;
        },

        setCartQty(product, qty) {
            qty = parseInt(qty) || 0;
            if (qty <= 0) {
                delete this.cart[product.id];
                return;
            }
            if (qty > product.stock) {
                qty = product.stock;
                this.clientError = `Stok ${product.name} maksimal ${product.stock}.`;
            } else {
                this.clientError = '';
            }
            this.cart[product.id] = {
                id: product.id,
                name: product.name,
                price: Number(product.price),
                stock: Number(product.stock),
                image: product.image || null,
                qty: qty,
                subtotal: Number(product.price) * qty,
            };
        },

        removeItem(productId) {
            delete this.cart[productId];
        },

        clearCart() {
            this.cart = {};
            this.clientError = '';
        },

        cartItems() {
            return Object.values(this.cart);
        },

        totalQty() {
            return this.cartItems().reduce((sum, item) => sum + Number(item.qty || 0), 0);
        },

        subtotal() {
            return this.cartItems().reduce((sum, item) => sum + Number(item.subtotal || 0), 0);
        },

        totalAmount() {
            return this.subtotal();
        },

        formatRupiah(value) {
            return Number(value || 0).toLocaleString('id-ID');
        },

        serviceIdentity() {
            if (this.customerId) {
                return 'Pelanggan terpilih';
            }

            if (this.statusOrder === 'Dine in') {
                return this.guestName?.trim() ? this.guestName.trim() : 'Nama pelanggan';
            }

            return this.guestName?.trim() ? this.guestName.trim() : 'Nomor antrean otomatis';
        },

        async proceedCheckout() {
            if (this.isSubmitting) {
                return;
            }

            if (this.totalQty() === 0) {
                this.clientError = 'Keranjang masih kosong.';
                return;
            }

            if (this.statusOrder === 'Dine in' && !String(this.tableNumber || '').trim()) {
                this.clientError = 'Nomor meja wajib diisi untuk dine in.';
                return;
            }

            this.clientError = '';
            this.isSubmitting = true;

            try {
                const payload = this.cartItems().map((item) => ({
                    id: item.id,
                    qty: item.qty,
                }));

                await $wire.call(
                    'checkoutFromClient',
                    payload,
                    this.customerId || null,
                    this.guestName || null,
                    this.statusOrder,
                    this.tableNumber || null
                );
            } finally {
                this.isSubmitting = false;
            }
        },
    }">

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{--  MAIN SPLIT: Products (left) + Cart (right)               --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <div class="card bg-base-100 border border-base-300 shadow-sm h-full flex flex-col overflow-hidden">
        <div class="grid grid-cols-1 xl:grid-cols-12 flex-1 min-h-0">

            {{-- ──────────────────────────────────────────────────────── --}}
            {{--  LEFT PANEL — Search, Filter, Product Grid              --}}
            {{-- ──────────────────────────────────────────────────────── --}}
            <section class="xl:col-span-8 w-full flex flex-col min-h-0">
                <div class="p-5 gap-4 flex flex-col flex-1 min-h-0 overflow-hidden">

                    {{-- Search & Sort Bar --}}
                    <div class="flex flex-col sm:flex-row items-stretch gap-2 shrink-0">
                        <div class="grow">
                            <x-form.input
                                name="search"
                                wireModelModifier="live.debounce.300ms"
                                wireModel="search"
                                placeholder="Cari menu..."
                                icon="heroicon-o-magnifying-glass"
                                size="input-sm"
                                containerClass="mb-0" />
                        </div>
                        <div class="w-full sm:w-52 shrink-0">
                            <x-form.select
                                name="sortBy"
                                wireModelModifier="live"
                                wireModel="sortBy"
                                placeholder=""
                                class="select-sm"
                                containerClass="mb-0">
                                <option value="stock_desc">Paling Banyak (Tersedia)</option>
                                <option value="stock_asc">Paling Sedikit (Tersedia)</option>
                                <option value="price_desc">Paling Mahal</option>
                                <option value="price_asc">Paling Murah</option>
                            </x-form.select>
                        </div>

                        {{-- Admin Quick-Action Shortcuts --}}
                        @can('pos.quick-add-product')
                            <button type="button"
                                class="btn btn-sm btn-success gap-1.5 shrink-0 shadow-sm shadow-success/20 hover:shadow-md hover:shadow-success/30 transition-all duration-200"
                                @click="$dispatch('open-modal', { id: 'pos-quick-create-product-modal' })">
                                <x-heroicon-o-plus-circle class="w-4 h-4" />
                                <span class="hidden lg:inline text-xs">Produk Baru</span>
                            </button>
                        @endcan
                        @can('pos.quick-adjust-stock')
                            <button type="button"
                                class="btn btn-sm btn-warning gap-1.5 shrink-0 shadow-sm shadow-warning/20 hover:shadow-md hover:shadow-warning/30 transition-all duration-200"
                                @click="$dispatch('open-modal', { id: 'pos-quick-adjust-stock-modal' })">
                                <x-heroicon-o-adjustments-horizontal class="w-4 h-4" />
                                <span class="hidden lg:inline text-xs">Adjust Stok</span>
                            </button>
                        @endcan
                    </div>

                    {{-- Category Filter Tabs (Alpine.js — instant, no server round-trip) --}}
                    <div class="flex gap-2 overflow-x-auto py-1 scrollbar-hide shrink-0">
                        <button @click="selectedCategory = ''"
                            :class="selectedCategory === '' ? 'btn-primary shadow-sm shadow-primary/30' : 'btn-ghost border border-base-300 hover:border-primary/40'"
                            class="btn btn-sm rounded-full shrink-0 transition-all duration-200">
                            Semua Kategori
                        </button>
                        @foreach($categories as $cat)
                            <button @click="selectedCategory = {{ $cat->id }}"
                                :class="selectedCategory === {{ $cat->id }} ? 'btn-primary shadow-sm shadow-primary/30' : 'btn-ghost border border-base-300 hover:border-primary/40'"
                                class="btn btn-sm rounded-full shrink-0 transition-all duration-200">
                                {{ $cat->name }}
                            </button>
                        @endforeach
                    </div>

                    {{-- Product Cards Grid (scrollable) --}}
                    <div class="flex-1 overflow-y-auto overflow-x-visible p-1 -m-1">
                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 2xl:grid-cols-5 gap-3 pb-4">
                            @forelse($products as $product)
                                @php
                                    $qty = $product->stock->qty_available ?? 0;
                                    $maxQty = 20;
                                    $stockPct = $qty > 0 ? min(100, round(($qty / $maxQty) * 100)) : 0;
                                    $isAvail = $qty > 0;

                                    [$stockLabel, $badgeClass, $progressClass] = match (true) {
                                        $qty <= 0 => ['Habis', 'badge-soft badge-error', 'progress-error'],
                                        $qty <= 5 => ['Stok Sedikit', 'badge-soft badge-warning', 'progress-warning'],
                                        default => ['Tersedia', 'badge-soft badge-success', 'progress-success'],
                                    };

                                    $productPayload = [
                                        'id' => (int) $product->id,
                                        'name' => $product->name,
                                        'price' => (int) $product->price,
                                        'stock' => (int) $qty,
                                        'image' => $product->foto_product ? Storage::url($product->foto_product) : null,
                                    ];
                                @endphp

                                <div
                                    wire:key="product-{{ $product->id }}"
                                    x-show="!selectedCategory || selectedCategory === {{ $product->category_id ?? 0 }}"
                                    @if($isAvail) @click="addToCart({{ \Illuminate\Support\Js::from($productPayload) }})" @endif
                                    x-bind:class="qtyInCart({{ $product->id }}) > 0 ? 'ring-2 ring-primary/40 border-primary/40' : ''"
                                    class="group cursor-pointer flex flex-col rounded-lg border bg-base-100 shadow-sm overflow-hidden border-base-200 {{ $isAvail ? 'hover:shadow-md hover:border-primary/30 active:scale-[0.98]' : 'cursor-not-allowed opacity-70' }} transition-all duration-200">

                                    {{-- Product Image --}}
                                    <div class="relative aspect-[4/3] overflow-hidden bg-base-200">
                                        @if($product->foto_product)
                                            <img
                                                src="{{ Storage::url($product->foto_product) }}"
                                                alt="{{ $product->name }}"
                                                class="w-full h-full object-cover transition-transform duration-500 {{ $isAvail ? 'group-hover:scale-105' : '' }}"
                                                loading="lazy" />
                                        @else
                                            <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-base-200 to-base-300">
                                                <x-heroicon-o-photo class="w-8 h-8 text-base-content/20" />
                                            </div>
                                        @endif

                                        <div class="absolute inset-0 bg-gradient-to-t from-black/30 via-transparent to-transparent pointer-events-none"></div>

                                        <span class="absolute top-1.5 left-1.5 badge badge-xs badge-soft badge-primary backdrop-blur-md bg-base-100/70 border-0 text-[9px] font-semibold px-1.5 shadow-sm">
                                            {{ $product->category->name ?? '—' }}
                                        </span>

                                        <span class="absolute top-1.5 right-1.5 badge badge-xs {{ $badgeClass }} text-[9px] font-bold px-1.5 shadow-sm backdrop-blur-md">
                                            {{ $stockLabel }}
                                        </span>

                                        @unless($isAvail)
                                            <div class="absolute inset-0 flex items-center justify-center bg-base-100/40 backdrop-blur-[2px]">
                                                <span class="badge badge-error badge-md gap-1 font-bold shadow-lg">
                                                    <x-heroicon-s-x-circle class="w-3.5 h-3.5" />
                                                    Habis
                                                </span>
                                            </div>
                                        @endunless

                                        <template x-if="qtyInCart({{ $product->id }}) > 0">
                                            <div class="absolute bottom-0 inset-x-0 h-0.5 bg-primary"></div>
                                        </template>
                                    </div>

                                    {{-- Product Info --}}
                                    <div class="flex flex-col gap-1.5 p-2.5">
                                        <h3 class="font-bold text-xs leading-snug line-clamp-2 min-h-[2rem] transition-colors duration-200"
                                            x-bind:class="qtyInCart({{ $product->id }}) > 0 ? 'text-primary' : ''">
                                            {{ $product->name }}
                                        </h3>

                                        <span class="text-xs font-black text-primary tracking-tight">
                                            Rp {{ number_format($product->price, 0, ',', '.') }}
                                        </span>

                                        <div class="flex items-center gap-1.5">
                                            <progress class="progress {{ $progressClass }} h-[2px] w-full rounded-full" value="{{ $stockPct }}" max="100"></progress>
                                            <span class="text-[8px] font-bold text-base-content/40 tabular-nums shrink-0">{{ $qty }}</span>
                                        </div>

                                        <div class="flex items-center justify-between" @click.stop>

                                            @if($isAvail)
                                                <template x-if="qtyInCart({{ $product->id }}) === 0">
                                                    <button type="button"
                                                        @click.stop="addToCart({{ \Illuminate\Support\Js::from($productPayload) }})"
                                                        class="w-8 h-8 flex items-center justify-center rounded-full bg-primary/10 text-primary hover:bg-primary hover:text-white transition-all duration-200 active:scale-90">
                                                        <x-heroicon-s-plus class="w-4 h-4" />
                                                    </button>
                                                </template>

                                                <template x-if="qtyInCart({{ $product->id }}) > 0">
                                                    <div class="flex items-center gap-1">
                                                        <button type="button"
                                                            @click.stop="decreaseQty({{ $product->id }})"
                                                            class="w-6 h-6 flex items-center justify-center rounded-full bg-base-200 text-base-content/70 hover:bg-primary/15 hover:text-primary transition-all duration-200 active:scale-90">
                                                            <x-heroicon-s-minus class="w-3 h-3" />
                                                        </button>

                                                        <input type="text" inputmode="numeric" pattern="[0-9]*"
                                                            :value="qtyInCart({{ $product->id }})"
                                                            @change.stop="setCartQty({{ \Illuminate\Support\Js::from($productPayload) }}, $event.target.value)"
                                                            @click.stop
                                                            @keydown.enter.stop="$event.target.blur()"
                                                            class="w-8 h-6 text-center text-[11px] font-black text-primary tabular-nums bg-base-200/60 border border-base-300 rounded-md focus:outline-none focus:border-primary" />

                                                        <button type="button"
                                                            @click.stop="addToCart({{ \Illuminate\Support\Js::from($productPayload) }})"
                                                            class="w-6 h-6 flex items-center justify-center rounded-full bg-primary text-white hover:bg-primary/80 transition-all duration-200 active:scale-90">
                                                            <x-heroicon-s-plus class="w-3 h-3" />
                                                        </button>

                                                        <button type="button"
                                                            @click.stop="removeItem({{ $product->id }})"
                                                            class="w-6 h-6 flex items-center justify-center rounded-full bg-error/10 text-error hover:bg-error hover:text-white transition-all duration-200 active:scale-90">
                                                            <x-heroicon-s-trash class="w-3 h-3" />
                                                        </button>
                                                    </div>
                                                </template>
                                            @else
                                                <div class="w-8 h-8 flex items-center justify-center rounded-full bg-base-200 text-base-content/25">
                                                    <x-heroicon-s-minus class="w-3.5 h-3.5" />
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-span-full py-16 flex flex-col items-center gap-2 text-base-content/30">
                                    <x-heroicon-o-face-frown class="w-16 h-16" />
                                    <p class="font-semibold text-sm">Menu tidak ditemukan</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                </div>
            </section>

            {{-- ──────────────────────────────────────────────────────── --}}
            {{--  RIGHT PANEL — Cart + Order Info + Checkout             --}}
            {{-- ──────────────────────────────────────────────────────── --}}
            <aside class="xl:col-span-4 flex flex-col min-h-0 xl:overflow-hidden">

                {{-- Cart Header --}}
                <div class="px-5 py-4 border-b border-base-200 flex items-center justify-between">
                    <h2 class="font-bold flex items-center gap-2">
                        <x-heroicon-o-shopping-cart class="w-5 h-5 text-primary" />
                        Keranjang
                        <span class="badge badge-primary badge-sm" x-text="`${totalQty()} item`"></span>
                    </h2>
                    <button type="button" class="btn btn-xs btn-ghost text-error" @click="clearCart()"
                        x-show="totalQty() > 0">Kosongkan</button>
                </div>

                <div class="card-body flex flex-col flex-1 min-h-0 p-5 gap-4">

                    {{-- Error message --}}
                    <p class="text-xs text-error font-medium" x-show="clientError" x-text="clientError"></p>

                    {{-- Cart Items --}}
                    <div class="flex-1 max-h-100 overflow-y-auto pr-1 space-y-2 scrollbar-hide">
                        <template x-if="totalQty() === 0">
                            <div class="rounded-2xl border border-dashed border-base-300 p-6 text-center text-base-content/40">
                                <x-heroicon-o-shopping-cart class="w-10 h-10 mx-auto mb-2" />
                                <p class="text-sm font-medium">Keranjang masih kosong</p>
                                <p class="text-[10px] mt-0.5 opacity-60">Klik produk untuk menambahkan</p>
                            </div>
                        </template>

                        <template x-for="item in cartItems()" :key="item.id">
                            <div class="rounded-xl border border-base-200 bg-base-200/40 p-3">
                                <div class="flex items-start gap-3">
                                    <div class="w-12 h-12 rounded-lg overflow-hidden bg-base-200 shrink-0">
                                        <template x-if="item.image">
                                            <img :src="item.image" :alt="item.name" class="w-full h-full object-cover" />
                                        </template>
                                        <template x-if="!item.image">
                                            <div class="w-full h-full flex items-center justify-center text-base-content/20">
                                                <x-heroicon-o-photo class="w-6 h-6" />
                                            </div>
                                        </template>
                                    </div>

                                    <div class="min-w-0 grow">
                                        <p class="text-sm font-bold truncate" x-text="item.name"></p>
                                        <p class="text-xs text-base-content/50" x-text="`Rp ${formatRupiah(item.price)} × ${item.qty}`"></p>
                                    </div>

                                    <div class="text-right shrink-0">
                                        <span class="text-sm font-black text-primary" x-text="`Rp ${formatRupiah(item.subtotal)}`"></span>
                                    </div>
                                </div>

                                <div class="mt-2 pt-2 border-t border-base-300/30 flex items-center justify-between">
                                    <div class="flex items-center gap-1">
                                        <button type="button" class="btn btn-xs btn-circle btn-ghost" @click="decreaseQty(item.id)">−</button>
                                        <span class="min-w-[2ch] text-center text-sm font-bold" x-text="item.qty"></span>
                                        <button type="button" class="btn btn-xs btn-circle btn-ghost" @click="addToCart(item)">+</button>
                                    </div>

                                    <button type="button" class="btn btn-xs btn-circle btn-ghost text-error" @click="removeItem(item.id)">
                                        <x-heroicon-o-trash class="w-3.5 h-3.5" />
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="divider my-0 shrink-0"></div>

                    {{-- Order Info --}}
                    <div class="grid grid-cols-1 gap-3 shrink-0">
                        <x-form.select
                            label="Pelanggan"
                            name="customer_id"
                            placeholder="Guest (Umum)"
                            class="select-sm"
                            x-model="customerId">
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                            @endforeach
                        </x-form.select>

                        <div x-show="!customerId" x-cloak>
                            <x-form.input
                                label="Nama Pembeli"
                                name="guest_name"
                                placeholder="Isi jika bukan member"
                                size="input-sm"
                                x-model="guestName" />
                        </div>

                        <x-form.select
                            label="Status Order"
                            name="status_order"
                            placeholder=""
                            class="select-sm"
                            x-model="statusOrder">
                            <option value="Take away">Take away</option>
                            <option value="Dine in">Dine in</option>
                        </x-form.select>

                        <div x-show="statusOrder === 'Dine in'" x-cloak>
                            <x-form.input
                                label="Nomor Meja"
                                name="table_number"
                                placeholder="Contoh: A1"
                                size="input-sm"
                                x-model="tableNumber" />
                        </div>
                    </div>

                    {{-- Summary --}}
                    <div class="space-y-2 text-sm rounded-xl border border-base-200 bg-base-200/40 p-4 shrink-0">
                        <div class="flex justify-between text-base-content/60">
                            <span>Jenis Item</span>
                            <span class="font-semibold" x-text="cartItems().length"></span>
                        </div>
                        <div class="flex justify-between text-base-content/60">
                            <span>Total Qty</span>
                            <span class="font-semibold" x-text="totalQty()"></span>
                        </div>
                        <div class="flex justify-between text-base-content/60">
                            <span>Identitas</span>
                            <span class="font-semibold text-right" x-text="serviceIdentity()"></span>
                        </div>
                        <div class="divider my-1"></div>
                        <div class="flex justify-between items-baseline">
                            <span class="font-black text-base">Total</span>
                            <span class="font-black text-lg text-primary" x-text="`Rp ${formatRupiah(subtotal())}`"></span>
                        </div>
                    </div>

                    {{-- Checkout Button --}}
                    <button type="button"
                        class="btn btn-primary btn-block btn-lg shadow-xl gap-3 shrink-0"
                        :disabled="isSubmitting || totalQty() === 0"
                        @click="proceedCheckout()">
                        <span class="loading loading-spinner loading-sm" x-show="isSubmitting"></span>
                        <x-heroicon-o-banknotes class="w-5 h-5" x-show="!isSubmitting" />
                        <span x-text="isSubmitting ? 'Memproses...' : `Bayar Rp ${formatRupiah(totalAmount())}`"></span>
                    </button>
                </div>
            </aside>
        </div>
    </div>

    <style>
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
    </style>

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{--  POS ADMIN SHORTCUT MODALS                                --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    @can('pos.quick-add-product')
        @livewire('admin.sales.modals.pos-quick-create-product', key('pos-quick-create-product'))
    @endcan
    @can('pos.quick-adjust-stock')
        @livewire('admin.sales.modals.pos-quick-adjust-stock', key('pos-quick-adjust-stock'))
    @endcan
</div>
