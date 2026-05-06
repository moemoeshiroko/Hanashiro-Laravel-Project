<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MapController extends Controller
{
    public function index()
    {
        return view('map');
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
