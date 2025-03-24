<?php

namespace App\Http\Controllers\Journey;

use App\Http\Controllers\Controller;
use App\Models\Journey;
use App\Models\Media;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\ResponseField;
use Knuckles\Scribe\Attributes\ResponseFromFile;
use Knuckles\Scribe\Attributes\UrlParam;

/**
 * @group Journey
 *
 * @subgroup Media
 *
 * @subgroupDescription APIs for managing media of a journey.
 */
class MediaController extends Controller
{
    /**
     * Get media
     *
     * Get all media of the specified journey.
     */
    #[UrlParam(name: 'journey_id', type: 'uuid', description: 'The ID of the journey.', example: '9e0aec9c-7517-4941-8b3c-217ed978a7aa')]
    #[ResponseFromFile(status: 200, file: 'storage/responses/journey/media/index.200.json', description: 'Success.')]
    #[ResponseFromFile(status: 403, file: 'storage/responses/journey/media/index.403.json', description: 'Unauthorized.')]
    #[ResponseFromFile(status: 404, file: 'storage/responses/journey/media/index.404.json', description: 'Journey not found.')]
    #[ResponseField('message', type: 'string', description: 'The error message.')]
    // Reponse field description should be added if we change the response structure so that media isn't an array at the top because like this the description wouldn't get shown anyway
    public function index(Journey $journey): JsonResponse
    {
        Gate::authorize('view', [
            $journey,
            false,
            request()->input('share_id'),
        ]);

        $media = $journey->media->map(function ($media) {
            return [
                'id' => $media->id,
                'user_id' => $media->user_id,
                'user_display_name' => $media->user->display_name,
                'user_username' => $media->user->username,
                'name' => $media->name,
                'link' => url(
                    "/api/journey/{$media->journey_id}/media/{$media->id}",
                    [],
                    config('app.env') === 'production'
                ),
                'type' => file_exists($media->getMediaPath())
                    ? mime_content_type($media->getMediaPath())
                    : 'unknown',
            ];
        });

        return response()->json($media);
    }

    /**
     * Show media
     *
     * Display the specified media.
     */
    #[UrlParam(name: 'journey_id', type: 'uuid', description: 'The ID of the journey.', example: '9e0aec9c-7517-4941-8b3c-217ed978a7aa')]
    #[UrlParam(name: 'media_id', type: 'uuid', description: 'The ID of the media.', example: '9e0aec9c-7517-4941-8b3c-217ed978a7aa')]
    #[Response(status: 200, description: 'Success. Returns the media file.')]
    #[ResponseFromFile(status: 403, file: 'storage/responses/journey/media/show.403.json', description: 'Unauthorized.')]
    #[ResponseFromFile(status: 404, file: 'storage/responses/journey/media/show.404.json', description: 'Journey or media not found.')]
    #[ResponseField('message', type: 'string', description: 'The error message.')]
    public function show(Request $request, Journey $journey, Media $media)
    {
        if ($request->has('thumbnail')) {
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
