<?php

namespace App\Http\Controllers;

use App\Models\Polygon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PolygonController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'descriptions' => 'nullable|string',
            'geometry_polygon' => 'required|string',
        ]);

        DB::table('polygons')->insert([
            'name' => $request->name,
            'description' => $request->descriptions,
            'geom' => DB::raw("ST_GeomFromText('{$request->geometry_polygon}', 4326)"),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Polygon saved successfully');
    }

    public function index()
    {
        $polygons = DB::table('polygons')
            ->select('*', DB::raw('ST_AsGeoJSON(geom) as geojson'))
            ->get();

        $features = $polygons->map(function ($polygon) {
            return [
                'type' => 'Feature',
                'geometry' => json_decode($polygon->geojson),
                'properties' => [
                    'name' => $polygon->name,
                    'description' => $polygon->description,
                    'created_at' => $polygon->created_at,
                ],
            ];
        });

        return response()->json([
            'type' => 'FeatureCollection',
            'data' => $features,
        ]);
    }
}
