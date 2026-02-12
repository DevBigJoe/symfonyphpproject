<?php
declare(strict_types=1);

namespace App\MessageHandler;

use App\Entity\Subscription;
use App\Message\SubscribeToTopicMessage;
use App\Repository\SubscriptionRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class SubscribeToTopicMessageHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private SubscriptionRepository $subscriptionRepository,
        private EntityManagerInterface $em,
    ) {
    }

    public function __invoke(SubscribeToTopicMessage $message): void
    {
        $user = $this->userRepository->find($message->getUserId());
        if (!$user) {
            return;
        }

        $topic = $message->getTopic();

        // Prüfen, ob Abo schon existiert (User + Topic)
        $existing = $this->subscriptionRepository->findOneBy([
            'user'  => $user,
            'topic' => $topic,
        ]);

        if ($existing) {
            // schon abonniert → nichts tun
            return;
        }

        $subscription = new Subscription()
            ->setUser($user)
            ->setTopic($topic);

        $this->em->persist($subscription);
        $this->em->flush();
    }
}
