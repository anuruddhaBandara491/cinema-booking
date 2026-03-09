<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Admin</p>
                <h2 class="text-2xl font-semibold text-white">Booking Session Timeline</h2>
            </div>
            <a href="{{ route('admin.booking-flow-logs.index') }}" class="text-blue-300 hover:text-white underline text-sm">
                ← Back to Logs
            </a>
        </div>
    </x-slot>

    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="space-y-6">

    <!-- Session Summary -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <p class="text-gray-600 text-sm">Session ID</p>
                <p class="text-lg font-mono font-bold text-gray-900">{{ Str::limit($sessionId, 20) }}</p>
            </div>
            <div>
                <p class="text-gray-600 text-sm">Duration</p>
                <p class="text-lg font-bold text-gray-900">{{ $totalDuration }}s</p>
            </div>
            <div>
                <p class="text-gray-600 text-sm">Total Steps</p>
                <p class="text-lg font-bold text-gray-900">{{ $logs->count() }}</p>
            </div>
            <div>
                <p class="text-gray-600 text-sm">Status</p>
                @if($isComplete)
                    <span class="text-lg font-bold text-green-600">✓ Completed</span>
                @else
                    <span class="text-lg font-bold text-yellow-600">⚠ Incomplete</span>
                @endif
            </div>
            <div>
                <p class="text-gray-600 text-sm">Customer</p>
                <p class="text-lg font-bold text-gray-900">{{ $firstLog->user_name ?? '-' }}</p>
            </div>
        </div>
    </div>

    <!-- Timeline -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-6">Event Timeline</h2>

        <div class="space-y-4">
            @foreach($logs as $index => $log)
                <div class="flex gap-4">
                    <!-- Timeline dot -->
                    <div class="flex flex-col items-center">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-sm font-bold
                            @if($log->status === 'success')
                                bg-green-500
                            @else
                                bg-red-500
                            @endif">
                            {{ $index + 1 }}
                        </div>
                        @if($index < $logs->count() - 1)
                            <div class="w-1 h-12 bg-gray-300 my-1"></div>
                        @endif
                    </div>

                    <!-- Event details -->
                    <div class="flex-1 pb-4">
                        <div class="bg-gray-50 rounded p-4">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <h3 class="font-bold text-gray-900">
                                        {{ ucfirst(str_replace('_', ' ', $log->step_name)) }}
                                    </h3>
                                    <p class="text-sm text-gray-600">
                                        Action: <span class="font-mono">{{ ucfirst(str_replace('_', ' ', $log->action)) }}</span>
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-gray-500">{{ $log->created_at->format('H:i:s') }}</p>
                                    @if($index > 0)
                                        <p class="text-xs text-gray-500">
                                            +{{ $log->created_at->diffInSeconds($logs[$index - 1]->created_at) }}s
                                        </p>
                                    @endif
                                </div>
                            </div>

                            <!-- Event details grid -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm mt-3 pt-3 border-t border-gray-200">
                                @if($log->user_name)
                                    <div><span class="text-gray-700 font-semibold">Name:</span> <span class="font-medium text-gray-900">{{ $log->user_name }}</span></div>
                                @else
                                    <div><span class="text-gray-700 font-semibold">Name:</span> <span class="text-gray-500 italic">Not provided</span></div>
                                @endif
                                @if($log->phone_number)
                                    <div><span class="text-gray-700 font-semibold">Phone:</span> <span class="font-medium text-gray-900">{{ $log->phone_number }}</span></div>
                                @else
                                    <div><span class="text-gray-700 font-semibold">Phone:</span> <span class="text-gray-500 italic">Not provided</span></div>
                                @endif
                                @if($log->email)
                                    <div><span class="text-gray-700 font-semibold">Email:</span> <span class="font-medium text-gray-900">{{ $log->email }}</span></div>
                                @else
                                    <div><span class="text-gray-700 font-semibold">Email:</span> <span class="text-gray-500 italic">Not provided</span></div>
                                @endif
                                @if($log->movie)
                                    <div><span class="text-gray-700 font-semibold">Movie:</span> <span class="font-medium text-gray-900">{{ $log->movie->title }}</span></div>
                                @endif
                                @if($log->show_date)
                                    <div><span class="text-gray-700 font-semibold">Show Date:</span> <span class="font-medium text-gray-900">{{ $log->show_date }}</span></div>
                                @endif
                                @if($log->show_time)
                                    <div><span class="text-gray-700 font-semibold">Show Time:</span> <span class="font-medium text-gray-900">{{ $log->show_time }}</span></div>
                                @endif
                                @if($log->ticket_count)
                                    <div><span class="text-gray-700 font-semibold">Tickets:</span> <span class="font-medium text-gray-900">{{ $log->ticket_count }}</span></div>
                                @endif
                                @if($log->selected_seats)
                                    <div><span class="text-gray-700 font-semibold">Seats:</span> <span class="font-medium text-gray-900">{{ implode(', ', $log->selected_seats) }}</span></div>
                                @endif
                                @if($log->ip_address)
                                    <div><span class="text-gray-700 font-semibold">IP:</span> <span class="font-mono text-xs text-gray-900">{{ $log->ip_address }}</span></div>
                                @endif
                                @if($log->status === 'failed' && $log->error_message)
                                    <div class="col-span-2 bg-red-50 p-2 rounded text-red-700">
                                        <span class="text-gray-700 font-semibold">Error:</span> {{ $log->error_message }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    </div>
    </section>
</x-app-layout>
