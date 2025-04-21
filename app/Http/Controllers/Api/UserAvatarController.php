<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Illuminate\Http\Request;

class UserAvatarController extends Controller
{
    public function store(Request $request, User $user)
    {
        $request->validate([
            'avatar' => 'required|file|mimes:jpeg,png,jpg|max:10000'
        ]);

        if (!extension_loaded('gd')) {
            return response()->json([
                'message' => 'GD Library extension is not available. Please contact your system administrator to enable it.',
                'error' => 'GD_NOT_AVAILABLE'
            ], 500);
        }

        try {
            $manager = new ImageManager(['driver' => 'gd']);

            $img = $manager->make($request->file('avatar'));

            if ($img->height() > $img->width()) {
                $img->resize(100, null, function ($constraint) {
                    $constraint->aspectRatio();
                });
            } else {
                $img->resize(null, 100, function ($constraint) {
                    $constraint->aspectRatio();
                });
            }

            $file = $request->file('avatar')->hashName();

            Storage::put('public/' . $file, $img->encode());

            $url = Storage::url($file);

            $user->avatar = $url;

            $user->save();

            return response()->json(['message' => 'Successfully uploaded', 'path' => $url]);
        } catch (\Exception $e) {
            \Log::error('Avatar upload failed: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to process the image. Please try again or contact support.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
