<?php

namespace App\Http\Controllers;

use App\Models\Polygon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class PolygonController extends Controller
{
    public function store(Request $request)
    {
        // Debugging: Catch errors before validation
        if ($request->has('image') && !$request->hasFile('image')) {
            $rawError = $_FILES['image']['error'] ?? 'Unknown raw error';
            $limit = ini_get('upload_max_filesize');
            $errorMsg = match ($rawError) {
                UPLOAD_ERR_INI_SIZE => "File exceeds PHP limit ($limit). Try restarting server with higher -d flag.",
                UPLOAD_ERR_FORM_SIZE => "File exceeds MAX_FILE_SIZE in HTML form",
                UPLOAD_ERR_PARTIAL => "File was only partially uploaded",
                UPLOAD_ERR_NO_FILE => "No file was uploaded",
                default => "Server rejected the file. Raw error code: $rawError"
            };
            return redirect()->back()->with('error', "Debug: $errorMsg");
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'descriptions' => 'nullable|string',
            'geometry_polygon' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:20480',
        ]);

        $name_image = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');

            if ($image->isValid()) {
                $targetDir = public_path('storage/images');
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }

                $extension = $image->getClientOriginalExtension() ?: $image->guessExtension();
                $name_image = time() . "_polygon." . strtolower($extension);
                try {
                    // Initialize ImageManager with GD driver
                    $manager = new ImageManager(new Driver());
                    
                    // Read image from file system
                    $img = $manager->read($image);
                    
                    // Scale down if image is wider than 1200px
                    if ($img->width() > 1200) {
                        $img->scale(width: 1200);
                    }
                    
                    // Save compressed image to target directory
                    $img->save($targetDir . '/' . $name_image, quality: 75);
                } catch (\Exception $e) {
                    return redirect()->back()->with('error', 'Failed to process and move uploaded file: ' . $e->getMessage());
                }
            } else {
                return redirect()->back()->with('error', 'The uploaded file is not valid.');
            }
        }

        DB::table('polygons')->insert([
            'name' => $request->name,
            'description' => $request->descriptions,
            'image' => $name_image,
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
                    'image' => $polygon->image ? asset('storage/images/' . $polygon->image) : null,
                    'created_at' => $polygon->created_at,
                ],
            ];
        });

        return response()->json([
            'type' => 'FeatureCollection',
            'data' => $features,
        ]);
    }

    public function destroy($id)
    {
        $polygon = DB::table('polygons')->where('id', $id)->first();

        if ($polygon) {
            if ($polygon->image) {
                $imagePath = public_path('storage/images/' . $polygon->image);
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }

            DB::table('polygons')->where('id', $id)->delete();
            return redirect()->back()->with('success', 'Polygon deleted successfully');
        }

        return redirect()->back()->with('error', 'Polygon not found');
    }
}
