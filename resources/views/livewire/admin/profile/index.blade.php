<div class="space-y-6">

    {{-- ═══════════════════════════════════════════
         HEADER / PROFILE SUMMARY
    ═══════════════════════════════════════════ --}}
    <div class="card bg-base-100 border border-base-300 overflow-hidden">
        <div class="card-body pt-0">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">

                {{-- Avatar + Name --}}
                <div class="flex items-end gap-4 -mt-8">
                    <div class="w-16 h-16 rounded-full ring-2 ring-base-100 ring-offset-0 bg-primary/10 text-primary grid place-items-center shadow-sm flex-shrink-0">
                        <span class="text-xl font-semibold">{{ strtoupper(substr($user->username, 0, 1)) }}</span>
                    </div>
                    <div class="pb-1">
                        <h1 class="text-lg font-semibold text-base-content leading-tight">{{ $user->username }}</h1>
                        <div class="mt-1.5">
                            @if($user->is_active)
                                <span class="badge badge-success badge-sm badge-soft gap-1">
                                    <x-heroicon-o-check class="w-3 h-3" />
                                    Aktif
                                </span>
                            @else
                                <span class="badge badge-error badge-sm badge-soft gap-1">
                                    <x-heroicon-o-x-mark class="w-3 h-3" />
                                    Nonaktif
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center gap-2 pb-1">
                    @if(!$isEditing)
                        <button wire:click="toggleEdit" class="btn btn-sm btn-outline gap-2">
                            <x-heroicon-o-pencil-square class="w-4 h-4" />
                            Edit Profil
                        </button>
                    @else
                        <button wire:click="toggleEdit" class="btn btn-sm btn-ghost gap-2">
                            <x-heroicon-o-x-mark class="w-4 h-4" />
                            Batal
                        </button>
                        <button wire:click="updateProfile" class="btn btn-sm btn-primary gap-2">
                            <x-heroicon-o-check class="w-4 h-4" />
                            Simpan
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════
         MAIN CONTENT — 1/3 + 2/3 GRID
    ═══════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ──────────────────────────────────────
             LEFT COLUMN  (1 col)
        ────────────────────────────────────── --}}
        <div class="space-y-6">

            {{-- Info Akun --}}
            <div class="card bg-base-100 border border-base-300">
                <div class="card-body gap-0">

                    {{-- Card Title --}}
                    <div class="flex items-center gap-2.5 mb-5">
                        <span class="w-7 h-7 rounded-lg bg-primary/10 text-primary grid place-items-center flex-shrink-0">
                            <x-heroicon-o-identification class="w-4 h-4" />
                        </span>
                        <h3 class="font-semibold text-sm text-base-content">Info Akun</h3>
                    </div>

                    <div class="space-y-4">

                        {{-- Username --}}
                        <div class="flex items-start gap-3">
                            <span class="w-8 h-8 rounded-lg bg-base-200/60 text-base-content/70 grid place-items-center flex-shrink-0">
                                <x-heroicon-o-user class="w-4 h-4" />
                            </span>
                            <div class="flex-1">
                                <p class="text-[10px] font-semibold uppercase tracking-widest text-base-content/40 mb-1">Username</p>
                                @if($isEditing)
                                    <input type="text" wire:model="username"
                                        class="input input-sm input-bordered w-full @error('username') input-error @enderror" />
                                    @error('username') <p class="text-error text-xs mt-1">{{ $message }}</p> @enderror
                                @else
                                    <p class="text-sm font-medium text-base-content">{{ $user->username }}</p>
                                @endif
                            </div>
                        </div>

                        <div class="divider my-0"></div>

                        {{-- Status --}}
                        <div class="flex items-start gap-3">
                            <span class="w-8 h-8 rounded-lg bg-base-200/60 text-base-content/70 grid place-items-center flex-shrink-0">
                                <x-heroicon-o-shield-check class="w-4 h-4" />
                            </span>
                            <div class="flex-1">
                                <p class="text-[10px] font-semibold uppercase tracking-widest text-base-content/40 mb-1">Status Akun</p>
                                @if($user->is_active)
                                    <span class="badge badge-success badge-sm badge-soft gap-1">
                                        <x-heroicon-o-check class="w-1.5 h-1.5" />
                                        Aktif
                                    </span>
                                @else
                                    <span class="badge badge-error badge-sm badge-soft gap-1">
                                        <x-heroicon-o-x-circle class="w-1.5 h-1.5" />
                                        Nonaktif
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="divider my-0"></div>

                        {{-- Last Login --}}
                        <div class="flex items-start gap-3">
                            <span class="w-8 h-8 rounded-lg bg-base-200/60 text-base-content/70 grid place-items-center flex-shrink-0">
                                <x-heroicon-o-clock class="w-4 h-4" />
                            </span>
                            <div class="flex-1">
                                <p class="text-[10px] font-semibold uppercase tracking-widest text-base-content/40 mb-1">Terakhir Login</p>
                                <p class="text-sm font-medium text-base-content">
                                    {{ optional($user->terakhir_login)->format('d F Y, H:i') ?? '-' }}
                                </p>
                                <p class="text-xs text-base-content/40 mt-0.5">
                                    {{ optional($user->terakhir_login)->diffForHumans() ?? '-' }}
                                </p>
                            </div>
                        </div>

                        {{-- Last IP --}}
                        <div class="flex items-start gap-3">
                            <span class="w-8 h-8 rounded-lg bg-base-200/60 text-base-content/70 grid place-items-center flex-shrink-0">
                                <x-heroicon-o-globe-alt class="w-4 h-4" />
                            </span>
                            <div class="flex-1">
                                <p class="text-[10px] font-semibold uppercase tracking-widest text-base-content/40 mb-1">IP Login Terakhir</p>
                                <p class="text-sm font-medium font-mono text-base-content">{{ $user->last_login_ip ?? '-' }}</p>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- Keamanan --}}
            <div class="card bg-base-100 border border-base-300">
                <div class="card-body gap-0">

                    {{-- Card Title --}}
                    <div class="flex items-center gap-2.5 mb-5">
                        <span class="w-7 h-7 rounded-lg bg-secondary/10 text-secondary grid place-items-center flex-shrink-0">
                            <x-heroicon-o-shield-check class="w-4 h-4" />
                        </span>
                        <h3 class="font-semibold text-sm text-base-content">Keamanan</h3>
                    </div>

                    @if(!$isChangingPassword)
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-sm font-medium text-base-content">Password</p>
                                <p class="text-xs text-base-content/40 mt-0.5">Terakhir diubah 3 bulan lalu</p>
                            </div>
                            <button wire:click="toggleChangePassword" class="btn btn-sm btn-outline">Ubah</button>
                        </div>
                    @else
                        <div class="space-y-3">
                            <div>
                                <label class="text-[10px] font-semibold uppercase tracking-widest text-base-content/40 mb-1 block">Password Saat Ini</label>
                                <input type="password" wire:model="current_password"
                                    class="input input-sm input-bordered w-full" placeholder="••••••••" />
                                @error('current_password') <p class="text-error text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="text-[10px] font-semibold uppercase tracking-widest text-base-content/40 mb-1 block">Password Baru</label>
                                <input type="password" wire:model="new_password"
                                    class="input input-sm input-bordered w-full" placeholder="Min. 8 karakter" />
                                @error('new_password') <p class="text-error text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="text-[10px] font-semibold uppercase tracking-widest text-base-content/40 mb-1 block">Konfirmasi Password</label>
                                <input type="password" wire:model="new_password_confirmation"
                                    class="input input-sm input-bordered w-full" placeholder="Ulangi password baru" />
                            </div>
                            <div class="flex justify-end gap-2 pt-1">
                                <button type="button" wire:click="toggleChangePassword" class="btn btn-xs btn-ghost">Batal</button>
                                <button wire:click="updatePassword" class="btn btn-xs btn-primary">Simpan Password</button>
                            </div>
                        </div>
                    @endif

                </div>
            </div>

        </div>

        {{-- ──────────────────────────────────────
             RIGHT COLUMN  (2 cols)
        ────────────────────────────────────── --}}
        <div class="col-span-1 lg:col-span-2 space-y-6">

            {{-- Ringkasan Aktivitas --}}
            <div class="card bg-base-100 border border-base-300">
                <div class="card-body gap-0">

                    {{-- Card Title --}}
                    <div class="flex items-center gap-2.5 mb-5">
                        <span class="w-7 h-7 rounded-lg bg-accent/10 text-accent grid place-items-center flex-shrink-0">
                            <x-heroicon-o-chart-bar class="w-4 h-4" />
                        </span>
                        <h3 class="font-semibold text-sm text-base-content">Ringkasan Aktivitas</h3>
                    </div>

                    {{-- Stat Cards --}}
                    <div class="stats stats-vertical lg:stats-horizontal bg-base-200/40 border border-base-300 w-full mb-5">
                        <div class="stat">
                            <div class="stat-figure text-primary">
                                <x-heroicon-o-arrow-right-on-rectangle class="w-5 h-5" />
                            </div>
                            <div class="stat-title">Total Login</div>
                            <div class="stat-value text-base">142</div>
                            <div class="stat-desc">Sejak bergabung</div>
                        </div>
                        <div class="stat">
                            <div class="stat-figure text-accent">
                                <x-heroicon-o-document-text class="w-5 h-5" />
                            </div>
                            <div class="stat-title">Transaksi</div>
                            <div class="stat-value text-base">38</div>
                            <div class="stat-desc">Bulan ini</div>
                        </div>
                        <div class="stat">
                            <div class="stat-figure text-info">
                                <x-heroicon-o-clock class="w-5 h-5" />
                            </div>
                            <div class="stat-title">Sesi Aktif</div>
                            <div class="stat-value text-base">1</div>
                            <div class="stat-desc">Perangkat</div>
                        </div>
                        <div class="stat">
                            <div class="stat-figure text-secondary">
                                <x-heroicon-o-user class="w-5 h-5" />
                            </div>
                            <div class="stat-title">Bergabung</div>
                            <div class="stat-value text-base">{{ optional($user->created_at)->format('Y') ?? '-' }}</div>
                            <div class="stat-desc">Tahun</div>
                        </div>
                    </div>

                    {{-- Login Detail --}}
                    <div class="stats stats-vertical sm:stats-horizontal bg-base-200/40 border border-base-300 w-full">
                        <div class="stat">
                            <div class="stat-figure text-secondary">
                                <x-heroicon-o-clock class="w-5 h-5" />
                            </div>
                            <div class="stat-title">Terakhir Login</div>
                            <div class="stat-value text-sm">
                                {{ optional($user->terakhir_login)->format('d F Y, H:i') ?? '-' }}
                            </div>
                            <div class="stat-desc">{{ optional($user->terakhir_login)->diffForHumans() ?? '-' }}</div>
                        </div>
                        <div class="stat">
                            <div class="stat-figure text-secondary">
                                <x-heroicon-o-globe-alt class="w-5 h-5" />
                            </div>
                            <div class="stat-title">IP Login Terakhir</div>
                            <div class="stat-value text-sm font-mono">{{ $user->last_login_ip ?? '-' }}</div>
                            <div class="stat-desc">Jaringan lokal</div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- Aktivitas Terakhir --}}
            <div class="card bg-base-100 border border-base-300">
                <div class="card-body gap-0">

                    {{-- Card Header --}}
                    <div class="flex items-center justify-between gap-3 mb-5">
                        <div class="flex items-center gap-2.5">
                            <span class="w-7 h-7 rounded-lg bg-info/10 text-info grid place-items-center flex-shrink-0">
                                <x-heroicon-o-clock class="w-4 h-4" />
                            </span>
                            <h3 class="font-semibold text-sm text-base-content">Aktivitas Terakhir</h3>
                        </div>

                        {{-- Filter --}}
                        <div class="dropdown dropdown-end">
                            <label tabindex="0" class="btn btn-ghost btn-sm gap-1.5 text-xs">
                                <x-heroicon-o-funnel class="w-3.5 h-3.5" />
                                Filter
                                @php $activeFilterCount = $activityFilter !== '' ? 1 : 0; @endphp
                                @if($activeFilterCount > 0)
                                    <span class="badge badge-primary badge-xs">{{ $activeFilterCount }}</span>
                                @endif
                            </label>
                            <div tabindex="0" class="dropdown-content z-10 card card-compact w-60 bg-base-100 border border-base-300 shadow-lg mt-2 p-4">
                                <div class="space-y-3">
                                    <div>
                                        <label class="text-[10px] font-semibold uppercase tracking-widest text-base-content/40 mb-1.5 block">Jenis Aktivitas</label>
                                        <select wire:model.live="activityFilter" class="select select-sm select-bordered w-full">
                                            <option value="">Semua Aktivitas</option>
                                            <option value="login">Login</option>
                                            <option value="profile">Profil</option>
                                            <option value="sales">Transaksi</option>
                                        </select>
                                    </div>
                                    <button wire:click="resetFilters" class="btn btn-ghost btn-sm w-full text-xs">Reset Filter</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Activity List --}}
                    <div class="space-y-0 divide-y divide-base-200">

                        @if($activityFilter === '' || $activityFilter === 'login')
                            <div class="flex items-start gap-3.5 py-3 first:pt-0 last:pb-0">
                                <span class="w-7 h-7 rounded-full bg-primary/10 text-primary grid place-items-center flex-shrink-0 mt-0.5">
                                    <x-heroicon-o-arrow-right-on-rectangle class="w-3.5 h-3.5" />
                                </span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-base-content">Login ke sistem</p>
                                    <p class="text-xs text-base-content/40 mt-0.5">Baru saja</p>
                                </div>
                                <span class="badge badge-sm badge-ghost text-xs">Login</span>
                            </div>
                        @endif

                        @if($activityFilter === '' || $activityFilter === 'profile')
                            <div class="flex items-start gap-3.5 py-3 first:pt-0 last:pb-0">
                                <span class="w-7 h-7 rounded-full bg-base-200 text-base-content/50 grid place-items-center flex-shrink-0 mt-0.5">
                                    <x-heroicon-o-user class="w-3.5 h-3.5" />
                                </span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-base-content">Memperbarui data profil</p>
                                    <p class="text-xs text-base-content/40 mt-0.5">Kemarin, 14:30</p>
                                </div>
                                <span class="badge badge-sm badge-ghost text-xs">Profil</span>
                            </div>
                        @endif

                        @if($activityFilter === '' || $activityFilter === 'sales')
                            <div class="flex items-start gap-3.5 py-3 first:pt-0 last:pb-0">
                                <span class="w-7 h-7 rounded-full bg-base-200 text-base-content/50 grid place-items-center flex-shrink-0 mt-0.5">
                                    <x-heroicon-o-document-text class="w-3.5 h-3.5" />
                                </span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-base-content">Transaksi penjualan
                                        <span class="font-mono text-xs text-base-content/60">#INV-001</span>
                                    </p>
                                    <p class="text-xs text-base-content/40 mt-0.5">18 Feb 2026, 09:15</p>
                                </div>
                                <span class="badge badge-sm badge-ghost text-xs">Transaksi</span>
                            </div>
                        @endif

                    </div>

                </div>
            </div>

        </div>
    </div>

</div>