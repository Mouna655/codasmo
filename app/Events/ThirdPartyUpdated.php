<?php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ThirdPartyUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public int $year, public array $payload) {}

    public function broadcastOn(): Channel
    {
        return new Channel("third-party.{$this->year}");
    }

    public function broadcastAs(): string { return 'ThirdPartyUpdated'; }

    public function broadcastWith(): array
    {
        return ['year' => $this->year, 'payload' => $this->payload, 'updated_at' => now()->toIso8601String()];
    }
}