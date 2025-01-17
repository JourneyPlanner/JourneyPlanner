<?php

namespace App\Http\Controllers\Journey;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\Request;
use App\Models\Journey;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class MediaController extends Controller
{
    /**
     * Get all media of the specified journey.
     */
    public function index(Journey $journey): JsonResponse
    {
        Gate::authorize("view", [$journey, false]);

        $media = $journey->media->map(function ($media) {
            return [
                "id" => $media->id,
                "user_id" => $media->user_id,
                "user_display_name" => $media->user->display_name,
                "user_username" => $media->user->username,
                "name" => $media->name,
                "link" => url(
                    "/api/journey/{$media->journey_id}/media/{$media->id}",
                    [],
                    config("app.env") === "production"
                ),
                "type" => file_exists($media->getMediaPath())
                    ? mime_content_type($media->getMediaPath())
                    : "unknown",
            ];
        });

        return response()->json($media);
    }

    /**
     * Display the specified media.
     */
    //public function show(Request $request, Journey $journey, string $media)
    public function show(Request $request, Journey $journey, Media $media)
    {
        if ($request->has("thumbnail")) {
            $thumbnailPath = $media->getThumbnailPath();
            if (file_exists($thumbnailPath)) {
                return response()->file($thumbnailPath);
            } else {
                return abort(404);
            }
        }

        $path = $media->getMediaPath();
        if (file_exists($path)) {
            return response()->file($media->getMediaPath());
        } else {
            return abort(404);
        }
    }
}
