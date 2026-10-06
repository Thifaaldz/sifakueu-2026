<x-filament-panels::page>
    @php
        $modules = $this->getActorModules();
    @endphp

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($modules as $role => $module)
            <x-filament::section>
                <x-slot name="heading">
                    {{ $module['label'] }}
                </x-slot>

                <x-slot name="description">
                    Panel: {{ $module['panel'] }} · Scope: {{ $module['scope'] }}
                </x-slot>

                <div class="space-y-5 text-sm">
                    <div>
                        <h3 class="mb-2 font-semibold text-gray-950 dark:text-white">Menu</h3>
                        <ul class="space-y-1.5">
                            @foreach ($module['menus'] as $menu)
                                <li class="flex gap-2">
                                    <span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-primary-500"></span>
                                    <span>{{ $menu }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div>
                        <h3 class="mb-2 font-semibold text-gray-950 dark:text-white">Boleh</h3>
                        <ul class="space-y-1.5">
                            @foreach ($module['allowed'] as $item)
                                <li class="flex gap-2">
                                    <x-filament::icon icon="heroicon-m-check-circle" class="mt-0.5 h-4 w-4 shrink-0 text-success-600" />
                                    <span>{{ $item }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div>
                        <h3 class="mb-2 font-semibold text-gray-950 dark:text-white">Tidak Boleh</h3>
                        <ul class="space-y-1.5">
                            @foreach ($module['denied'] as $item)
                                <li class="flex gap-2">
                                    <x-filament::icon icon="heroicon-m-x-circle" class="mt-0.5 h-4 w-4 shrink-0 text-danger-600" />
                                    <span>{{ $item }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </x-filament::section>
        @empty
            <x-filament::section>
                <x-slot name="heading">
                    Belum Ada Modul
                </x-slot>

                Role akun ini belum memiliki modul panel yang sesuai.
            </x-filament::section>
        @endforelse
    </div>
</x-filament-panels::page>
