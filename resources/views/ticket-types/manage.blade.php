<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Admin</p>
                <h2 class="text-2xl font-semibold text-white">Ticket types</h2>
            </div>
            <button type="button" class="btn-primary" onclick="window.dispatchEvent(new CustomEvent('ticket-form:open', { detail: { mode: 'create' } }))">
                Add Ticket Type
            </button>
        </div>
    </x-slot>

    <section class="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:px-8" x-data="{
        mode: 'create',
        formAction: '{{ route('admin.ticket-types.store') }}',
        method: 'post',
        form: {
            id: null,
            name: '',
            adult_price: '',
            has_child: false,
            child_price: '',
        },
        openForm(payload) {
            this.mode = payload.mode || 'create';
            if (this.mode === 'edit') {
                this.form = {
                    id: payload.ticket.id,
                    name: payload.ticket.name,
                    adult_price: payload.ticket.adult_price,
                    has_child: payload.ticket.has_child,
                    child_price: payload.ticket.child_price || '',
                };
                this.formAction = payload.ticket.update_url;
                this.method = 'put';
            } else {
                this.form = {
                    id: null,
                    name: '',
                    adult_price: '',
                    has_child: false,
                    child_price: '',
                };
                this.formAction = '{{ route('admin.ticket-types.store') }}';
                this.method = 'post';
            }
            $dispatch('open-modal', 'ticket-form');
        },
        openDelete(payload) {
            this.form = { id: payload.ticket.id, name: payload.ticket.name };
            this.formAction = payload.ticket.delete_url;
            $dispatch('open-modal', 'ticket-delete');
        }
    }" x-on:ticket-form:open.window="openForm($event.detail)">
        @if (session('status'))
            <div class="mb-6 rounded-xl border border-emerald-400/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
                {{ session('status') }}
            </div>
        @endif

        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($ticketTypes as $ticketType)
                @php
                    $ticketPayload = [
                        'id' => $ticketType->id,
                        'name' => $ticketType->name,
                        'adult_price' => $ticketType->adult_price,
                        'has_child' => $ticketType->has_child,
                        'child_price' => $ticketType->child_price,
                        'update_url' => route('admin.ticket-types.update', $ticketType),
                        'delete_url' => route('admin.ticket-types.destroy', $ticketType),
                    ];
                    $ticketPayloadJson = json_encode(
                        $ticketPayload,
                        JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
                    );
                @endphp
                <div class="card-surface p-5" x-data="{ ticket: JSON.parse($el.dataset.ticket) }" data-ticket='{{ $ticketPayloadJson }}'>
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Ticket type</p>
                            <h3 class="text-lg font-semibold text-white">{{ $ticketType->name }}</h3>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" x-on:click="openForm({ mode: 'edit', ticket })" class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-white/10 bg-canvas-muted text-white transition hover:border-accent/60">
                                <span class="text-sm">✎</span>
                            </button>
                            <button type="button" x-on:click="openDelete({ ticket })" class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-rose-500/20 bg-rose-500/10 text-rose-200 transition hover:border-rose-500/60">
                                <span class="text-sm">🗑</span>
                            </button>
                        </div>
                    </div>
                    <div class="mt-4 space-y-2 text-sm text-slate-300">
                        <div class="flex items-center justify-between">
                            <span>Adult price</span>
                            <span class="text-white">LKR {{ number_format($ticketType->adult_price, 2) }}</span>
                        </div>
                        @if ($ticketType->has_child)
                            <div class="flex items-center justify-between">
                                <span>Child price</span>
                                <span class="text-white">LKR {{ number_format($ticketType->child_price, 2) }}</span>
                            </div>
                        @else
                            <div class="text-xs uppercase tracking-[0.2em] text-slate-500">No child ticket</div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-full rounded-2xl border border-white/10 bg-canvas-muted p-10 text-center text-sm text-slate-400">
                    No ticket types yet. Add your first ticket type.
                </div>
            @endforelse
        </div>

        <x-modal name="ticket-form" maxWidth="lg" focusable>
            <div class="bg-slate-950 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-[0.2em] text-slate-400" x-text="mode === 'create' ? 'Add ticket type' : 'Edit ticket type'"></p>
                        <h3 class="text-xl font-semibold text-white" x-text="mode === 'create' ? 'Create ticket type' : 'Update ticket type'"></h3>
                    </div>
                    <button class="text-slate-400 hover:text-white" x-on:click="$dispatch('close-modal', 'ticket-form')">✕</button>
                </div>

                <form class="mt-6 space-y-4" method="POST" :action="formAction">
                    @csrf
                    <template x-if="method === 'put'">
                        <input type="hidden" name="_method" value="PUT" />
                    </template>

                    <div>
                        <label class="text-xs uppercase tracking-[0.2em] text-slate-400">Ticket name</label>
                        <input type="text" name="name" x-model="form.name" required
                               class="mt-2 w-full rounded-xl border border-white/10 bg-canvas-muted px-4 py-3 text-sm text-white" />
                    </div>

                    <div>
                        <label class="text-xs uppercase tracking-[0.2em] text-slate-400">Adult price (LKR)</label>
                        <input type="number" name="adult_price" step="0.01" min="0" x-model="form.adult_price" required
                               class="mt-2 w-full rounded-xl border border-white/10 bg-canvas-muted px-4 py-3 text-sm text-white" />
                    </div>

                    <div class="flex items-center gap-3">
                        <input type="checkbox" id="has_child" name="has_child" value="1" x-model="form.has_child"
                               class="h-4 w-4 rounded border-white/20 bg-slate-900 text-amber-300" />
                        <label for="has_child" class="text-xs uppercase tracking-[0.2em] text-slate-400">Allow child ticket</label>
                    </div>

                    <div x-show="form.has_child" x-transition.opacity.duration.200>
                        <label class="text-xs uppercase tracking-[0.2em] text-slate-400">Child price (LKR)</label>
                        <input type="number" name="child_price" step="0.01" min="0" x-model="form.child_price" :required="form.has_child"
                               class="mt-2 w-full rounded-xl border border-white/10 bg-canvas-muted px-4 py-3 text-sm text-white" />
                    </div>

                    <div class="flex justify-end gap-3">
                        <button type="button" class="btn-ghost" x-on:click="$dispatch('close-modal', 'ticket-form')">Cancel</button>
                        <button class="btn-primary" type="submit" x-text="mode === 'create' ? 'Save Ticket' : 'Save Changes'"></button>
                    </div>
                </form>
            </div>
        </x-modal>

        <x-modal name="ticket-delete" maxWidth="lg" focusable>
            <div class="bg-slate-950 p-6">
                <h3 class="text-xl font-semibold text-white">Delete ticket type</h3>
                <p class="mt-2 text-sm text-slate-400">Are you sure you want to remove <span class="text-white" x-text="form.name"></span>?</p>
                <form method="POST" :action="formAction" class="mt-6 flex justify-end gap-3">
                    @csrf
                    @method('delete')
                    <button type="button" class="btn-ghost" x-on:click="$dispatch('close-modal', 'ticket-delete')">Cancel</button>
                    <button class="rounded-full bg-rose-600 px-6 py-3 text-xs font-semibold uppercase tracking-[0.2em] text-white">Delete</button>
                </form>
            </div>
        </x-modal>
    </section>
</x-app-layout>
