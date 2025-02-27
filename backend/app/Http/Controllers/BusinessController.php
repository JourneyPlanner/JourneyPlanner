<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Journey\TemplateController;
use App\Models\Business\Business;
use App\Models\Business\BusinessImage;
use App\Models\Business\BusinessImageAltText;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Business\BusinessText;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class BusinessController extends Controller
{
    /**
     * Get a specific business by its slug.
     */
    public function show(string $slug, Request $request): JsonResponse
    {
        $validated = $request->validate([
            "language" => "required|string|in:en,de",
        ]);
        $business = Business::where("slug", $slug)->firstOrFail([
            "id",
            "name",
            "default_language",
        ]);

        // Load images and texts for the specified language
        $business->load([
            "images" => function ($query) {
                $query->select("business_id", "key", "id");
            },
            "images.imageAltTexts" => function ($query) use ($validated) {
                $query
                    ->select("business_image_id", "alt_text")
                    ->where("language", $validated["language"]);
            },
            "texts" => function ($query) use ($validated) {
                $query
                    ->select("business_id", "key", "value")
                    ->where("language", $validated["language"]);
            },
        ]);

        // Load default language texts for all keys
        $defaultTexts = BusinessText::select("business_id", "key", "value")
            ->where("business_id", $business->id)
            ->where("language", $business->default_language)
            ->get();

        // Determine which keys from the default texts are missing in the translated texts
        $translatedKeys = $business->texts->pluck("key")->all();
        $missingDefaultTexts = $defaultTexts->filter(function ($text) use (
            $translatedKeys
        ) {
            return !in_array($text->key, $translatedKeys);
        });

        // Merge missing default texts to ensure all keys are present
        $business->texts = collect($business->texts)
            ->merge($missingDefaultTexts)
            ->all();

        // Load default language alt texts for all images that don't have a translated alt text
        foreach ($business->images as $image) {
            if ($image->imageAltTexts->isEmpty()) {
                $image->load([
                    "imageAltTexts" => function ($query) use ($business) {
                        $query
                            ->select("business_image_id", "alt_text")
                            ->where("language", $business->default_language);
                    },
                ]);
            }
        }

        // Prepare the response
        $businessToReturn = [];
        $businessToReturn["name"] = $business->name;

        $businessToReturn["images"] = [];
        foreach ($business->images as $image) {
            $imageToReturn = [];
            $imageToReturn["link"] = $image->getLink();
            $imageToReturn[
                "alt_text"
            ] = $image->imageAltTexts->first()->alt_text;
            $businessToReturn["images"][$image->key] = $imageToReturn;
        }

        $businessToReturn["texts"] = [];
        foreach ($business->texts as $text) {
            $businessToReturn["texts"][$text->key] = $text->value;
        }

        return response()->json($businessToReturn);
    }

    /**
     * Get a specific image of a business.
     */
    public function showImage(string $slug, BusinessImage $image)
    {
        $path = $image->getPath();
        if (file_exists($path)) {
            return response()->file($path);
        } else {
            return abort(404);
        }
    }

    /**
     * Get all templates of a business.
     */
    public function showTemplates(string $slug, Request $request): JsonResponse
    {
        $validated = $request->validate([
            "per_page" => "nullable|integer|min:1|max:100",
        ]);
        $templates = Business::where("slug", $slug)
            ->firstOrFail()
            ->templates()
            ->wherePivot("visible", true)
            ->with([
                "users" => function ($query) {
                    $query->select("id", "username", "display_name");
                },
            ])
            ->cursorPaginate(
                $validated["per_page"] ?? TemplateController::$perPage,
                TemplateController::getColumns()
            )
            ->withQueryString();

        return response()->json($templates);
    }

    /**
     * Get all activities of a business.
     */
    public function showActivities(string $slug, Request $request): JsonResponse
    {
        $validated = $request->validate([
            "per_page" => "nullable|integer|min:1|max:100",
        ]);
        $activities = Business::where("slug", $slug)
            ->firstOrFail()
            ->activities()
            ->cursorPaginate(
                $validated["per_page"] ?? TemplateController::$perPage
            )
            ->withQueryString();

        return response()->json($activities);
    }

    /**
     * Upload an image for a business.
     */
    public function uploadImage(string $slug, Request $request): JsonResponse
    {
        $validated = $request->validate([
            "image" => "nullable|image",
            "type" => "required|string|in:image,banner",
            "alt_texts" => "required|array",
            "alt_texts.*.language" => "required|string|in:en,de",
            "alt_texts.*.alt_text" => "required|string|max:255",
        ]);
        $business = Business::where("slug", $slug)->firstOrFail();

        // Verify user business membership
        Gate::authorize("update", $business);

        $businessImage = $business
            ->images()
            ->where("key", $validated["type"])
            ->first();
        $image = $request->file("image");

        // Save the image
        if ($image) {
            if ($businessImage) {
                Storage::delete($businessImage->getPath());
            }

            $fileName = $image->store("business_images/{$slug}");
            $businessImage = BusinessImage::updateOrCreate(
                ["business_id" => $business->id, "key" => $validated["type"]],
                ["path" => $fileName]
            );
        } elseif (!$businessImage) {
            return response()->json(["error" => "No image provided"], 400);
        }

        // Save alt texts
        foreach ($validated["alt_texts"] as $altText) {
            BusinessImageAltText::updateOrCreate(
                [
                    "business_image_id" => $businessImage->id,
                    "language" => $altText["language"],
                ],
                ["alt_text" => $altText["alt_text"]]
            );
        }

        // Prepare the response
        $businessImage->load("imageAltTexts");
        $response = [];
        $response["link"] = $businessImage->getLink();
        foreach ($businessImage->imageAltTexts as $altText) {
            $response["alt_texts"][$altText->language] = $altText->alt_text;
        }
        return response()->json($response, 201);
    }
}
