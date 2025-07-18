<div class="space-y-8">
    {{-- Modern Employee Header with Gradient --}}
    <div class="relative bg-gradient-to-br from-purple-600 via-blue-700 to-indigo-800 rounded-2xl p-8 text-white overflow-hidden">
        {{-- Background Pattern --}}
        <div class="absolute inset-0 bg-black opacity-10">
            <svg class="absolute inset-0 h-full w-full" fill="currentColor" viewBox="0 0 100 100">
                <defs>
                    <pattern id="employee-pattern" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse">
                        <circle cx="10" cy="10" r="1.5" fill="currentColor" opacity="0.15"/>
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#employee-pattern)"/>
            </svg>
        </div>
        
        <div class="relative flex items-center justify-between">
            <div class="flex items-center space-x-6">
                <div class="bg-white/20 backdrop-blur-sm p-4 rounded-xl">
                    <x-heroicon-o-user class="w-10 h-10 text-white" />
                </div>
                <div>
                    <h1 class="text-3xl font-bold mb-2">{{ $employee->name }}</h1>
                    <div class="flex items-center space-x-4 text-purple-100">
                        <span class="flex items-center">
                            <x-heroicon-o-envelope class="w-5 h-5 mr-2" />
                            {{ $employee->email }}
                        </span>
                        @if($employee->divisi)
                        <span class="flex items-center">
                            <x-heroicon-o-building-office-2 class="w-5 h-5 mr-2" />
                            {{ $employee->divisi->nama }}
                        </span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="text-right">
                <div class="bg-white/20 backdrop-blur-sm rounded-lg px-4 py-2">
                    <p class="text-sm font-medium">Employee ID</p>
                    <p class="text-xs opacity-90">#{{ str_pad($employee->id, 4, '0', STR_PAD_LEFT) }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Enhanced Performance Overview Cards --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        @php
            $kpiCount = $employee->realisasiKpis->count();
            $kpiAvg = $employee->realisasiKpis->avg('nilai');
            $okrCount = $employee->realisasiOkrs->count();
            $okrAvg = $employee->realisasiOkrs->avg('nilai');
            $overallAvg = ($kpiAvg + $okrAvg) / 2;
            
            $kpiApproved = $employee->realisasiKpis->whereNotNull('approved_at')->count();
            $okrApproved = $employee->realisasiOkrs->whereNotNull('approved_at')->count();
            $totalApproved = $kpiApproved + $okrApproved;
            
            $kpiPending = $employee->realisasiKpis->where('is_cutoff', true)->whereNull('approved_at')->count();
            $okrPending = $employee->realisasiOkrs->where('is_cutoff', true)->whereNull('approved_at')->count();
            $totalPending = $kpiPending + $okrPending;
        @endphp

        {{-- KPI Performance Card --}}
        <div class="bg-gradient-to-br from-emerald-50 to-green-100 border-2 border-emerald-200 rounded-2xl p-6 hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between mb-6">
                <div class="bg-emerald-500 p-4 rounded-xl shadow-lg">
                    <x-heroicon-o-chart-bar class="w-8 h-8 text-white" />
                </div>
                <div class="text-right">
                    <div class="text-3xl font-bold text-emerald-800">{{ $kpiCount }}</div>
                    <div class="text-sm font-medium text-emerald-600">KPI Tasks</div>
                </div>
            </div>
            
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-emerald-700">Performance Average</span>
                    <span class="text-lg font-bold text-emerald-800">
                        {{ $kpiAvg ? number_format($kpiAvg, 1) . '%' : 'N/A' }}
                    </span>
                </div>
                
                <div class="w-full bg-emerald-200 rounded-full h-3">
                    <div class="bg-gradient-to-r from-emerald-500 to-green-500 h-3 rounded-full transition-all duration-700 shadow-sm" 
                         style="width: {{ $kpiAvg ? min($kpiAvg, 100) : 0 }}%"></div>
                </div>
                
                <div class="grid grid-cols-2 gap-3 mt-4">
                    <div class="bg-white/70 rounded-lg p-3 text-center">
                        <div class="text-lg font-bold text-emerald-800">{{ $kpiApproved }}</div>
                        <div class="text-xs text-emerald-600">Approved</div>
                    </div>
                    <div class="bg-white/70 rounded-lg p-3 text-center">
                        <div class="text-lg font-bold text-amber-700">{{ $kpiPending }}</div>
                        <div class="text-xs text-amber-600">Pending</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- OKR Performance Card --}}
        <div class="bg-gradient-to-br from-blue-50 to-indigo-100 border-2 border-blue-200 rounded-2xl p-6 hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between mb-6">
                <div class="bg-blue-500 p-4 rounded-xl shadow-lg">
                    <x-heroicon-o-flag class="w-8 h-8 text-white" />
                </div>
                <div class="text-right">
                    <div class="text-3xl font-bold text-blue-800">{{ $okrCount }}</div>
                    <div class="text-sm font-medium text-blue-600">OKR Goals</div>
                </div>
            </div>
            
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-blue-700">Performance Average</span>
                    <span class="text-lg font-bold text-blue-800">
                        {{ $okrAvg ? number_format($okrAvg, 1) . '%' : 'N/A' }}
                    </span>
                </div>
                
                <div class="w-full bg-blue-200 rounded-full h-3">
                    <div class="bg-gradient-to-r from-blue-500 to-indigo-500 h-3 rounded-full transition-all duration-700 shadow-sm" 
                         style="width: {{ $okrAvg ? min($okrAvg, 100) : 0 }}%"></div>
                </div>
                
                <div class="grid grid-cols-2 gap-3 mt-4">
                    <div class="bg-white/70 rounded-lg p-3 text-center">
                        <div class="text-lg font-bold text-blue-800">{{ $okrApproved }}</div>
                        <div class="text-xs text-blue-600">Approved</div>
                    </div>
                    <div class="bg-white/70 rounded-lg p-3 text-center">
                        <div class="text-lg font-bold text-amber-700">{{ $okrPending }}</div>
                        <div class="text-xs text-amber-600">Pending</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Overall Performance Score --}}
        <div class="bg-gradient-to-br from-purple-50 to-pink-100 border-2 border-purple-200 rounded-2xl p-6 hover:shadow-xl transition-all duration-300">
            <div class="text-center mb-6">
                <div class="bg-gradient-to-br from-purple-500 to-pink-500 p-4 rounded-full shadow-lg mx-auto w-20 h-20 flex items-center justify-center mb-4">
                    <x-heroicon-o-star class="w-10 h-10 text-white" />
                </div>
                <div class="text-4xl font-bold text-purple-800 mb-2">
                    {{ $overallAvg ? number_format($overallAvg, 0) : 'N/A' }}
                </div>
                <div class="text-sm font-medium text-purple-600">Overall Score</div>
            </div>
            
            <div class="space-y-4">
                @if($overallAvg)
                    <div class="text-center">
                        @if($overallAvg >= 90)
                            <span class="bg-green-100 text-green-800 px-4 py-2 rounded-full text-sm font-bold">
                                🏆 Outstanding
                            </span>
                        @elseif($overallAvg >= 80)
                            <span class="bg-blue-100 text-blue-800 px-4 py-2 rounded-full text-sm font-bold">
                                🌟 Excellent
                            </span>
                        @elseif($overallAvg >= 70)
                            <span class="bg-yellow-100 text-yellow-800 px-4 py-2 rounded-full text-sm font-bold">
                                👍 Good
                            </span>
                        @elseif($overallAvg >= 60)
                            <span class="bg-orange-100 text-orange-800 px-4 py-2 rounded-full text-sm font-bold">
                                ⚡ Fair
                            </span>
                        @else
                            <span class="bg-red-100 text-red-800 px-4 py-2 rounded-full text-sm font-bold">
                                📈 Needs Focus
                            </span>
                        @endif
                    </div>
                    
                    <div class="w-full bg-purple-200 rounded-full h-3">
                        <div class="bg-gradient-to-r from-purple-500 to-pink-500 h-3 rounded-full transition-all duration-700 shadow-sm" 
                             style="width: {{ min($overallAvg, 100) }}%"></div>
                    </div>
                @else
                    <div class="text-center">
                        <span class="bg-gray-100 text-gray-600 px-4 py-2 rounded-full text-sm">
                            No Data Available
                        </span>
                    </div>
                @endif
                
                <div class="bg-white/70 rounded-lg p-3 text-center">
                    <div class="text-lg font-bold text-purple-800">{{ $totalApproved + $totalPending }}</div>
                    <div class="text-xs text-purple-600">Total Tasks</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Detailed Task Breakdown --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        {{-- KPI Details --}}
        <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
            <div class="bg-gradient-to-r from-emerald-50 to-green-100 px-6 py-4 border-b border-gray-200">
                <h3 class="text-xl font-bold text-gray-900 flex items-center">
                    <x-heroicon-o-chart-bar class="w-6 h-6 mr-3 text-emerald-600" />
                    KPI Performance Details
                </h3>
            </div>
            <div class="p-6">
                @if($employee->realisasiKpis->count() > 0)
                    <div class="space-y-4">
                        @foreach($employee->realisasiKpis->take(5) as $kpi)
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-3 h-3 rounded-full {{ $kpi->approved_at ? 'bg-green-500' : ($kpi->is_cutoff ? 'bg-yellow-500' : 'bg-gray-400') }}"></div>
                                        <div>
                                            <p class="font-medium text-gray-900 text-sm">{{ Str::limit($kpi->kelolaKpi->nama ?? 'N/A', 30) }}</p>
                                            <p class="text-xs text-gray-500">
                                                {{ $kpi->approved_at ? 'Approved' : ($kpi->is_cutoff ? 'Pending Review' : 'In Progress') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-lg font-bold text-emerald-600">{{ number_format($kpi->nilai, 1) }}%</div>
                                </div>
                            </div>
                        @endforeach
                        
                        @if($employee->realisasiKpis->count() > 5)
                            <div class="text-center py-2">
                                <span class="text-sm text-gray-500">... and {{ $employee->realisasiKpis->count() - 5 }} more KPIs</span>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="text-center py-8">
                        <div class="bg-gray-100 rounded-full p-6 mx-auto w-16 h-16 flex items-center justify-center mb-4">
                            <x-heroicon-o-chart-bar class="w-8 h-8 text-gray-400" />
                        </div>
                        <p class="text-gray-500">No KPI data available</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- OKR Details --}}
        <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
            <div class="bg-gradient-to-r from-blue-50 to-indigo-100 px-6 py-4 border-b border-gray-200">
                <h3 class="text-xl font-bold text-gray-900 flex items-center">
                    <x-heroicon-o-flag class="w-6 h-6 mr-3 text-blue-600" />
                    OKR Performance Details
                </h3>
            </div>
            <div class="p-6">
                @if($employee->realisasiOkrs->count() > 0)
                    <div class="space-y-4">
                        @foreach($employee->realisasiOkrs->take(5) as $okr)
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-3 h-3 rounded-full {{ $okr->approved_at ? 'bg-green-500' : ($okr->is_cutoff ? 'bg-yellow-500' : 'bg-gray-400') }}"></div>
                                        <div>
                                            <p class="font-medium text-gray-900 text-sm">{{ Str::limit($okr->kelolaOkr->nama ?? 'N/A', 30) }}</p>
                                            <p class="text-xs text-gray-500">
                                                {{ $okr->approved_at ? 'Approved' : ($okr->is_cutoff ? 'Pending Review' : 'In Progress') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-lg font-bold text-blue-600">{{ number_format($okr->nilai, 1) }}%</div>
                                </div>
                            </div>
                        @endforeach
                        
                        @if($employee->realisasiOkrs->count() > 5)
                            <div class="text-center py-2">
                                <span class="text-sm text-gray-500">... and {{ $employee->realisasiOkrs->count() - 5 }} more OKRs</span>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="text-center py-8">
                        <div class="bg-gray-100 rounded-full p-6 mx-auto w-16 h-16 flex items-center justify-center mb-4">
                            <x-heroicon-o-flag class="w-8 h-8 text-gray-400" />
                        </div>
                        <p class="text-gray-500">No OKR data available</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
