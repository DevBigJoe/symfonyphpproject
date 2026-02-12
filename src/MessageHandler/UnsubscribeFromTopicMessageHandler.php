<?php

declare(strict_types=1);

namespace App\MessageHandler;

use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use App\Message\UnsubscribeFromTopicMessage;

#[AsMessageHandler]
final class UnsubscribeFromTopicMessageHandler
{
    public function __invoke(UnsubscribeFromTopicMessage $message): void
    {
    }
}
