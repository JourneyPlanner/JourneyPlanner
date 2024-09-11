<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Journey;
use App\Models\Media;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class UploadController extends Controller
{
    /**
     * Handle an upload request.
     */
    public function upload(Request $request): JsonResponse
    {
        if ($request->user()->currentAccessToken()->can("upload:media")) {
            if (array_key_exists("Type", $request->all())) {
                $type = $request->input("Type");
                if ($type == "pre-create") {
                    return $this->pre_create($request);
                } elseif ($type == "post-finish") {
                    return $this->post_finish($request);
                }
            }
        } else {
            return $this->rejectWithReason("Unauthorized", 401);
        }
        return $this->rejectWithReason("Invalid request", 400);
    }

    /**
     * Reject the upload request with a reason.
     *
     * @param string $reason The reason for rejecting the upload request.
     * @param int $statusCode The status code to return in the response.
     * @return \Illuminate\Http\JsonResponse The response to return to the client.
     */
    private function rejectWithReason($reason, $statusCode = 400): JsonResponse
    {
        return response()->json([
            "HTTPResponse" => [
                "StatusCode" => $statusCode,
                "Body" => json_encode(["message" => $reason]),
                "Header" => [
                    "Content-Type" => "application/json",
                ],
            ],
            "RejectUpload" => true,
            "StopUpload" => true,
        ]);
    }

    /**
     * Handle the pre_create event.
     */
    private function pre_create($request): JsonResponse
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            "Event.Upload.MetaData.journey" => "required",
            "Event.Upload.MetaData.filename" => "required",
            "Event.Upload.MetaData.filetype" => "required",
            "Event.Upload.Size" => "required",
        ]);

        if ($validator->fails()) {
            return $this->rejectWithReason($validator->errors()->first(), 400);
        }

        // Validate filetype, allow image/* and video/* as well as .pdf and .txt
        $allowedTypes = ["image", "video", "application/pdf", "text/plain"];
        $filetype = $request->all()["Event"]["Upload"]["MetaData"]["filetype"];
        $filetypeParts = explode("/", $filetype);
        if (
            !in_array($filetype, $allowedTypes) &&
            !in_array($filetypeParts[0], $allowedTypes)
        ) {
            return $this->rejectWithReason("Invalid file type", 415);
        }

        // Validate filesize
        $maxFileSize = 1024 * 1024 * 1024; // 1 GB
        $size = $request->all()["Event"]["Upload"]["Size"];
        if ($size > $maxFileSize) {
            return $this->rejectWithReason("Upload too big", 413);
        }

        // Check if the journey exists and the user is a member
        $journey = Journey::find(
            $request->all()["Event"]["Upload"]["MetaData"]["journey"]
        );
        if (!$journey) {
            return $this->rejectWithReason("Journey not found", 404);
        }
        if ($request->user()->cannot("journeyMember", $journey)) {
            return $this->rejectWithReason(
                "User is not a member of given journey",
                403
            );
        }

        // Allow the upload
        return response()->json([
            "HTTPResponse" => [
                "StatusCode" => 200,
                "Body" => json_encode(["message" => "OK"]),
                "Header" => [
                    "Content-Type" => "application/json",
                ],
            ],
            "RejectUpload" => false,
            "StopUpload" => false,
        ]);
    }

    /**
     * Handle the post_finish event.
     */
    private function post_finish($request): JsonResponse
    {
        // Get necessary data from the request
        $path = $request->all()["Event"]["Upload"]["Storage"]["Path"];
        $filename = $request->all()["Event"]["Upload"]["MetaData"]["filename"];
        $journeyId = $request->all()["Event"]["Upload"]["MetaData"]["journey"];

        // Create journey folder if it doesn't exist.
        $journeyFolder = Media::getJourneyFolder($journeyId);
        if (!file_exists($journeyFolder)) {
            mkdir($journeyFolder, 0750, true);
        }

        $filename = hrtime(true) . "_" . $filename;

        // Create media record in database.
        $media = new Media();
        $media->name = $filename;
        $media->journey_id = $journeyId;
        $media->user_id = $request->user()->id;
        $media->save();

        // Move file to journey folder.
        try {
            rename($path, $journeyFolder . "/" . $filename);
        } catch (\Exception $ignored) {
        }

        // Create thumbnail for video files.
        try {
            // Add thumbnail if it's a video.
            $filetype = mime_content_type($media->getMediaPath());
            if (strpos($filetype, "video") !== false) {
                $ffmpeg = FFMpeg::fromDisk("")->open($media->getMediaSubpath());
                $duration = $ffmpeg->getDurationInMiliseconds();
                $ffmpeg
                    ->getFrameFromSeconds($duration / 2000)
                    ->export()
                    ->toDisk("")
                    ->save($media->getThumbnailSubpath());
            }
        } catch (\Exception $ignored) {
        }

        // Remove .info file.
        try {
            unlink($path . ".info");
        } catch (\Exception $ignored) {
        }

        // Allow the upload (doesn't actually do anything, just for good measure)
        return response()->json([
            "HTTPResponse" => [
                "StatusCode" => 200,
                "Body" => json_encode(["message" => "OK"]),
                "Header" => [
                    "Content-Type" => "application/json",
                ],
            ],
            "RejectUpload" => false,
            "StopUpload" => false,
        ]);
    }
}
