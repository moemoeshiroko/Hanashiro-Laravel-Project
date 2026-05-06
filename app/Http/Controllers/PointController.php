<?php

namespace App\Http\Controllers;

use App\Models\Point;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class PointController extends Controller
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
            'geometry_point' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $name_image = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');

            // File validation checks
            if ($image->isValid()) {
                $targetDir = public_path('storage/images');
                
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }

                $extension = $image->getClientOriginalExtension() ?: $image->guessExtension();
                $name_image = time() . "_point." . strtolower($extension);
                
                try {
                    // Initialize ImageManager with GD driver
                    $manager = new ImageManager(new Driver());
                    
                    // Decode image from file system (Intervention Image v4 syntax)
                    $img = $manager->decode($image);
                    
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

        DB::table('points')->insert([
            'name' => $request->name,
            'description' => $request->descriptions,
            'image' => $name_image,
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
                    'id' => $point->id,
                    'name' => $point->name,
                    'description' => $point->description,
                    'image' => $point->image ? asset('storage/images/' . $point->image) : null,
                    'created_at' => $point->created_at,
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
        $point = Point::findOrFail($id);

        if ($point->image) {
            $imagePath = public_path('storage/images/' . $point->image);
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        $point->delete();

        return redirect()->back()->with('success', 'Point deleted successfully');
    }
}
