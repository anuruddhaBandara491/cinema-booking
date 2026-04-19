<?php

namespace App\Http\Controllers;

use App\Models\SliderImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SliderImageController extends Controller
{
    public function index(): View
    {
        // $this->authorize('viewAny', SliderImage::class);
        $images = SliderImage::orderBy('order')->get();
        return view('slider.manage', compact('images'));
    }

    public function create(): View
    {
        // $this->authorize('create', SliderImage::class);
        return view('slider.create');
    }

    public function store(Request $request): RedirectResponse
    {
        // $this->authorize('create', SliderImage::class);
        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'trailer_url' => ['nullable', 'url'],
            'image' => ['required', 'image', 'max:2048'],
            'order' => ['nullable', 'integer'],
        ]);
        $data['image_path'] = $request->file('image')->store('slider', 'public');
        SliderImage::create($data);
        return redirect()->route('slider.manage')->with('status', 'Slider image added.');
    }

    public function destroy(SliderImage $sliderImage): RedirectResponse
    {
        // $this->authorize('delete', $sliderImage);
        if ($sliderImage->image_path) {
            Storage::disk('public')->delete($sliderImage->image_path);
        }
        $sliderImage->delete();
        return redirect()->route('slider.manage')->with('status', 'Slider image deleted.');
    }
}
