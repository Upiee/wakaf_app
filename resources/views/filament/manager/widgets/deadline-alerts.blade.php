<x-filament-widgets::widget>
    <x-filament::section>
        <div class="space-y-4">
            <!-- Pending Approvals Alert -->
            @if($pendingApprovals > 0)
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <x-heroicon-o-exclamation-triangle class="h-5 w-5 text-yellow-400" />
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">
                                {{ $pendingApprovals }} Pending Approvals
                            </h3>
                            <div class="mt-2 text-sm text-yellow-700">
                                <p>You have {{ $pendingApprovals }} items waiting for your approval.</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Urgent KPIs -->
            @if($urgentKpis->count() > 0)
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex items-center mb-3">
                        <div class="flex-shrink-0">
                            <x-heroicon-o-clock class="h-5 w-5 text-red-400" />
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">
                                Urgent KPIs ({{ $urgentKpis->count() }})
                            </h3>
                        </div>
                    </div>
                    <div class="space-y-2">
                        @foreach($urgentKpis as $kpi)
                            <div class="text-sm text-red-700 flex justify-between items-center">
                                <span>{{ $kpi->user->name }} - {{ Str::limit($kpi->kpi->activity ?? 'N/A', 30) }}</span>
                                <span class="text-xs bg-red-100 text-red-800 px-2 py-1 rounded">
                                    {{ $kpi->periode }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Urgent OKRs -->
            @if($urgentOkrs->count() > 0)
                <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                    <div class="flex items-center mb-3">
                        <div class="flex-shrink-0">
                            <x-heroicon-o-clock class="h-5 w-5 text-orange-400" />
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-orange-800">
                                Urgent OKRs ({{ $urgentOkrs->count() }})
                            </h3>
                        </div>
                    </div>
                    <div class="space-y-2">
                        @foreach($urgentOkrs as $okr)
                            <div class="text-sm text-orange-700 flex justify-between items-center">
                                <span>{{ $okr->user->name }} - {{ Str::limit($okr->okr->activity ?? 'N/A', 30) }}</span>
                                <span class="text-xs bg-orange-100 text-orange-800 px-2 py-1 rounded">
                                    {{ $okr->periode }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- No Urgent Items -->
            @if($urgentKpis->count() == 0 && $urgentOkrs->count() == 0 && $pendingApprovals == 0)
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <x-heroicon-o-check-circle class="h-5 w-5 text-green-400" />
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-green-800">
                                All Clear!
                            </h3>
                            <div class="mt-2 text-sm text-green-700">
                                <p>No urgent items or pending approvals at this time.</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
