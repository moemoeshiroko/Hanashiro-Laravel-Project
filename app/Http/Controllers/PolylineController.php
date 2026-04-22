<?php

namespace App\Http\Controllers;

use App\Models\Polyline;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PolylineController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'descriptions' => 'nullable|string',
            'geometry_polyline' => 'required|string',
        ]);

        DB::table('polylines')->insert([
            'name' => $request->name,
            'description' => $request->descriptions,
            'geom' => DB::raw("ST_GeomFromText('{$request->geometry_polyline}', 4326)"),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Polyline saved successfully');
    }

    public function index()
    {
        $polylines = DB::table('polylines')
            ->select('*', DB::raw('ST_AsGeoJSON(geom) as geojson'))
            ->get();

        $features = $polylines->map(function ($polyline) {
            return [
                'type' => 'Feature',
                'geometry' => json_decode($polyline->geojson),
                'properties' => [
                    'name' => $polyline->name,
                    'description' => $polyline->description,
                    'created_at' => $polyline->created_at,
                ],
            ];
        });

        return response()->json([
            'type' => 'FeatureCollection',
            'data' => $features,
        ]);
    }
}
