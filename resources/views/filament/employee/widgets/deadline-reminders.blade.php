<x-filament-widgets::widget>    <x-filament::section>        <div class="space-y-4">            <!-- Header -->            <div class="flex items-center justify-between">                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Task Reminders</h3>                <div class="flex gap-2">                    @if($total_pending > 0)                        <span class="inline-flex items-center rounded-md bg-yellow-50 px-2 py-1 text-xs font-medium text-yellow-800 ring-1 ring-inset ring-yellow-600/20 dark:bg-yellow-900/20 dark:text-yellow-300 dark:ring-yellow-500/20">                            {{ $total_pending }} Pending                        </span>                    @endif                    @if($total_drafts > 0)                        <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-800 ring-1 ring-inset ring-blue-600/20 dark:bg-blue-900/20 dark:text-blue-300 dark:ring-blue-500/20">                            {{ $total_drafts }} Drafts                        </span>                    @endif                </div>            </div>            <!-- Summary Message -->            @if($total_pending > 0 || $total_drafts > 0)                <div class="rounded-lg bg-amber-50 p-4 dark:bg-amber-900/20">                    <div class="flex">                        <div class="flex-shrink-0">                            <x-heroicon-s-exclamation-triangle class="h-5 w-5 text-amber-400" />                        </div>                        <div class="ml-3">                            <h3 class="text-sm font-medium text-amber-800 dark:text-amber-200">                                Action Required                            </h3>                            <p class="mt-1 text-sm text-amber-700 dark:text-amber-300">                                You have {{ $total_pending + $total_drafts }} items that need your attention.                            </p>                        </div>                    </div>                </div>            @endif            <!-- Items List -->            @if($pending_items->count() > 0)                <div class="space-y-3">                    @foreach($pending_items as $item)                        <div class="flex items-center gap-3 p-3 rounded-lg border {{ $item['is_urgent'] ? 'border-red-200 bg-red-50 dark:border-red-700 dark:bg-red-900/20' : 'border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800' }}">                            <div class="flex h-8 w-8 items-center justify-center rounded-full {{                                 $item['type'] === 'KPI' ? 'bg-blue-100 dark:bg-blue-900' :                                 ($item['type'] === 'OKR' ? 'bg-green-100 dark:bg-green-900' :                                 'bg-gray-100 dark:bg-gray-700')                             }}">                                <span class="text-xs font-medium {{                                     $item['type'] === 'KPI' ? 'text-blue-600 dark:text-blue-400' :                                     ($item['type'] === 'OKR' ? 'text-green-600 dark:text-green-400' :                                     'text-gray-600 dark:text-gray-400')                                 }}">                                    {{ str_replace(' Draft', '', $item['type']) }}                                </span>                            </div>                                                        <div class="flex-1 min-w-0">                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">                                    {{ $item['title'] }}                                </p>                                <div class="flex items-center gap-2 mt-1">                                    <span class="text-xs text-gray-500 dark:text-gray-400">                                        {{ $item['periode'] ?? $current_quarter }}                                    </span>
                                    @if($item['priority'] === 'high')
                                        <span class="inline-flex items-center rounded-full bg-red-100 px-2 py-1 text-xs font-medium text-red-800 dark:bg-red-900/20 dark:text-red-300">
                                            High Priority
                                        </span>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-2">
                                @if(str_contains($item['type'], 'Draft'))
                                    <span class="text-xs font-medium text-blue-600 dark:text-blue-400">
                                        Draft
                                    </span>
                                @else
                                    <span class="text-xs font-medium text-yellow-600 dark:text-yellow-400">
                                        Not Started
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-6">
                    <x-heroicon-o-check-circle class="mx-auto h-12 w-12 text-green-400" />
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">All caught up!</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        You have no pending tasks at the moment.
                    </p>
                </div>
            @endif

            <!-- Action Buttons -->
            @if($total_pending > 0 || $total_drafts > 0)
                <div class="flex gap-2 pt-3 border-t border-gray-200 dark:border-gray-700">
                    <a href="{{ route('filament.employee.resources.realisasi-kpis.index') }}" 
                       class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        View KPI
                    </a>
                    <a href="{{ route('filament.employee.resources.realisasi-okrs.index') }}" 
                       class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        View OKR
                    </a>
                </div>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
