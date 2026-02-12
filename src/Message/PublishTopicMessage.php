<?php
declare(strict_types=1);

namespace App\Message;

final class PublishTopicMessage
{
    public function __construct(
        private readonly string $topic,
        private readonly string $subject,
        private readonly string $body,
    ) {
    }

    public function getTopic(): string
    {
        return $this->topic;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getBody(): string
    {
        return $this->body;
    }
}
