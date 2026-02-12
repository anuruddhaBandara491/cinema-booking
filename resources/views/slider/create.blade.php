<x-app-layout>
    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="max-w-xl mx-auto py-10">
            <h2 class="text-2xl font-bold mb-6">Add Slider Image</h2>
            <form method="POST" action="{{ route('slider.store') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf
                <div>
                    <label class="text-xs uppercase tracking-[0.2em] text-slate-400">Title</label>
                    <input type="text" name="title" x-model="form.title" class="mt-2 w-full rounded-xl border border-white/10 bg-canvas-muted px-4 py-3 text-sm text-white" />
                </div>
                <div>
                    <label class="text-xs uppercase tracking-[0.2em] text-slate-400">Subtitle</label>
                    <input type="text" name="subtitle" class="mt-2 w-full rounded-xl border border-white/10 bg-canvas-muted px-4 py-3 text-sm text-white" />
                </div>
                <div>
                    <label class="text-xs uppercase tracking-[0.2em] text-slate-400">Image <span class="text-red-500">*</span></label>
                    <input type="file" name="image" required class="mt-2 w-full rounded-xl border border-white/10 bg-canvas-muted px-4 py-3 text-sm text-white" />
                </div>
                <div>
                    <label class="text-xs uppercase tracking-[0.2em] text-slate-400">Order<span class="text-red-500">*</span>   </label>
                    <input type="number" name="order" required class="mt-2 w-24 rounded-xl border border-white/10 bg-canvas-muted px-4 py-3 text-sm text-white" />
                </div>
                <button class="btn-primary">Add Image</button>
            </form>
        </div>
    </section>
</x-app-layout>
