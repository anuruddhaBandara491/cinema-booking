<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Admin</p>
                <h2 class="text-2xl font-semibold text-white">Booking Flow Logs</h2>
            </div>
            <div class="flex gap-2 flex-wrap">
                <a href="{{ route('admin.booking-flow-logs.abandoned') }}" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded text-sm font-medium">
                    Abandoned
                </a>
                <a href="{{ route('admin.booking-flow-logs.failed-payments') }}" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded text-sm font-medium">
                    Failed Payments
                </a>
                <a href="{{ route('admin.booking-flow-logs.performance') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded text-sm font-medium">
                    Performance
                </a>
            </div>
        </div>
    </x-slot>

    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
<div class="space-y-6">

    <!-- Filter Panel -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Filter Logs</h2>
        <form method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div>
                <label class="block text-gray-700 text-sm font-semibold mb-2">Date Range</label>
                <div class="flex gap-2">
                    <input type="date" name="start_date" value="{{ request('start_date') }}" class="flex-1 px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500">
                    <input type="date" name="end_date" value="{{ request('end_date') }}" class="flex-1 px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500">
                </div>
            </div>

            <div>
                <label class="block text-gray-700 text-sm font-semibold mb-2">Phone Number</label>
                <input type="text" name="phone_number" value="{{ request('phone_number') }}" placeholder="Search by phone" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500">
            </div>

            <div>
                <label class="block text-gray-700 text-sm font-semibold mb-2">Email</label>
                <input type="email" name="email" value="{{ request('email') }}" placeholder="Search by email" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500">
            </div>

            <div>
                <label class="block text-gray-700 text-sm font-semibold mb-2">Session ID</label>
                <input type="text" name="session_id" value="{{ request('session_id') }}" placeholder="Search by session" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500">
            </div>

            <div>
                <label class="block text-gray-700 text-sm font-semibold mb-2">Movie</label>
                <select name="movie_id" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500 text-gray-900">
                    <option value="">All Movies</option>
                    @foreach($movies as $movie)
                        <option value="{{ $movie->id }}" {{ request('movie_id') == $movie->id ? 'selected' : '' }}>
                            {{ $movie->title }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-gray-700 text-sm font-semibold mb-2">Step</label>
                <select name="step_name" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500 text-gray-900">
                    <option value="">All Steps</option>
                    @foreach($steps as $step)
                        <option value="{{ $step }}" {{ request('step_name') == $step ? 'selected' : '' }}>
                            {{ ucfirst(str_replace('_', ' ', $step)) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-gray-700 text-sm font-semibold mb-2">Status</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500 text-gray-900">
                    <option value="">All Statuses</option>
                    <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>Success</option>
                    <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                </select>
            </div>

            <div class="flex gap-2 pt-6">
                <button type="submit" class="flex-1 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded font-semibold">
                    Filter
                </button>
                <a href="{{ route('admin.booking-flow-logs.index') }}" class="flex-1 bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded font-semibold text-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Logs Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-200 border-b-2 border-gray-300">
                    <tr>
                        <th class="px-4 py-3 text-left text-gray-700 font-semibold">Date/Time</th>
                        <th class="px-4 py-3 text-left text-gray-700 font-semibold">Session ID</th>
                        <th class="px-4 py-3 text-left text-gray-700 font-semibold">Step</th>
                        <th class="px-4 py-3 text-left text-gray-700 font-semibold">Action</th>
                        <th class="px-4 py-3 text-left text-gray-700 font-semibold">Name</th>
                        <th class="px-4 py-3 text-left text-gray-700 font-semibold">Phone</th>
                        <th class="px-4 py-3 text-left text-gray-700 font-semibold">Movie</th>
                        <th class="px-4 py-3 text-left text-gray-700 font-semibold">Seats</th>
                        <th class="px-4 py-3 text-left text-gray-700 font-semibold">Status</th>
                        <th class="px-4 py-3 text-left text-gray-700 font-semibold">IP Address</th>
                        <th class="px-4 py-3 text-left text-gray-700 font-semibold">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                            <td class="px-4 py-3 text-sm font-mono text-gray-900">
                                <a href="{{ route('admin.booking-flow-logs.timeline', $log->session_id) }}" class="text-blue-600 hover:underline">
                                    {{ Str::limit($log->session_id, 12) }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <span class="px-2 py-1 rounded text-xs font-semibold bg-blue-100 text-blue-800">
                                    {{ ucfirst(str_replace('_', ' ', $log->step_name)) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ ucfirst(str_replace('_', ' ', $log->action)) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $log->user_name ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $log->phone_number ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900">
                                @if($log->movie)
                                    {{ $log->movie->title }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900">
                                @if($log->selected_seats)
                                    {{ implode(', ', $log->selected_seats) }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm">
                                @if($log->status === 'success')
                                    <span class="px-2 py-1 rounded text-xs font-semibold bg-green-100 text-green-800">Success</span>
                                @else
                                    <span class="px-2 py-1 rounded text-xs font-semibold bg-red-100 text-red-800">Failed</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm font-mono text-xs text-gray-900">{{ $log->ip_address ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm">
                                <a href="{{ route('admin.booking-flow-logs.timeline', $log->session_id) }}" class="text-blue-600 hover:underline">
                                    View Timeline
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="px-4 py-6 text-center text-gray-500">
                                No logs found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $logs->links() }}
    </div>
    </div>
    </section>
</x-app-layout>
