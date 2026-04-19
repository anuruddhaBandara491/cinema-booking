<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Admin</p>
                <h2 class="text-2xl font-semibold text-white">Abandoned Bookings</h2>
            </div>
            <a href="{{ route('admin.booking-flow-logs.index') }}" class="text-blue-300 hover:text-white underline text-sm">
                ← Back to Logs
            </a>
        </div>
    </x-slot>

    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="space-y-6">

    <!-- Summary Stats -->
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
        <p class="text-yellow-800">
            <span class="font-bold">{{ count($abandonedSessions->items()) }} sessions</span> have not completed payment after reaching seat selection or ticket selection.
        </p>
    </div>

    <!-- Abandoned Bookings Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-200 border-b-2 border-gray-300">
                    <tr>
                        <th class="px-4 py-3 text-left text-gray-700 font-semibold">Abandoned At</th>
                        <th class="px-4 py-3 text-left text-gray-700 font-semibold">Session ID</th>
                        <th class="px-4 py-3 text-left text-gray-700 font-semibold">Customer Name</th>
                        <th class="px-4 py-3 text-left text-gray-700 font-semibold">Phone</th>
                        <th class="px-4 py-3 text-left text-gray-700 font-semibold">Email</th>
                        <th class="px-4 py-3 text-left text-gray-700 font-semibold">Movie</th>
                        <th class="px-4 py-3 text-left text-gray-700 font-semibold">Last Step</th>
                        <th class="px-4 py-3 text-left text-gray-700 font-semibold">Log Count</th>
                        <th class="px-4 py-3 text-left text-gray-700 font-semibold">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($abandonedSessions as $session)
                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $session['abandoned_at']->format('Y-m-d H:i:s') }}</td>
                            <td class="px-4 py-3 text-sm font-mono text-gray-900">
                                <a href="{{ route('admin.booking-flow-logs.timeline', $session['session_id']) }}" class="text-blue-600 hover:underline">
                                    {{ Str::limit($session['session_id'], 12) }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $session['user_name'] ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $session['phone_number'] ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $session['email'] ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900">
                                @if($session['movie_id'])
                                    @php
                                        $movie = \App\Models\Movie::find($session['movie_id']);
                                    @endphp
                                    @if($movie)
                                        {{ $movie->title }}
                                    @else
                                        -
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <span class="px-2 py-1 rounded text-xs font-semibold bg-orange-100 text-orange-800">
                                    {{ ucfirst(str_replace('_', ' ', $session['last_step'])) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-center text-gray-900">{{ $session['log_count'] }}</td>
                            <td class="px-4 py-3 text-sm">
                                <a href="{{ route('admin.booking-flow-logs.timeline', $session['session_id']) }}" class="text-blue-600 hover:underline">
                                    View Timeline
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-6 text-center text-gray-500">
                                No abandoned bookings found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $abandonedSessions->links() }}
    </div>
    </div>
    </section>
</x-app-layout>
