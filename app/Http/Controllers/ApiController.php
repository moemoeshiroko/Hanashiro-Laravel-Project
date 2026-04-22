<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiController extends Controller
{
    /**
     * Get all points as GeoJSON.
     */
    public function points()
    {
        $points = DB::table('points')
            ->select('*', DB::raw('ST_AsGeoJSON(geom) as geojson'))
            ->get();

        return response()->json([
            'type' => 'FeatureCollection',
            'data' => $this->toGeoJson($points),
        ]);
    }

    /**
     * Get all polylines as GeoJSON.
     */
    public function polylines()
    {
        $polylines = DB::table('polylines')
            ->select('*', DB::raw('ST_AsGeoJSON(geom) as geojson'))
            ->get();

        return response()->json([
            'type' => 'FeatureCollection',
            'data' => $this->toGeoJson($polylines),
        ]);
    }

    /**
     * Get all polygons as GeoJSON.
     */
    public function polygons()
    {
        $polygons = DB::table('polygons')
            ->select('*', DB::raw('ST_AsGeoJSON(geom) as geojson'))
            ->get();

        return response()->json([
            'type' => 'FeatureCollection',
            'data' => $this->toGeoJson($polygons),
        ]);
    }

    /**
     * Helper to format database results into GeoJSON features.
     */
    private function toGeoJson($items)
    {
        return $items->map(function ($item) {
            return [
                'type' => 'Feature',
                'geometry' => json_decode($item->geojson),
                'properties' => [
                    'name' => $item->name,
                    'description' => $item->description,
                    'created_at' => $item->created_at,
                ],
            ];
        });
    }
}
