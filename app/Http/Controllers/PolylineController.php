<?php

namespace App\Http\Controllers;

use App\Models\Polyline;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class PolylineController extends Controller
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
            'geometry_polyline' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
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
                $name_image = time() . "_polyline." . strtolower($extension);
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

        DB::table('polylines')->insert([
            'name' => $request->name,
            'description' => $request->descriptions,
            'image' => $name_image,
            'geom' => DB::raw("ST_GeomFromText('{$request->geometry_polyline}', 4326)"),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Polyline saved successfully');
    }

    public function update(Request $request, $id)
    {
        $polyline = Polyline::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'descriptions' => 'nullable|string',
            'geometry_polyline' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $image = $request->file('image');

            if ($image->isValid()) {
                $targetDir = public_path('storage/images');
                
                if ($polyline->image) {
                    $oldImagePath = $targetDir . '/' . $polyline->image;
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }

                $extension = $image->getClientOriginalExtension() ?: $image->guessExtension();
                $name_image = time() . "_polyline." . strtolower($extension);
                
                try {
                    $manager = new ImageManager(new Driver());
                    $img = $manager->decode($image);
                    if ($img->width() > 1200) {
                        $img->scale(width: 1200);
                    }
                    $img->save($targetDir . '/' . $name_image, quality: 75);
                    $polyline->image = $name_image;
                } catch (\Exception $e) {
                    return redirect()->back()->with('error', 'Failed to process image: ' . $e->getMessage());
                }
            }
        }

        if ($request->has('name')) {
            $polyline->name = $request->name;
        }
        if ($request->has('descriptions')) {
            $polyline->description = $request->descriptions;
        }
        
        if ($request->geometry_polyline) {
            $polyline->geom = DB::raw("ST_GeomFromText('{$request->geometry_polyline}', 4326)");
        }

        $polyline->save();

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Polyline updated successfully']);
        }

        return redirect()->route('map')->with('success', 'Polyline updated successfully');
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
                    'id' => $polyline->id,
                    'name' => $polyline->name,
                    'description' => $polyline->description,
                    'image' => $polyline->image ? asset('storage/images/' . $polyline->image) : null,
                    'created_at' => $polyline->created_at,
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
        $polyline = Polyline::findOrFail($id);

        if ($polyline->image) {
            $imagePath = public_path('storage/images/' . $polyline->image);
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        $polyline->delete();

        return redirect()->back()->with('success', 'Polyline deleted successfully');
    }
}
