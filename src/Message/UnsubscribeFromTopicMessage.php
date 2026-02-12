<?php
declare(strict_types=1);

namespace App\Message;

final class UnsubscribeFromTopicMessage
{
    public function __construct(
        private int $userId,
        private string $topic
    ) {}

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getTopic(): string
    {
        return $this->topic;
    }
}
