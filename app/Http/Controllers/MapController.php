<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MapController extends Controller
{
    public function index($type = null, $id = null)
    {
        $route = request()->route();
        $resolvedId = $route ? $route->parameter('id') : $id;
        $resolvedType = $route ? ($route->parameter('type') ?? $route->defaults['type'] ?? $type) : $type;

        return view('map', [
            'focusType' => $resolvedType,
            'focusId' => $resolvedId
        ]);
    }

    public function table()
    {
        $points = \App\Models\Point::all();
        $polylines = \App\Models\Polyline::all();
        $polygons = \App\Models\Polygon::all();

        return view('table', compact('points', 'polylines', 'polygons'));
    }

    public function deleteAll()
    {
        // Delete records from database
        \Illuminate\Support\Facades\DB::table('points')->delete();
        \Illuminate\Support\Facades\DB::table('polylines')->delete();
        \Illuminate\Support\Facades\DB::table('polygons')->delete();

        // Delete all images in the storage directory
        $imagePath = public_path('storage/images');
        if (is_dir($imagePath)) {
            $files = glob($imagePath . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }

        return redirect()->back()->with('success', 'All data has been cleared successfully.');
    }
}
