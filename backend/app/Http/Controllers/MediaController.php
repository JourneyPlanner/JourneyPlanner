<?php

namespace App\Http\Controllers;

use App\Models\Media;
use Illuminate\Http\Request;
use App\Models\Journey;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;

class MediaController extends Controller
{
    /**
     * Get the users of the journey.
     */
    public function index(Request $request, $journey): JsonResponse
    {
        $journey = Journey::findOrFail($journey);
        Gate::authorize("journeyMember", $journey);
        $media = $journey->media->map(function ($media) {
            return [
                "id" => $media->id,
                "user_id" => $media->user_id,
                "user_first_name" => $media->user->firstName,
                "user_last_name" => $media->user->lastName,
                "name" => $media->name,
                "link" => route("media.show", [$media->journey_id, $media->id]),
                "type" => file_exists($media->getMediaPath())
                    ? mime_content_type($media->getMediaPath())
                    : "unknown",
            ];
        });

        return response()->json($media);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $journey, $media)
    {
        $journey = Journey::findOrFail($journey);
        //Gate::authorize("journeyMember", $journey);
        if ($request->has("thumbnail")) {
            $media = Media::findOrFail($media);
            $thumbnailPath = $media->getThumbnailPath();
            if (file_exists($thumbnailPath)) {
                return response()->file($thumbnailPath);
            } else {
                return abort(404);
            }
        }

        $media = Media::findOrFail($media);
        $path = $media->getMediaPath();
        if (file_exists($path)) {
            return response()->file($media->getMediaPath());
        } else {
            return abort(404);
        }
    }
}
