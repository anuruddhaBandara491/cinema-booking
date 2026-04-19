<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Admin</p>
                <h2 class="text-2xl font-semibold text-white">Failed Payments</h2>
            </div>
            <a href="{{ route('admin.booking-flow-logs.index') }}" class="text-blue-300 hover:text-white underline text-sm">
                ← Back to Logs
            </a>
        </div>
    </x-slot>

    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="space-y-6">

    <!-- Filter Panel -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Filter by Date Range</h2>
        <form method="GET" class="flex gap-4">
            <div class="flex-1">
                <label class="block text-gray-700 text-sm font-semibold mb-2">Start Date</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500">
            </div>
            <div class="flex-1">
                <label class="block text-gray-700 text-sm font-semibold mb-2">End Date</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500">
            </div>
            <div class="flex gap-2 pt-6">
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded font-semibold">
                    Filter
                </button>
                <a href="{{ route('admin.booking-flow-logs.failed-payments') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded font-semibold">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Summary Stats -->
    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
        <p class="text-red-800">
            <span class="font-bold">{{ count($failedPayments->items()) }} payment failures</span> recorded in the system.
        </p>
    </div>

    <!-- Failed Payments Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-200 border-b-2 border-gray-300">
                    <tr>
                        <th class="px-4 py-3 text-left text-gray-700 font-semibold">Failed At</th>
                        <th class="px-4 py-3 text-left text-gray-700 font-semibold">Session ID</th>
                        <th class="px-4 py-3 text-left text-gray-700 font-semibold">Customer Name</th>
                        <th class="px-4 py-3 text-left text-gray-700 font-semibold">Phone</th>
                        <th class="px-4 py-3 text-left text-gray-700 font-semibold">Email</th>
                        <th class="px-4 py-3 text-left text-gray-700 font-semibold">Movie</th>
                        <th class="px-4 py-3 text-left text-gray-700 font-semibold">Seats</th>
                        <th class="px-4 py-3 text-left text-gray-700 font-semibold">Error Message</th>
                        <th class="px-4 py-3 text-left text-gray-700 font-semibold">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($failedPayments as $log)
                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                            <td class="px-4 py-3 text-sm font-mono text-gray-900">
                                <a href="{{ route('admin.booking-flow-logs.timeline', $log->session_id) }}" class="text-blue-600 hover:underline">
                                    {{ Str::limit($log->session_id, 12) }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $log->user_name ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $log->phone_number ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $log->email ?? '-' }}</td>
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
                                @if($log->error_message)
                                    <span class="px-2 py-1 rounded text-xs font-mono bg-red-100 text-red-800">
                                        {{ Str::limit($log->error_message, 30) }}
                                    </span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <a href="{{ route('admin.booking-flow-logs.timeline', $log->session_id) }}" class="text-blue-600 hover:underline">
                                    View Timeline
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-6 text-center text-gray-500">
                                No failed payments found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $failedPayments->links() }}
    </div>
    </div>
    </section>
</x-app-layout>
