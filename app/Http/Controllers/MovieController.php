<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MovieController extends Controller
{
    public function index(Request $request): View
    {
        $movies = Movie::query()
            ->where(function ($query) {
                $query->where('is_published', true)
                    ->orWhere('is_upcoming', true);
            })
            ->orderBy('show_start_date')
            ->get();

        return view('movies.index', [
            'movies' => $movies,
        ]);
    }

    public function manage(Request $request): View
    {
        $this->ensureManager($request);

        $movies = Movie::latest()->get();

        return view('movies.manage', [
            'movies' => $movies,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureManager($request);

        $data = $this->validatedData($request);

        $data['show_times'] = $this->parseTimes($data['show_times']);
        $data['booking_window_days'] = $data['booking_window_days'] ?? 3;

        if ($request->hasFile('cover_image')) {
            $data['cover_image_path'] = $request->file('cover_image')->store('movies', 'public');
        }

        if (! empty($data['publish_immediately'])) {
            $data['is_published'] = true;
            $data['published_at'] = now();
        } else {
            $data['is_published'] = false;
            $data['published_at'] = null;
        }

        $data['is_upcoming'] = ! empty($data['is_upcoming']);

        if ($data['is_published']) {
            $data['is_upcoming'] = false;
        }

        if ($data['is_upcoming']) {
            $data['is_published'] = false;
            $data['published_at'] = null;
        }

        unset($data['publish_immediately']);

        Movie::create($data);

        return redirect()->route('manager.movies.index')->with('status', 'Movie added.');
    }

    public function update(Request $request, Movie $movie): RedirectResponse
    {
        $this->ensureManager($request);

        $data = $this->validatedData($request, $movie->id);

        $data['show_times'] = $this->parseTimes($data['show_times']);
        $data['booking_window_days'] = $data['booking_window_days'] ?? 3;

        if ($request->hasFile('cover_image')) {
            if ($movie->cover_image_path) {
                Storage::disk('public')->delete($movie->cover_image_path);
            }
            $data['cover_image_path'] = $request->file('cover_image')->store('movies', 'public');
        }

        if (! empty($data['publish_immediately'])) {
            $data['is_published'] = true;
            $data['published_at'] = $movie->published_at ?? now();
        } else {
            $data['is_published'] = false;
            $data['published_at'] = null;
        }

        $data['is_upcoming'] = ! empty($data['is_upcoming']);

        if ($data['is_published']) {
            $data['is_upcoming'] = false;
        }

        if ($data['is_upcoming']) {
            $data['is_published'] = false;
            $data['published_at'] = null;
        }

        unset($data['publish_immediately']);

        $movie->update($data);

        return redirect()->route('manager.movies.index')->with('status', 'Movie updated.');
    }

    public function destroy(Request $request, Movie $movie): RedirectResponse
    {
        $this->ensureManager($request);

        if ($movie->cover_image_path) {
            Storage::disk('public')->delete($movie->cover_image_path);
        }

        $movie->delete();

        return redirect()->route('manager.movies.index')->with('status', 'Movie deleted.');
    }

    private function ensureManager(Request $request): void
    {
        abort_unless($request->user()?->hasAnyRole(['manager', 'admin']), 403);
    }

    private function validatedData(Request $request, ?int $movieId = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'show_start_date' => ['required', 'date'],
            'show_end_date' => ['required', 'date', 'after_or_equal:show_start_date'],
            'show_times' => ['required', 'string'],
            'booking_window_days' => ['nullable', 'integer', 'min:1', 'max:14'],
            'cover_image' => ['nullable', 'image', 'max:2048'],
            'booking_charge' => ['required', 'numeric', 'min:0'],
            'publish_immediately' => ['nullable', 'boolean'],
            'is_upcoming' => ['nullable', 'boolean'],
            'status' => ['nullable', 'in:published,upcoming,draft'],
        ]);
    }

    private function parseTimes(string $times): array
    {
        return collect(explode(',', $times))
            ->map(fn ($time) => trim($time))
            ->filter()
            ->values()
            ->all();
    }

}
