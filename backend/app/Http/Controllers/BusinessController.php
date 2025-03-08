<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Journey\TemplateController;
use App\Models\Business\Business;
use App\Models\Business\BusinessImage;
use App\Models\Business\BusinessImageAltText;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Business\BusinessText;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class BusinessController extends Controller
{
    /**
     * Get a specific business by its slug.
     */
    public function show(Business $business, Request $request): JsonResponse
    {
        $validated = $request->validate([
            "language" => "required|string|in:en,de",
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
    public function showImage(Business $business, BusinessImage $image)
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
    public function showTemplates(
        Business $business,
        Request $request
    ): JsonResponse {
        $validated = $request->validate([
            "per_page" => "nullable|integer|min:1|max:100",
        ]);
        $templates = $business
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
    public function showActivities(
        Business $business,
        Request $request
    ): JsonResponse {
        $validated = $request->validate([
            "per_page" => "nullable|integer|min:1|max:100",
        ]);
        $activities = $business
            ->activities()
            ->cursorPaginate(
                $validated["per_page"] ?? TemplateController::$perPage
            )
            ->withQueryString();

        return response()->json($activities);
    }

    /**
     * Update the texts of a business.
     */
    public function updateTexts(Request $request, Business $business): Response
    {
        // Verify user business membership
        Gate::authorize("update", $business);

        // Validate the request
        $validated = $request->validate([
            "texts" => "required|array",
            "texts.*.language" => "in:de,en",
            "texts.*.texts" => "required|array",
            "texts.*.texts.*.key" =>
                "required|string|in:company_name,button,button_link,text",
            "texts.*.texts.*.value" => "required|string",
        ]);

        // Update the texts
        foreach ($validated["texts"] as $text) {
            foreach ($text["texts"] as $textValue) {
                BusinessText::updateOrCreate(
                    [
                        "business_id" => $business->id,
                        "key" => $textValue["key"],
                        "language" => $text["language"],
                    ],
                    ["value" => $textValue["value"]]
                );
            }
        }

        return response()->noContent();
    }

    /**
     * Update the templates of a business.
     */
    public function updateTemplates(
        Request $request,
        Business $business
    ): Response {
        // Verify user business membership
        Gate::authorize("update", $business);

        // Validate the request
        $validated = $request->validate([
            "templates" => "nullable|array",
            "templates.*" => "required|exists:journeys,id",
        ]);

        // Sometimes there is an issue with the request validation and the templates key is not set
        if (!isset($validated["templates"])) {
            $validated["templates"] = [];
        }

        // Update the templates
        $business->templates()->update(["visible" => false]);
        $business
            ->templates()
            ->whereIn("id", $validated["templates"])
            ->update(["visible" => true]);

        return response()->noContent();
    }

    /**
     * Upload an image for a business.
     */
    public function uploadImage(
        Business $business,
        Request $request
    ): JsonResponse {
        // Verify user business membership
        Gate::authorize("update", $business);

        $validated = $request->validate([
            "image" => "nullable|image",
            "type" => "required|string|in:image,banner",
            "alt_texts" => "required|array",
            "alt_texts.*.language" => "required|string|in:en,de",
            "alt_texts.*.alt_text" => "required|string|max:255",
        ]);

        $businessImage = $business
            ->images()
            ->where("key", $validated["type"])
            ->first();
        $image = $request->file("image");

        // Save the image
        if ($image) {
            if ($businessImage) {
                Storage::delete($businessImage->file_name);
            }

            $fileName = $image->store("business_images/{$business->slug}");
            $businessImage = BusinessImage::updateOrCreate(
                ["business_id" => $business->id, "key" => $validated["type"]],
                ["file_name" => $fileName]
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

    /**
     * Get the businesses of the authenticated user.
     */
    public function currentsUserIndex(Request $request): JsonResponse
    {
        $businesses = $request
            ->user()
            ->businesses()
            ->select("id", "name", "slug")
            ->get();

        return response()->json($businesses);
    }
}
