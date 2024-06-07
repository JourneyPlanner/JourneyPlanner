<?php

namespace App\Http\Controllers;

use App\Models\Media;
use Illuminate\Http\Request;
use App\Models\Journey;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\File;

class MediaController extends Controller
{
    /**
     * Get the users of the journey.
     */
    public function index(Request $request, $journey): JsonResponse
    {
        $journey = Journey::findOrFail($journey);
        //Gate::authorize("journeyMember", $journey);
        $media = $journey->media->map(function ($media) {
            return [
                'id' => $media->id,
                'user_id' => $media->user_id,
                'user_first_name' => $media->user->firstName,
                'user_last_name' => $media->user->lastName,
                'link' => route('media.show', [$media->journey_id, $media->id]),
                'type' => mime_content_type(storage_path($media->path)),
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
        $media = Media::findOrFail($media);

        return response()->file(storage_path($media->path));
    }

    public function download(Request $request, $journey)
    {
        $journey = Journey::findOrFail($journey);
        //Gate::authorize("journeyMember", $journey);

        $mediaFolder = storage_path("app/journey_media/{$journey->id}");

        // Create a zip file
        $zip = new \ZipArchive();

        $zipFileName = storage_path("/app/journey_media/JourneyPlanner_{$journey->name}_media.zip");
        $zip->open($zipFileName, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        $files = File::files($mediaFolder);

        foreach ($files as $file) {
            $zip->addFile($file, basename($file));
        }

        $zip->close();

        return response()->download($zipFileName)->deleteFileAfterSend(true);
    }
}