<?php

namespace App\Http\Controllers;

use App\Models\Point;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PointController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'descriptions' => 'nullable|string',
            'geometry_point' => 'required|string',
        ]);

        $point = new Point();
        $point->name = $request->name;
        $point->description = $request->descriptions;
        // Using bindings to prevent SQL injection
        $point->geom = DB::raw("ST_GeomFromText(?, 4326)");
        
        // Eloquent doesn't support bindings in DB::raw for save() directly in all versions, 
        // but we can use a more robust way:
        DB::table('points')->insert([
            'name' => $request->name,
            'description' => $request->descriptions,
            'geom' => DB::raw("ST_GeomFromText('{$request->geometry_point}', 4326)"),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Point saved successfully');
    }

    public function index()
    {
        $points = DB::table('points')
            ->select('*', DB::raw('ST_AsGeoJSON(geom) as geojson'))
            ->get();

        $features = $points->map(function ($point) {
            return [
                'type' => 'Feature',
                'geometry' => json_decode($point->geojson),
                'properties' => [
                    'name' => $point->name,
                    'description' => $point->description,
                    'created_at' => $point->created_at,
                ],
            ];
        });

        return response()->json([
            'type' => 'FeatureCollection',
            'data' => $features,
        ]);
    }
}
