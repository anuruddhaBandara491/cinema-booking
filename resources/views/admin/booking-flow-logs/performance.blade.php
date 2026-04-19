<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Admin</p>
                <h2 class="text-2xl font-semibold text-white">Performance Metrics</h2>
            </div>
            <a href="{{ route('admin.booking-flow-logs.index') }}" class="text-blue-300 hover:text-white underline text-sm">
                ← Back to Logs
            </a>
        </div>
    </x-slot>

    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="space-y-6">

    <!-- Info Banner -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
        <p class="text-blue-800">
            This page shows the average time users spend between each step of the booking flow. Use this data to identify bottlenecks and performance issues.
        </p>
    </div>

    <!-- Performance Metrics Cards -->
    @if(count($metrics) > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach($metrics as $transition => $data)
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        {{ str_replace('_to_', ' → ', ucfirst(str_replace('_', ' ', $transition))) }}
                    </h3>

                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Average Time:</span>
                            <span class="text-2xl font-bold text-blue-600">{{ $data['average_seconds'] }}s</span>
                        </div>

                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Minimum Time:</span>
                            <span class="text-lg text-gray-900">{{ $data['min_seconds'] }}s</span>
                        </div>

                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Maximum Time:</span>
                            <span class="text-lg text-gray-900">{{ $data['max_seconds'] }}s</span>
                        </div>

                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Total Transitions:</span>
                            <span class="text-lg text-gray-900">{{ $data['total_transitions'] }}</span>
                        </div>

                        <!-- Progress indicator -->
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            @php
                                $avgPercent = min(100, ($data['average_seconds'] / max($metrics, function($item) { return $item['average_seconds']; })['average_seconds'] ?? 1) * 100);
                            @endphp
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-gray-600 w-12">Fast</span>
                                <div class="flex-1 bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-500 h-2 rounded-full" style="width: {{ $avgPercent }}%"></div>
                                </div>
                                <span class="text-xs text-gray-600 w-12 text-right">Slow</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Summary -->
        <div class="bg-white rounded-lg shadow-md p-6 mt-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Summary</h3>

            @php
                $totalAvgTime = array_sum(array_column($metrics, 'average_seconds'));
                $slowestTransition = array_reduce($metrics, function($carry, $item) {
                    if ($carry === null || $item['average_seconds'] > $carry['value']) {
                        return ['transition' => $carry['transition'] ?? '', 'value' => $item['average_seconds']];
                    }
                    return $carry;
                }, null);
                $fastestTransition = array_reduce($metrics, function($carry, $item) {
                    if ($carry === null || ($item['average_seconds'] < $carry['value'] && $item['average_seconds'] > 0)) {
                        return ['transition' => $carry['transition'] ?? '', 'value' => $item['average_seconds']];
                    }
                    return $carry;
                }, null);
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-gray-50 rounded p-4">
                    <p class="text-gray-600 text-sm">Total Booking Flow Time</p>
                    <p class="text-3xl font-bold text-gray-900">{{ round($totalAvgTime, 2) }}s</p>
                </div>

                <div class="bg-yellow-50 rounded p-4">
                    <p class="text-gray-600 text-sm">Slowest Transition</p>
                    @if($slowestTransition)
                        <p class="text-lg font-bold text-yellow-600">
                            @php
                                $parts = explode(' → ', $slowestTransition);
                                echo $parts[0] ?? '';
                            @endphp
                        </p>
                        <p class="text-sm text-gray-600">{{ $slowestTransition }}s</p>
                    @else
                        <p class="text-gray-600">N/A</p>
                    @endif
                </div>

                <div class="bg-green-50 rounded p-4">
                    <p class="text-gray-600 text-sm">Fastest Transition</p>
                    @if($fastestTransition)
                        <p class="text-lg font-bold text-green-600">
                            @php
                                $parts = explode(' → ', $fastestTransition);
                                echo $parts[0] ?? '';
                            @endphp
                        </p>
                        <p class="text-sm text-gray-600">{{ $fastestTransition }}s</p>
                    @else
                        <p class="text-gray-600">N/A</p>
                    @endif
                </div>
            </div>

            <!-- Recommendations -->
            <div class="mt-6 pt-6 border-t border-gray-200">
                <h4 class="font-semibold text-gray-900 mb-2">Recommendations</h4>
                <ul class="list-disc list-inside space-y-2 text-sm text-gray-700">
                    <li>Monitor transitions that take longer than 30 seconds for potential issues</li>
                    <li>Look for outliers where max_seconds is significantly higher than average</li>
                    <li>Correlate slow transitions with high abandonment rates</li>
                    <li>Optimize the slowest step first to improve overall conversion</li>
                </ul>
            </div>
        </div>
    @else
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
            <p class="text-yellow-800">
                No performance data available yet. Wait for more bookings to be completed to see performance metrics.
            </p>
        </div>
    @endif
    </div>
    </section>
</x-app-layout>
