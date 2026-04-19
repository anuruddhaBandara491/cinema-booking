<?php

namespace App\Http\Controllers;

use App\Models\TicketType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TicketTypeController extends Controller
{
    public function index(Request $request): View
    {
        $this->ensureAdmin($request);

        $ticketTypes = TicketType::orderBy('name')->get();

        return view('ticket-types.manage', [
            'ticketTypes' => $ticketTypes,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureAdmin($request);

        $data = $this->validatedData($request);

        $data['has_child'] = ! empty($data['has_child']);
        if (! $data['has_child']) {
            $data['child_price'] = null;
        }

        TicketType::create($data);

        return redirect()->route('admin.ticket-types.index')
            ->with('status', 'Ticket type added.');
    }

    public function update(Request $request, TicketType $ticketType): RedirectResponse
    {
        $this->ensureAdmin($request);

        $data = $this->validatedData($request);

        $data['has_child'] = ! empty($data['has_child']);
        if (! $data['has_child']) {
            $data['child_price'] = null;
        }

        $ticketType->update($data);

        return redirect()->route('admin.ticket-types.index')
            ->with('status', 'Ticket type updated.');
    }

    public function destroy(Request $request, TicketType $ticketType): RedirectResponse
    {
        $this->ensureAdmin($request);

        $ticketType->delete();

        return redirect()->route('admin.ticket-types.index')
            ->with('status', 'Ticket type deleted.');
    }

    private function ensureAdmin(Request $request): void
    {
        abort_unless($request->user()?->hasRole('admin'), 403);
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'adult_price' => ['required', 'numeric', 'min:0'],
            'has_child' => ['nullable', 'boolean'],
            'child_price' => [
                Rule::requiredIf(fn () => (bool) $request->input('has_child')),
                'nullable',
                'numeric',
                'min:0',
            ],
        ]);
    }
}
