<div class="fi-wi-stats-overview-stat relative rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
    <div class="grid gap-6">
        <div class="flex items-center gap-2">
            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-100 dark:bg-indigo-900">
                <svg class="h-5 w-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">My Progress Summary</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $current_quarter }}</p>
            </div>
        </div>
        
        <!-- Overall Progress -->
        <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-800">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Overall Completion</span>
                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ number_format($overall_completion_rate, 1) }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                <div class="bg-indigo-600 h-2 rounded-full transition-all duration-300" style="width: {{ $overall_completion_rate }}%"></div>
            </div>
        </div>
        
        <!-- KPI & OKR Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- KPI Stats -->
            <div class="space-y-3">
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 rounded-full bg-blue-500"></div>
                    <h4 class="font-medium text-gray-900 dark:text-white">KPI Progress</h4>
                </div>
                
                <div class="space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-400">Total KPIs</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ $kpi_stats['total'] }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-400">Completed</span>
                        <span class="font-medium text-green-600 dark:text-green-400">{{ $kpi_stats['completed'] }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-400">Pending Approval</span>
                        <span class="font-medium text-orange-600 dark:text-orange-400">{{ $kpi_stats['pending'] }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-400">Draft</span>
                        <span class="font-medium text-gray-600 dark:text-gray-400">{{ $kpi_stats['draft'] }}</span>
                    </div>
                </div>
                
                <div class="pt-2">
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-600 dark:text-gray-400">Completion Rate</span>
                        <span class="font-medium text-blue-600 dark:text-blue-400">{{ number_format($kpi_stats['completion_rate'], 1) }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                        <div class="bg-blue-500 h-2 rounded-full transition-all duration-300" style="width: {{ $kpi_stats['completion_rate'] }}%"></div>
                    </div>
                </div>
                
                @if($kpi_stats['avg_score'] > 0)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-400">Average Score</span>
                        <span class="font-medium text-blue-600 dark:text-blue-400">{{ number_format($kpi_stats['avg_score'], 1) }}%</span>
                    </div>
                @endif
            </div>
            
            <!-- OKR Stats -->
            <div class="space-y-3">
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 rounded-full bg-green-500"></div>
                    <h4 class="font-medium text-gray-900 dark:text-white">OKR Progress</h4>
                </div>
                
                <div class="space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-400">Total OKRs</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ $okr_stats['total'] }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-400">Completed</span>
                        <span class="font-medium text-green-600 dark:text-green-400">{{ $okr_stats['completed'] }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-400">Pending Approval</span>
                        <span class="font-medium text-orange-600 dark:text-orange-400">{{ $okr_stats['pending'] }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-400">Draft</span>
                        <span class="font-medium text-gray-600 dark:text-gray-400">{{ $okr_stats['draft'] }}</span>
                    </div>
                </div>
                
                <div class="pt-2">
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-600 dark:text-gray-400">Completion Rate</span>
                        <span class="font-medium text-green-600 dark:text-green-400">{{ number_format($okr_stats['completion_rate'], 1) }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                        <div class="bg-green-500 h-2 rounded-full transition-all duration-300" style="width: {{ $okr_stats['completion_rate'] }}%"></div>
                    </div>
                </div>
                
                @if($okr_stats['avg_score'] > 0)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-400">Average Score</span>
                        <span class="font-medium text-green-600 dark:text-green-400">{{ number_format($okr_stats['avg_score'], 1) }}%</span>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
