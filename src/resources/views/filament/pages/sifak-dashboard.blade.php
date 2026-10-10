<x-filament-panels::page class="fi-dashboard-page sd">
    <div
        class="sd-stack"
        x-data="{
            role: 'all',
            roles: @js(array_keys($roleFilters)),
            q: '',
            tab: 'pending',
            guide: true,
            init() {
                try {
                    const saved = localStorage.getItem('sd-role');
                    this.role = this.roles.includes(saved) ? saved : 'all';
                    this.guide = localStorage.getItem('sd-guide-hidden') !== '1';
                } catch (e) {}
                this.$watch('role', (value) => { try { localStorage.setItem('sd-role', value) } catch (e) {} });
            },
            hideGuide(hidden = true) {
                this.guide = ! hidden;
                try { localStorage.setItem('sd-guide-hidden', hidden ? '1' : '0') } catch (e) {}
            },
            inRole(roles) { return this.role === 'all' || roles.includes(this.role) },
            match(text) { return this.q === '' || text.toLowerCase().includes(this.q.trim().toLowerCase()) },
            openStage(pipeline, stage) {
                this.$dispatch('open-modal', { id: 'sd-stage' });
                this.$wire.openStage(pipeline, stage);
            },
        }"
        x-on:keydown.window.slash="if (! ['INPUT', 'TEXTAREA', 'SELECT'].includes($event.target.tagName) && ! $event.target.isContentEditable) { $event.preventDefault(); $refs.search.focus() }"
    >
        {{-- Sapaan --}}
        <section class="sd-hero">
            <div class="sd-hero-main">
                <p class="sd-eyebrow">{{ $today }}@if ($tenant) · {{ $tenant }}@endif</p>
                <h1 class="sd-hero-title">{{ $greeting }}, {{ $user->name }}</h1>
                <div class="sd-chips">
                    @foreach ($roles as $role)
                        <span class="sd-chip">{{ $role }}</span>
                    @endforeach
                </div>
            </div>
            <div class="sd-hero-aside">
                <div class="sd-hero-side">
                    @if ($pendingTotal > 0)
                        <span class="sd-hero-count">{{ $pendingTotal }}</span>
                        <span class="sd-hero-caption">pekerjaan menunggu tindakan Anda</span>
                    @else
                        <x-filament::icon icon="heroicon-o-check-circle" class="sd-hero-ok" />
                        <span class="sd-hero-caption">Tidak ada pekerjaan tertunda</span>
                    @endif
                </div>
                <button type="button" wire:click="$refresh" class="sd-refresh" title="Muat ulang angka">
                    <x-filament::icon icon="heroicon-m-arrow-path" class="sd-refresh-icon" wire:loading.class="sd-spin" wire:target="$refresh" />
                    <span>Diperbarui {{ $refreshedAt }}</span>
                </button>
            </div>
        </section>

        {{-- Toolbar: cari & filter peran --}}
        <section class="sd-toolbar">
            <label class="sd-search">
                <x-filament::icon icon="heroicon-m-magnifying-glass" class="sd-search-icon" />
                <input x-ref="search" x-model.debounce.150ms="q" type="search" placeholder="Cari tugas, menu, atau fitur…" aria-label="Cari di beranda" x-on:keydown.escape="q = ''; $el.blur()">
                <kbd x-show="q === ''">/</kbd>
            </label>
            @if (count($roleFilters) > 1)
                <div class="sd-segment" role="tablist" aria-label="Tampilkan sebagai peran">
                    <button type="button" role="tab" x-on:click="role = 'all'" x-bind:aria-selected="role === 'all'" x-bind:class="{ 'is-active': role === 'all' }">Semua peran</button>
                    @foreach ($roleFilters as $key => $label)
                        <button type="button" role="tab" x-on:click="role = @js($key)" x-bind:aria-selected="role === @js($key)" x-bind:class="{ 'is-active': role === @js($key) }">{{ $label }}</button>
                    @endforeach
                </div>
            @endif
            <button type="button" class="sd-link" x-show="! guide" x-cloak x-on:click="hideGuide(false)">
                <x-filament::icon icon="heroicon-m-light-bulb" class="sd-link-icon" /> Tampilkan panduan
            </button>
        </section>

        {{-- Panduan singkat --}}
        <section class="sd-guide" x-show="guide" x-collapse>
            <div class="sd-guide-head">
                <x-filament::icon icon="heroicon-o-light-bulb" class="sd-guide-icon" />
                <strong>Cara cepat memakai beranda ini</strong>
                <button type="button" class="sd-guide-close" x-on:click="hideGuide()" aria-label="Tutup panduan">
                    <x-filament::icon icon="heroicon-m-x-mark" class="sd-link-icon" />
                </button>
            </div>
            <ol>
                @foreach ($guides as $i => $step)
                    <li><span>{{ $i + 1 }}</span>{{ $step }}</li>
                @endforeach
            </ol>
        </section>

        {{-- Status & perjalanan akademik mahasiswa --}}
        @if ($student)
            <section>
                <div class="sd-head">
                    <h2>Status Saya</h2>
                    <p>{{ $student['nim'] }} · {{ $student['prodi'] }} · Semester {{ $student['semester'] }}</p>
                </div>
                <div class="sd-grid sd-grid-4">
                    <div class="sd-card sd-stat">
                        <span class="sd-stat-label">IPK</span>
                        <span class="sd-stat-value">{{ $student['ipk'] ?? '-' }}</span>
                        <div class="sd-progress"><span style="width: {{ min(100, ((float) $student['ipk']) / 4 * 100) }}%"></span></div>
                        <span class="sd-muted">dari skala 4,00</span>
                    </div>
                    <div class="sd-card sd-stat">
                        <span class="sd-stat-label">SKS Lulus</span>
                        <span class="sd-stat-value">{{ $student['sks'] ?? 0 }}</span>
                        <div class="sd-progress"><span style="width: {{ min(100, ((int) $student['sks']) / 144 * 100) }}%"></span></div>
                        <span class="sd-muted">target 144 SKS kelulusan</span>
                    </div>
                    @foreach ([['KRS', 'heroicon-o-clipboard-document-list', $student['krs']], ['Surat Terakhir', 'heroicon-o-envelope', $student['surat']]] as [$title, $icon, $item])
                        <div class="sd-card">
                            <div class="sd-card-top">
                                <x-filament::icon :icon="$icon" class="sd-card-icon" />
                                <span class="sd-card-title">{{ $title }}</span>
                            </div>
                            @if ($item)
                                <x-filament::badge :color="$item['tone']">{{ $item['status'] }}</x-filament::badge>
                                <p class="sd-muted">{{ $item['detail'] }}</p>
                            @else
                                <p class="sd-muted">Belum ada data.</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($journey)
            <section class="sd-panel">
                <div class="sd-head">
                    <h2>Perjalanan Akademik</h2>
                    <p>Langkah yang disorot adalah posisi Anda sekarang. Klik langkah untuk membuka halamannya.</p>
                </div>
                <ol class="sd-journey">
                    @foreach ($journey as $i => $step)
                        <li class="sd-journey-step is-{{ $step['state'] }}">
                            <a @if ($step['url']) href="{{ $step['url'] }}" wire:navigate @endif class="sd-journey-link">
                                <span class="sd-journey-dot">
                                    @if ($step['state'] === 'done')
                                        <x-filament::icon icon="heroicon-m-check" />
                                    @else
                                        {{ $i + 1 }}
                                    @endif
                                </span>
                                <span class="sd-journey-text">
                                    <strong>{{ $step['label'] }}</strong>
                                    <span>{{ $step['detail'] }}</span>
                                    @if ($step['state'] === 'current')
                                        <em>Langkah Anda sekarang</em>
                                    @endif
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ol>
            </section>
        @endif

        {{-- Angka kunci --}}
        @if (count($kpis))
            <section class="sd-grid sd-grid-kpi">
                @foreach ($kpis as $kpi)
                    <{{ $kpi['url'] ? 'a' : 'div' }} @if ($kpi['url']) href="{{ $kpi['url'] }}" wire:navigate @endif class="sd-kpi sd-tone-{{ $kpi['tone'] }}">
                        <span class="sd-task-icon"><x-filament::icon :icon="$kpi['icon']" /></span>
                        <span class="sd-kpi-body">
                            <span class="sd-stat-label">{{ $kpi['label'] }}</span>
                            <span class="sd-kpi-value">{{ $kpi['value'] }}</span>
                            <span class="sd-muted">{{ $kpi['hint'] }}</span>
                        </span>
                    </{{ $kpi['url'] ? 'a' : 'div' }}>
                @endforeach
            </section>
        @endif

        {{-- Pekerjaan tertunda --}}
        @if (count($tasks))
            @php($pending = collect($tasks)->where('count', '>', 0))
            <section>
                <div class="sd-head">
                    <h2>Perlu Tindakan</h2>
                    <div class="sd-segment sd-segment-sm">
                        <button type="button" x-on:click="tab = 'pending'" x-bind:class="{ 'is-active': tab === 'pending' }">Menunggu ({{ $pending->count() }})</button>
                        <button type="button" x-on:click="tab = 'urgent'" x-bind:class="{ 'is-active': tab === 'urgent' }">Mendesak ({{ $pending->where('tone', 'danger')->count() }})</button>
                        <button type="button" x-on:click="tab = 'all'" x-bind:class="{ 'is-active': tab === 'all' }">Semua ({{ count($tasks) }})</button>
                    </div>
                </div>
                @if ($pending->isEmpty())
                    <div class="sd-allclear" x-show="tab !== 'all'">
                        <x-filament::icon icon="heroicon-o-check-badge" class="sd-allclear-icon" />
                        <div>
                            <strong>Semua pekerjaan sudah beres.</strong>
                            <span>Kami akan menampilkan di sini bila ada {{ collect($tasks)->pluck('label')->map(fn ($l) => mb_strtolower($l))->take(3)->implode(', ') }}, dan lainnya.</span>
                        </div>
                    </div>
                @endif
                @if ($pending->isNotEmpty() && $pending->where('tone', 'danger')->isEmpty())
                    <p class="sd-muted" x-show="tab === 'urgent'" x-cloak>Tidak ada pekerjaan mendesak saat ini.</p>
                @endif
                <div class="sd-grid sd-grid-3">
                    @foreach ($tasks as $task)
                        @php($tone = $task['count'] > 0 ? $task['tone'] : 'idle')
                        <a
                            href="{{ $task['url'] }}"
                            wire:navigate
                            class="sd-task sd-tone-{{ $tone }}"
                            x-show="inRole(@js($task['roles'])) && match(@js($task['label'] . ' ' . $task['hint'])) && (tab === 'all' || (tab === 'pending' && {{ $task['count'] > 0 ? 'true' : 'false' }}) || (tab === 'urgent' && {{ $task['count'] > 0 && $task['tone'] === 'danger' ? 'true' : 'false' }}))"
                        >
                            <span class="sd-task-icon"><x-filament::icon :icon="$task['icon']" /></span>
                            <span class="sd-task-body">
                                <span class="sd-task-label">{{ $task['label'] }}</span>
                                <span class="sd-task-hint">{{ $task['hint'] }}</span>
                            </span>
                            <span class="sd-task-count">{{ $task['count'] }}</span>
                            <x-filament::icon icon="heroicon-m-chevron-right" class="sd-task-go" />
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Alur kerja --}}
        @if (count($pipelines))
            <section>
                <div class="sd-head">
                    <h2>Alur Kerja</h2>
                    <p>Klik sebuah tahap untuk melihat data di tahap itu tanpa meninggalkan beranda.</p>
                </div>
                <div class="sd-grid sd-grid-2">
                    @foreach ($pipelines as $pipeline)
                        @php($bottleneck = collect($pipeline['stages'])->whereNotIn('tone', ['success', 'gray'])->sortByDesc('count')->first())
                        <div class="sd-panel sd-flow" x-show="inRole(@js($pipeline['roles'])) && match(@js($pipeline['title'] . ' ' . $pipeline['hint']))">
                            <div class="sd-card-top">
                                <x-filament::icon :icon="$pipeline['icon']" class="sd-card-icon" />
                                <span class="sd-card-title">{{ $pipeline['title'] }}</span>
                                <a href="{{ $pipeline['url'] }}" wire:navigate class="sd-flow-all">{{ $pipeline['total'] }} data · Lihat semua</a>
                            </div>
                            <p class="sd-muted">{{ $pipeline['hint'] }}</p>
                            <div class="sd-flow-bar" aria-hidden="true">
                                @foreach ($pipeline['stages'] as $phase)
                                    @if ($phase['count'] > 0)
                                        <span class="sd-bar-{{ $phase['tone'] }}" style="width: {{ $phase['percent'] }}%" title="{{ $phase['label'] }}: {{ $phase['count'] }}"></span>
                                    @endif
                                @endforeach
                            </div>
                            <div class="sd-flow-stages">
                                @foreach ($pipeline['stages'] as $phase)
                                    <button
                                        type="button"
                                        class="sd-stage sd-stage-{{ $phase['tone'] }} @if ($bottleneck && $bottleneck['count'] > 0 && $bottleneck['key'] === $phase['key']) is-bottleneck @endif"
                                        x-on:click="openStage(@js($pipeline['key']), @js($phase['key']))"
                                        @disabled($phase['count'] === 0)
                                        title="{{ $phase['count'] === 0 ? 'Tidak ada data' : 'Lihat ' . $phase['count'] . ' data' }}"
                                    >
                                        <span class="sd-stage-count">{{ $phase['count'] }}</span>
                                        <span class="sd-stage-label">{{ $phase['label'] }}</span>
                                        @if ($bottleneck && $bottleneck['count'] > 0 && $bottleneck['key'] === $phase['key'])
                                            <span class="sd-stage-flag">Penumpukan</span>
                                        @endif
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Grafik --}}
        @if (count($charts))
            <section x-show="q === ''">
                <div class="sd-head">
                    <h2>Analitik</h2>
                    <p>Arahkan kursor ke grafik untuk melihat angka detail.</p>
                </div>
                <div class="sd-grid sd-grid-2">
                    @foreach ($charts as $chart)
                        <div @class(['sd-chart', 'sd-chart-wide' => $chart === 'trend' && count($charts) % 2 === 1])>
                            @livewire(\App\Filament\Widgets\RoleChartWidget::class, ['chart' => $chart], key('sd-chart-' . $chart))
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Pintasan & agenda --}}
        @if (count($actions) || $showAgenda)
            <section class="sd-split">
                @if (count($actions))
                    <div class="sd-panel">
                        <div class="sd-head"><h2>Akses Cepat</h2></div>
                        <div class="sd-actions">
                            @foreach ($actions as $action)
                                <a href="{{ $action['url'] }}" wire:navigate class="sd-action" x-show="inRole(@js($action['roles'])) && match(@js($action['label']))">
                                    <x-filament::icon :icon="$action['icon']" class="sd-action-icon" />
                                    <span>{{ $action['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
                @if ($showAgenda)
                    <div class="sd-panel" x-show="q === ''">
                        <div class="sd-head"><h2>Agenda Sidang</h2></div>
                        @forelse ($agenda as $item)
                            <div class="sd-agenda">
                                <div class="sd-agenda-date">{{ $item['date'] }}<span>{{ $item['time'] }}</span></div>
                                <div>
                                    <div class="sd-agenda-title">{{ $item['title'] }}</div>
                                    <div class="sd-muted">{{ $item['place'] }}</div>
                                </div>
                            </div>
                        @empty
                            <p class="sd-muted">Tidak ada jadwal sidang mendatang.</p>
                        @endforelse
                    </div>
                @endif
            </section>
        @endif

        {{-- Ringkasan risiko (staf & pimpinan) --}}
        @if (count($this->getVisibleWidgets()))
            <section x-show="q === ''">
                <div class="sd-head"><h2>Ringkasan Risiko Akademik</h2></div>
                <x-filament-widgets::widgets :columns="$this->getColumns()" :data="$this->getWidgetData()" :widgets="$this->getVisibleWidgets()" />
            </section>
        @endif

        {{-- Fitur milik role --}}
        <section>
            <div class="sd-head">
                <h2>Fitur Anda</h2>
                <p>Hanya fitur sesuai role Anda. Menu yang sama tersedia di sidebar kiri.</p>
            </div>
            <div class="sd-grid sd-grid-3">
                @foreach ($features as $group => $items)
                    <div class="sd-card sd-feature" x-show="match(@js($group . ' ' . collect($items)->pluck('label')->implode(' ')))">
                        <div class="sd-card-top">
                            <x-filament::icon :icon="$groupIcons[$group] ?? 'heroicon-o-squares-2x2'" class="sd-card-icon" />
                            <span class="sd-card-title">{{ $group }}</span>
                        </div>
                        <ul>
                            @foreach ($items as $item)
                                <li x-show="match(@js($group . ' ' . $item['label']))"><a href="{{ $item['url'] }}" wire:navigate>{{ $item['label'] }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        </section>
    </div>

    {{-- Panel geser: data per tahap alur --}}
    <x-filament::modal id="sd-stage" slide-over width="lg">
        <x-slot name="heading">
            <span wire:loading.remove wire:target="openStage">{{ $stage['title'] ?? 'Memuat…' }}</span>
            <span wire:loading wire:target="openStage">Memuat…</span>
        </x-slot>

        <div wire:loading.flex wire:target="openStage" class="sd-drawer-loading">
            <x-filament::loading-indicator class="h-6 w-6" /> Mengambil data…
        </div>

        <div wire:loading.remove wire:target="openStage">
            @if ($stage)
                <div class="sd-drawer-meta">
                    <x-filament::badge :color="$stage['tone'] === 'gray' ? 'gray' : $stage['tone']">{{ $stage['stage'] }}</x-filament::badge>
                    <span class="sd-muted">{{ $stage['total'] }} data{{ $stage['total'] > count($stage['items']) ? ', menampilkan ' . count($stage['items']) . ' terbaru' : '' }}</span>
                </div>
                <ul class="sd-drawer-list">
                    @forelse ($stage['items'] as $item)
                        <li>
                            <{{ $item['url'] ? 'a' : 'div' }} @if ($item['url']) href="{{ $item['url'] }}" wire:navigate @endif class="sd-drawer-item">
                                <span class="sd-drawer-text">
                                    <strong>{{ $item['title'] }}</strong>
                                    <span class="sd-muted">{{ $item['subtitle'] }}</span>
                                </span>
                                <x-filament::badge :color="$item['tone']">{{ $item['status'] }}</x-filament::badge>
                                @if ($item['url'])
                                    <x-filament::icon icon="heroicon-m-chevron-right" class="sd-task-go" />
                                @endif
                            </{{ $item['url'] ? 'a' : 'div' }}>
                        </li>
                    @empty
                        <li class="sd-muted">Tidak ada data pada tahap ini.</li>
                    @endforelse
                </ul>
            @endif
        </div>

        @if ($stage['url'] ?? null)
            <x-slot name="footer">
                <x-filament::button tag="a" :href="$stage['url']" icon="heroicon-m-arrow-top-right-on-square">Buka daftar lengkap</x-filament::button>
            </x-slot>
        @endif
    </x-filament::modal>
</x-filament-panels::page>
