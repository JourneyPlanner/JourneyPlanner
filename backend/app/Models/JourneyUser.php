<?php

namespace App\Models;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Database\Eloquent\BroadcastsEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class JourneyUser extends Pivot
{
    use HasFactory, BroadcastsEvents;

    public const JOURNEY_MEMBER_ROLE_ID = 0;
    public const JOURNEY_GUIDE_ROLE_ID = 1;
    public const TEMPLATE_CREATOR_ROLE_ID = 2;

    /**
     * The table associated with the model.
     */
    protected $table = "journey_user";

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ["role"];

    /**
     * Get the channels that model events should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel|\Illuminate\Database\Eloquent\Model>
     */
    public function broadcastOn(string $event): array
    {
        return [new PrivateChannel("App.Models.Journey.{$this->journey_id}")];
    }

    /**
     * Get the user that the journey user belongs to.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            "id" => $this->user_id,
            "username" => $this->user->username,
            "display_name" => $this->user->display_name,
            "role" => $this->role,
        ];
    }
}
