<?php

namespace App\Http\Controllers\Frontend\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LocationController extends Controller
{
    public function index(): View {
        return view('admin.locations.list', [
            'locations' => Location::orderBy('id')->paginate(),
        ]);
    }

    public function create(Request $request): RedirectResponse {
        $this->authorize('create', Location::class);
        $validated = $request->validate([
                                            'name'           => ['required', 'string'],
                                            'address_street' => ['required', 'string'],
                                            'address_zip'    => ['required', 'string'],
                                            'address_city'   => ['required', 'string'],
                                            'latitude'       => ['required', 'numeric'],
                                            'longitude'      => ['required', 'numeric'],
                                        ]);

        $location = Location::create($validated);
        return redirect()->route('admin.locations')
                         ->with('alert-success', 'Location with ID ' . $location->id . ' created');
    }

    public function renderEdit(int $id): View {
        $location = Location::findOrFail($id);
        $this->authorize('update', $location);
        return view('admin.locations.edit', [
            'location' => $location,
        ]);
    }

    public function edit(int $id, Request $request) {
        $location = Location::findOrFail($id);
        $this->authorize('update', $location);

        $validated = $request->validate([
                                            'name'           => ['required', 'string'],
                                            'address_street' => ['required', 'string'],
                                            'address_zip'    => ['required', 'string'],
                                            'address_city'   => ['required', 'string'],
                                            'latitude'       => ['required', 'numeric'],
                                            'longitude'      => ['required', 'numeric'],
                                        ]);
        $location->update($validated);
        return redirect()->route('admin.locations')
                         ->with('alert-success', 'Location with ID ' . $location->id . ' updated');
    }

    public function delete(Request $request): RedirectResponse {
        $validated = $request->validate([
                                            'id' => ['required', 'numeric', 'exists:locations,id'],
                                        ]);
        $location  = Location::findOrFail($validated['id']);
        $this->authorize('delete', $location);
        $location->delete();
        return redirect()->route('admin.locations')
                         ->with('alert-success', 'Location with ID ' . $location->id . ' deleted');
    }
}
