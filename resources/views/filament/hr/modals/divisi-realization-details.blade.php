<div class="space-y-8">
    {{-- Modern Header with Gradient --}}
    <div class="relative bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-800 rounded-2xl p-8 text-white overflow-hidden">
        {{-- Background Pattern --}}
        <div class="absolute inset-0 bg-black opacity-10">
            <svg class="absolute inset-0 h-full w-full" fill="currentColor" viewBox="0 0 100 100">
                <defs>
                    <pattern id="hero-pattern" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse">
                        <circle cx="10" cy="10" r="2" fill="currentColor" opacity="0.1"/>
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#hero-pattern)"/>
            </svg>
        </div>
        
        <div class="relative flex items-center justify-between">
            <div class="flex items-center space-x-6">
                <div class="bg-white/20 backdrop-blur-sm p-4 rounded-xl">
                    <x-heroicon-o-building-office-2 class="w-10 h-10 text-white" />
                </div>
                <div>
                    <h1 class="text-3xl font-bold mb-2">{{ $divisi->nama }}</h1>
                    <div class="flex items-center space-x-4 text-blue-100">
                        <span class="flex items-center">
                            <x-heroicon-o-users class="w-5 h-5 mr-2" />
                            {{ $divisi->employees_count }} Employee
                        </span>
                        <span class="flex items-center">
                            <x-heroicon-o-chart-bar class="w-5 h-5 mr-2" />
                            Performance Dashboard
                        </span>
                    </div>
                </div>
            </div>
            <div class="text-right">
                <div class="bg-white/20 backdrop-blur-sm rounded-lg px-4 py-2">
                    <p class="text-sm font-medium">Report Generated</p>
                    <p class="text-xs opacity-90">{{ now()->format('d M Y, H:i') }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Enhanced Statistics Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        @php
            $kpiCount = \App\Models\RealisasiKpi::whereHas('user', function($query) use ($divisi) {
                $query->where('divisi_id', $divisi->id);
            })->count();
            
            $kpiAvg = \App\Models\RealisasiKpi::whereHas('user', function($query) use ($divisi) {
                $query->where('divisi_id', $divisi->id);
            })->avg('nilai');
            
            $okrCount = \App\Models\RealisasiOkr::whereHas('user', function($query) use ($divisi) {
                $query->where('divisi_id', $divisi->id);
            })->count();
            
            $okrAvg = \App\Models\RealisasiOkr::whereHas('user', function($query) use ($divisi) {
                $query->where('divisi_id', $divisi->id);
            })->avg('nilai');
            
            $approvedCount = \App\Models\RealisasiKpi::whereHas('user', function($query) use ($divisi) {
                $query->where('divisi_id', $divisi->id);
            })->whereNotNull('approved_at')->count() + 
            \App\Models\RealisasiOkr::whereHas('user', function($query) use ($divisi) {
                $query->where('divisi_id', $divisi->id);
            })->whereNotNull('approved_at')->count();
            
            $pendingCount = \App\Models\RealisasiKpi::whereHas('user', function($query) use ($divisi) {
                $query->where('divisi_id', $divisi->id);
            })->where('is_cutoff', true)->whereNull('approved_at')->count() + 
            \App\Models\RealisasiOkr::whereHas('user', function($query) use ($divisi) {
                $query->where('divisi_id', $divisi->id);
            })->where('is_cutoff', true)->whereNull('approved_at')->count();
        @endphp

        {{-- KPI Card Enhanced --}}
        <div class="relative group">
            <div class="bg-gradient-to-br from-emerald-50 to-green-100 border-2 border-emerald-200 rounded-xl p-6 hover:shadow-lg transition-all duration-300 group-hover:scale-105">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-emerald-500 p-3 rounded-xl shadow-lg">
                        <x-heroicon-o-chart-bar class="w-8 h-8 text-white" />
                    </div>
                    <div class="text-right">
                        <div class="text-3xl font-bold text-emerald-800">{{ $kpiCount }}</div>
                        <div class="text-sm font-medium text-emerald-600">Total KPI</div>
                    </div>
                </div>
                <div class="space-y-2">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-emerald-700">Performance Average</span>
                        <span class="font-bold text-emerald-800">
                            {{ $kpiAvg ? number_format($kpiAvg, 1) . '%' : 'N/A' }}
                        </span>
                    </div>
                    <div class="w-full bg-emerald-200 rounded-full h-2">
                        <div class="bg-emerald-500 h-2 rounded-full transition-all duration-500" 
                             style="width: {{ $kpiAvg ? min($kpiAvg, 100) : 0 }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- OKR Card Enhanced --}}
        <div class="relative group">
            <div class="bg-gradient-to-br from-blue-50 to-indigo-100 border-2 border-blue-200 rounded-xl p-6 hover:shadow-lg transition-all duration-300 group-hover:scale-105">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-blue-500 p-3 rounded-xl shadow-lg">
                        <x-heroicon-o-flag class="w-8 h-8 text-white" />
                    </div>
                    <div class="text-right">
                        <div class="text-3xl font-bold text-blue-800">{{ $okrCount }}</div>
                        <div class="text-sm font-medium text-blue-600">Total OKR</div>
                    </div>
                </div>
                <div class="space-y-2">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-blue-700">Performance Average</span>
                        <span class="font-bold text-blue-800">
                            {{ $okrAvg ? number_format($okrAvg, 1) . '%' : 'N/A' }}
                        </span>
                    </div>
                    <div class="w-full bg-blue-200 rounded-full h-2">
                        <div class="bg-blue-500 h-2 rounded-full transition-all duration-500" 
                             style="width: {{ $okrAvg ? min($okrAvg, 100) : 0 }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Approved Card Enhanced --}}
        <div class="relative group">
            <div class="bg-gradient-to-br from-green-50 to-emerald-100 border-2 border-green-200 rounded-xl p-6 hover:shadow-lg transition-all duration-300 group-hover:scale-105">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-green-500 p-3 rounded-xl shadow-lg">
                        <x-heroicon-o-check-circle class="w-8 h-8 text-white" />
                    </div>
                    <div class="text-right">
                        <div class="text-3xl font-bold text-green-800">{{ $approvedCount }}</div>
                        <div class="text-sm font-medium text-green-600">Approved</div>
                    </div>
                </div>
                <div class="flex items-center justify-center">
                    <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium">
                        ✓ Completed Tasks
                    </span>
                </div>
            </div>
        </div>

        {{-- Pending Card Enhanced --}}
        <div class="relative group">
            <div class="bg-gradient-to-br from-amber-50 to-yellow-100 border-2 border-amber-200 rounded-xl p-6 hover:shadow-lg transition-all duration-300 group-hover:scale-105">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-amber-500 p-3 rounded-xl shadow-lg">
                        <x-heroicon-o-clock class="w-8 h-8 text-white" />
                    </div>
                    <div class="text-right">
                        <div class="text-3xl font-bold text-amber-800">{{ $pendingCount }}</div>
                        <div class="text-sm font-medium text-amber-600">Pending</div>
                    </div>
                </div>
                <div class="flex items-center justify-center">
                    <span class="bg-amber-100 text-amber-800 px-3 py-1 rounded-full text-sm font-medium">
                        ⏳ Awaiting Review
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Modern Employee List --}}
    <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
        <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-8 py-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 flex items-center">
                        <x-heroicon-o-user-group class="w-7 h-7 mr-3 text-blue-600" />
                        Team Members
                    </h2>
                    <p class="text-gray-600 mt-1">Performance overview for each team member</p>
                </div>
                <div class="bg-blue-100 px-4 py-2 rounded-lg">
                    <span class="text-blue-800 font-semibold">{{ $divisi->employees_count }} Members</span>
                </div>
            </div>
        </div>
        
        <div class="divide-y divide-gray-100">
            @forelse($divisi->employees as $index => $employee)
                @php
                    $empKpiCount = $employee->realisasiKpis->count();
                    $empKpiAvg = $employee->realisasiKpis->avg('nilai');
                    $empOkrCount = $employee->realisasiOkrs->count();
                    $empOkrAvg = $employee->realisasiOkrs->avg('nilai');
                    $overallAvg = ($empKpiAvg + $empOkrAvg) / 2;
                @endphp
                <div class="px-8 py-6 hover:bg-gray-50 transition-colors duration-200">
                    <div class="flex items-center justify-between">
                        {{-- Employee Info --}}
                        <div class="flex items-center space-x-4">
                            <div class="relative">
                                <div class="bg-gradient-to-br from-blue-500 to-purple-600 p-3 rounded-full shadow-md">
                                    <x-heroicon-o-user class="w-6 h-6 text-white" />
                                </div>
                                <div class="absolute -top-1 -right-1 bg-green-500 w-4 h-4 rounded-full border-2 border-white"></div>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">{{ $employee->name }}</h3>
                                <p class="text-gray-500 text-sm">{{ $employee->email }}</p>
                            </div>
                        </div>

                        {{-- Performance Metrics --}}
                        <div class="flex items-center space-x-8">
                            {{-- KPI Section --}}
                            <div class="text-center">
                                <div class="bg-emerald-100 rounded-lg px-4 py-3">
                                    <div class="text-2xl font-bold text-emerald-800">{{ $empKpiCount }}</div>
                                    <div class="text-xs font-medium text-emerald-600 uppercase tracking-wide">KPI</div>
                                    <div class="text-sm font-semibold text-emerald-700 mt-1">
                                        {{ $empKpiAvg ? number_format($empKpiAvg, 1) . '%' : 'N/A' }}
                                    </div>
                                </div>
                            </div>

                            {{-- OKR Section --}}
                            <div class="text-center">
                                <div class="bg-blue-100 rounded-lg px-4 py-3">
                                    <div class="text-2xl font-bold text-blue-800">{{ $empOkrCount }}</div>
                                    <div class="text-xs font-medium text-blue-600 uppercase tracking-wide">OKR</div>
                                    <div class="text-sm font-semibold text-blue-700 mt-1">
                                        {{ $empOkrAvg ? number_format($empOkrAvg, 1) . '%' : 'N/A' }}
                                    </div>
                                </div>
                            </div>

                            {{-- Overall Performance --}}
                            <div class="text-center">
                                <div class="bg-purple-100 rounded-lg px-4 py-3">
                                    <div class="text-2xl font-bold text-purple-800">
                                        {{ $overallAvg ? number_format($overallAvg, 0) : 'N/A' }}
                                    </div>
                                    <div class="text-xs font-medium text-purple-600 uppercase tracking-wide">Overall</div>
                                    <div class="text-sm font-semibold mt-1">
                                        @if($overallAvg)
                                            @if($overallAvg >= 85)
                                                <span class="text-green-600">🌟 Excellent</span>
                                            @elseif($overallAvg >= 70)
                                                <span class="text-yellow-600">👍 Good</span>
                                            @else
                                                <span class="text-red-600">📈 Needs Focus</span>
                                            @endif
                                        @else
                                            <span class="text-gray-500">No Data</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-8 py-16 text-center">
                    <div class="bg-gray-100 rounded-full p-8 mx-auto w-24 h-24 flex items-center justify-center mb-6">
                        <x-heroicon-o-user-group class="w-12 h-12 text-gray-400" />
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">No Team Members</h3>
                    <p class="text-gray-500 max-w-md mx-auto">This division doesn't have any employees assigned yet. Add team members to see their performance data here.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
