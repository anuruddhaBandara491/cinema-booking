<x-app-layout>

    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="max-w-2xl mx-auto py-10">
            <h2 class="text-2xl font-bold mb-6">Manage Slider Images</h2>
            <a href="{{ route('slider.create') }}" class="btn-primary mb-4 inline-block">Add New Image</a>
            @if(session('status'))
                <div class="mb-4 text-green-600">{{ session('status') }}</div>
            @endif
            <div class="space-y-4">
                @foreach($images as $image)
                    <div class="flex items-center gap-4 p-4 bg-black rounded shadow border border-secondary">
                        <img src="{{ asset('storage/' . $image->image_path) }}" alt="" class="w-24 h-16 object-cover rounded" />
                        <div class="flex-1">
                            <div class="font-semibold">{{ $image->title }}</div>
                            <div class="text-sm text-gray-500">{{ $image->subtitle }}</div>
                        </div>
                        <form method="POST" action="{{ route('slider.destroy', $image) }}">
                            @csrf
                            @method('DELETE')
                            <button class="btn-ghost text-red-600">Delete</button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
</x-app-layout>
