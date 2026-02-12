<?php
declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\PublishTopicMessage;
use App\Repository\SubscriptionRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[AsMessageHandler]
final readonly class PublishTopicMessageHandler
{
    public function __construct(
        private SubscriptionRepository $subscriptionRepository,
        private MailerInterface $mailer,
    ) {
    }

    public function __invoke(PublishTopicMessage $message): void
    {
        $topic = $message->getTopic();
        $subject = $message->getSubject();
        $body = $message->getBody();

        $subscriptions = $this->subscriptionRepository->findByTopic($topic);

        foreach ($subscriptions as $subscription) {
            $user = $subscription->getUser();
            if (!$user || !$user->getEmail()) {
                continue;
            }

            $email = new Email()
                ->from('noreply@example.com')   
                ->to($user->getEmail())
                ->subject($subject)
                ->html($body);

            $this->mailer->send($email);
        }
    }
}
