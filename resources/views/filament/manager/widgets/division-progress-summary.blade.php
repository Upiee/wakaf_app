<div class="fi-wi-stats-overview-stat relative rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
            {{ $division_name }} - Progress Summary
        </h3>
        <div class="flex items-center space-x-2">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                Data: {{ $target_quarter }}
            </span>
            @if(count($available_quarters) > 1)
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200" title="Quartal tersedia: {{ implode(', ', $available_quarters) }}">
                    {{ count($available_quarters) }} Quarters Available
                </span>
            @endif
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                {{ $total_employees }} Employees
            </span>
        </div>
    </div>

    <!-- Overall Performance Card -->
    <div class="rounded-xl bg-custom-50 p-6 dark:bg-custom-400/10 mb-6" style="--c-50:var(--{{ $overall['performance_color'] }}-50);--c-400:var(--{{ $overall['performance_color'] }}-400);--c-600:var(--{{ $overall['performance_color'] }}-600);">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-custom-600 dark:text-custom-400" style="--c-400:var(--{{ $overall['performance_color'] }}-400);--c-600:var(--{{ $overall['performance_color'] }}-600);">
                    Overall Division Performance
                </p>
                <p class="text-3xl font-bold text-custom-600 dark:text-custom-400" style="--c-400:var(--{{ $overall['performance_color'] }}-400);--c-600:var(--{{ $overall['performance_color'] }}-600);">
                    {{ number_format($overall['weighted_score'], 1) }}%
                </p>
                <p class="text-sm text-custom-600 dark:text-custom-400" style="--c-400:var(--{{ $overall['performance_color'] }}-400);--c-600:var(--{{ $overall['performance_color'] }}-600);">
                    {{ $overall['performance_level'] }}
                </p>
            </div>
            <div class="text-right">
                <div class="grid grid-cols-3 gap-4 text-center">
                    <div>
                        <p class="text-lg font-semibold text-green-600 dark:text-green-400">{{ $overall['total_completed'] }}</p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Completed</p>
                    </div>
                    <div>
                        <p class="text-lg font-semibold text-yellow-600 dark:text-yellow-400">{{ $overall['total_pending'] }}</p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Pending</p>
                    </div>
                    <div>
                        <p class="text-lg font-semibold text-gray-600 dark:text-gray-400">{{ $overall['total_draft'] }}</p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Draft</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI & OKR Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <!-- KPI Section -->
        <div class="rounded-xl bg-blue-50 p-6 dark:bg-blue-400/10">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-500/10">
                        <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h4 class="text-base font-semibold text-gray-900 dark:text-white">KPI Progress</h4>
                        <p class="text-sm text-gray-500 dark:text-gray-400">({{ number_format($kpi_stats['weight_percentage'], 1) }}% weight)</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($kpi_stats['avg_score'], 1) }}%</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Avg Score</p>
                </div>
            </div>
            
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-gray-600 dark:text-gray-300">Total Assigned</span>
                    <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $kpi_stats['total_assigned'] }}</span>
                </div>
                
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-gray-600 dark:text-gray-300">Completed</span>
                    <span class="text-sm font-semibold text-green-600 dark:text-green-400">{{ $kpi_stats['completed'] }}</span>
                </div>
                
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-gray-600 dark:text-gray-300">Pending Approval</span>
                    <span class="text-sm font-semibold text-yellow-600 dark:text-yellow-400">{{ $kpi_stats['pending'] }}</span>
                </div>
                
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-gray-600 dark:text-gray-300">Draft</span>
                    <span class="text-sm font-semibold text-gray-600 dark:text-gray-400">{{ $kpi_stats['draft'] }}</span>
                </div>
                
                <!-- Completion Rate Progress Bar -->
                <div class="pt-2">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-300">Completion Rate</span>
                        <span class="text-sm font-semibold text-blue-600 dark:text-blue-400">{{ number_format($kpi_stats['completion_rate'], 1) }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                        <div class="bg-blue-600 h-2 rounded-full dark:bg-blue-400" style="width: {{ $kpi_stats['completion_rate'] }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- OKR Section -->
        <div class="rounded-xl bg-green-50 p-6 dark:bg-green-400/10">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-green-500/10">
                        <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h4 class="text-base font-semibold text-gray-900 dark:text-white">OKR Progress</h4>
                        <p class="text-sm text-gray-500 dark:text-gray-400">({{ number_format($okr_stats['weight_percentage'], 1) }}% weight)</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ number_format($okr_stats['avg_score'], 1) }}%</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Avg Score</p>
                </div>
            </div>
            
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-gray-600 dark:text-gray-300">Total Assigned</span>
                    <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $okr_stats['total_assigned'] }}</span>
                </div>
                
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-gray-600 dark:text-gray-300">Completed</span>
                    <span class="text-sm font-semibold text-green-600 dark:text-green-400">{{ $okr_stats['completed'] }}</span>
                </div>
                
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-gray-600 dark:text-gray-300">Pending Approval</span>
                    <span class="text-sm font-semibold text-yellow-600 dark:text-yellow-400">{{ $okr_stats['pending'] }}</span>
                </div>
                
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-gray-600 dark:text-gray-300">Draft</span>
                    <span class="text-sm font-semibold text-gray-600 dark:text-gray-400">{{ $okr_stats['draft'] }}</span>
                </div>
                
                <!-- Completion Rate Progress Bar -->
                <div class="pt-2">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-300">Completion Rate</span>
                        <span class="text-sm font-semibold text-green-600 dark:text-green-400">{{ number_format($okr_stats['completion_rate'], 1) }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                        <div class="bg-green-600 h-2 rounded-full dark:bg-green-400" style="width: {{ $okr_stats['completion_rate'] }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>