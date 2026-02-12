<?php
declare(strict_types=1);

namespace App\Message;

final class SubscribeToTopicMessage
{
    public function __construct(
        private readonly int $userId,
        private readonly string $topic,
    ) {
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getTopic(): string
    {
        return $this->topic;
    }
}
