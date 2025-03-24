<?php

namespace App\Http\Controllers\Journey;

use App\Http\Controllers\Controller;
use App\Models\Journey;
use App\Models\JourneyUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Knuckles\Scribe\Attributes\ResponseField;
use Knuckles\Scribe\Attributes\ResponseFromFile;
use Knuckles\Scribe\Attributes\UrlParam;

/**
 * @group Journey
 *
 * @subgroup JourneyUser
 *
 * @subgroupDescription APIs for managing journey users.
 */
class JourneyUserController extends Controller
{
    /**
     * Get users
     *
     * Get the users of the journey.
     */
    #[UrlParam(name: 'journey_id', type: 'uuid', description: 'The ID of the journey.', example: '9e0aec9c-7517-4941-8b3c-217ed978a7aa')]
    #[ResponseFromFile(status: 200, file: 'storage/responses/journey/user/index.200.json', description: 'Success.')]
    #[ResponseFromFile(status: 403, file: 'storage/responses/journey/user/index.403.json', description: 'Unauthorized.')]
    #[ResponseFromFile(status: 404, file: 'storage/responses/journey/user/index.404.json', description: 'Journey not found.')]
    #[ResponseField('message', type: 'string', description: 'The error message.')]
    // Reponse field description should be added if we change the response structure so that users isn't an array at the top because like this the description wouldn't get shown anyway
    public function index(Journey $journey): JsonResponse
    {
        // authorize the user
        Gate::authorize('view', [
            $journey,
            false,
            request()->input('share_id'),
        ]);

        // return the users of the journey in json format
        return response()->json(
            $journey->users()->get(['id', 'display_name', 'username', 'role'])
        );
    }

    /**
     * Join journey
     *
     * Join a journey with an invite code.
     */
    #[UrlParam(name: 'invite', type: 'uuid', description: 'The invite code of the journey.', example: '9e0aec9c-7517-4941-8b3c-217ed978a7aa')]
    #[ResponseFromFile(status: 200, file: 'storage/responses/journey/user/store.200.json', description: 'Success but already a member.')]
    #[ResponseFromFile(status: 201, file: 'storage/responses/journey/user/store.201.json', description: 'Success. Added user to the journey.')]
    #[ResponseFromFile(status: 401, file: 'storage/responses/journey/user/store.401.json', description: 'Unauthorized.')]
    #[ResponseFromFile(status: 404, file: 'storage/responses/journey/user/store.404.json', description: 'Journey not found.')]
    #[ResponseField('message', type: 'string', description: 'The error/success message.')]
    #[ResponseField('journey', type: 'uuid', description: 'The ID of the journey.')]
    public function store(Request $request, $invite): JsonResponse
    {
        $journey = Journey::where('invite', $invite)->firstOrFail();

        if ($request->user()->can('view', [$journey, false])) {
            return response()->json(
                [
                    'message' => 'You are already a member of this journey',
                    'journey' => $journey->id,
                ],
                200
            );
        }

        // Add the user to the journey
        if ($journey->is_guest) {
            $journey->users()->attach(Auth::id(), [
                'role' => JourneyUser::JOURNEY_GUIDE_ROLE_ID,
            ]);

            $journey->is_guest = false;
            $journey->save();
        } else {
            $journey->users()->attach(Auth::id(), [
                'role' => JourneyUser::JOURNEY_MEMBER_ROLE_ID,
            ]);
        }

        return response()->json(
            [
                'message' => 'You have successfully joined the journey',
                'journey' => $journey->id,
            ],
            201
        );
    }

    /**
     * Current user role
     *
     * Get the role of the current user in the journey.
     */
    #[UrlParam(name: 'journey_id', type: 'uuid', description: 'The ID of the journey.', example: '9e0aec9c-7517-4941-8b3c-217ed978a7aa')]
    #[ResponseFromFile(status: 200, file: 'storage/responses/journey/user/currentUserDetails.200.json', description: 'Success.')]
    #[ResponseFromFile(status: 403, file: 'storage/responses/journey/user/currentUserDetails.403.json', description: 'Unauthorized.')]
    #[ResponseFromFile(status: 404, file: 'storage/responses/journey/user/currentUserDetails.404.json', description: 'Journey not found.')]
    #[ResponseField('message', type: 'string', description: 'The error message.')]
    #[ResponseField('user_id', type: 'uuid', description: 'The ID of the user.')]
    #[ResponseField('role', type: 'integer', description: 'The role of the user in the journey. 0 for member, 1 for guide.')]
    public function currentUserDetails(Journey $journey): JsonResponse
    {
        $journeyUser = $journey
            ->users()
            ->where('user_id', Auth::id())
            ->firstOrFail(['user_id', 'role']);

        return response()->json($journeyUser);
    }

    /**
     * Update role
     *
     * Update the role of the specified user in the journey.
     */
    #[UrlParam(name: 'journey_id', type: 'uuid', description: 'The ID of the journey.', example: '9e0aec9c-7517-4941-8b3c-217ed978a7aa')]
    #[UrlParam(name: 'id', type: 'uuid', description: 'The ID of the user.', example: '9e0aec9c-7517-4941-8b3c-217ed978a7aa')]
    #[ResponseFromFile(status: 200, file: 'storage/responses/journey/user/update.200.json', description: 'Success.')]
    #[ResponseFromFile(status: 403, file: 'storage/responses/journey/user/update.403.json', description: 'Unauthorized.')]
    #[ResponseFromFile(status: 404, file: 'storage/responses/journey/user/update.404.json', description: 'Journey not found.')]
    #[ResponseFromFile(status: 422, file: 'storage/responses/journey/user/update.422.json', description: 'Validation error.')]
    #[ResponseField('message', type: 'string', description: 'The error/success message.')]
    #[ResponseField('errors', type: 'object', description: 'The validation errors.')]
    #[ResponseField('user', type: 'integer', description: 'Whether the update was actually successful or not.')]
    public function update(
        Request $request,
        Journey $journey,
        $user
    ): JsonResponse {
        Gate::authorize('update', [$journey, false]);

        if (Auth::id() == $user) {
            return response()->json(
                [
                    'message' => 'You cannot update your own role',
                ],
                403
            );
        }

        $validated = $request->validate([
            'role' => 'required|integer|numeric|between:0,1',
        ]);

        $journeyUser = $journey
            ->users()
            ->updateExistingPivot($user, ['role' => $validated['role']]);

        return response()->json(
            [
                'message' => 'User role updated successfully',
                'user' => $journeyUser,
            ],
            200
        );
    }

    /**
     * Leave journey
     *
     * Leave the journey.
     */
    #[UrlParam(name: 'journey_id', type: 'uuid', description: 'The ID of the journey.', example: '9e0aec9c-7517-4941-8b3c-217ed978a7aa')]
    #[ResponseFromFile(status: 200, file: 'storage/responses/journey/user/leave.200.json', description: 'Success.')]
    #[ResponseFromFile(status: 403, file: 'storage/responses/journey/user/leave.403.json', description: 'Unauthorized. Also returned if the user is the only guide and the journey still has other members.')]
    #[ResponseFromFile(status: 404, file: 'storage/responses/journey/user/leave.404.json', description: 'Journey not found.')]
    #[ResponseField('message', type: 'string', description: 'The error/success message.')]
    public function leave(Journey $journey)
    {
        if ($journey->is_guest) {
            JourneyController::deleteJourney($journey);

            return response()->json(
                [
                    'message' => 'Journey and journey user removed successfully',
                ],
                200
            );
        }

        // Prevent the user from leaving if they are the only guide
        if (
            $journey
                ->users()
                ->wherePivot('user_id', Auth::id())
                ->wherePivot('role', 1)
                ->exists() &&
            $journey
                ->users()
                ->wherePivot('role', JourneyUser::JOURNEY_GUIDE_ROLE_ID)
                ->count() === 1 &&
            $journey->users()->count() !== 1
        ) {
            return response()->json(
                [
                    'message' => 'You cannot leave the journey if you are the only guide',
                ],
                403
            );
        }

        $journey->users()->detach(Auth::id());

        // Remove the journey if the user was the last member
        if ($journey->users()->count() === 0) {
            JourneyController::deleteJourney($journey);

            return response()->json(
                [
                    'message' => 'Journey and journey user removed successfully',
                ],
                200
            );
        }

        return response()->json(
            [
                'message' => 'Journey user removed successfully',
            ],
            200
        );
    }

    /**
     * Kick user
     *
     * Remove (kick) a user from the journey.
     */
    #[UrlParam(name: 'journey_id', type: 'uuid', description: 'The ID of the journey.', example: '9e0aec9c-7517-4941-8b3c-217ed978a7aa')]
    #[UrlParam(name: 'user', type: 'uuid', description: 'The ID of the user.', example: '9e0aec9c-7517-4941-8b3c-217ed978a7aa')]
    #[ResponseFromFile(status: 200, file: 'storage/responses/journey/user/destroy.200.json', description: 'Success.')]
    #[ResponseFromFile(status: 403, file: 'storage/responses/journey/user/destroy.403.json', description: 'Unauthorized.')]
    #[ResponseFromFile(status: 404, file: 'storage/responses/journey/user/destroy.404.json', description: 'Journey not found.')]
    #[ResponseField('message', type: 'string', description: 'The error/success message.')]
    public function destroy(Journey $journey, string $user)
    {
        if (Auth::id() === $user) {
            return $this->leave($journey);
        }

        Gate::authorize('update', [$journey, false]);
        JourneyUser::where('journey_id', $journey->id)
            ->where('user_id', $user)
            ->delete();

        return response()->json(
            [
                'message' => 'Journey user removed successfully',
            ],
            200
        );
    }
}
