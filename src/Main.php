<?php

declare(strict_types=1);

namespace MichaelPetri\PubSubExmulatorDlxFailExample;

use Google\Cloud\PubSub\PubSubClient;

final class Main
{
    public static function run(): int
    {
        $client = new PubSubClient();

        $topic = $client->topic('dead-letter-topic');

        if ($topic->exists()) {
            self::log("Dead letter Topic found");
            $topic->delete();
            self::log("Dead letter Topic deleted");
        }

        $client->createTopic('dead-letter-topic');
        self::log("Dead letter Topic created");

        $topic = $client->topic('topic');

        if ($topic->exists()) {
            self::log("Topic found");
            $topic->delete();
            self::log("Topic deleted");
        }

        try {
            $client
                ->createTopic('topic')
                ->subscribe('subscription-with-dlx', [
                    'messageRetentionDuration' => '86400s',
                    'enableMessageOrdering' => true,
                    'enableExactlyOnceDelivery' => false,
                    'deadLetterPolicy' => [
                        'deadLetterTopic' => 'projects/pubsub-emulator/topics/dead-letter-topic',
                        'maxDeliveryAttempts' => 5,
                    ],
                ])
                ->create();
        } catch (\Throwable $e) {
            self::log("Failed to create subscription: " . $e->getMessage());
        }

        return 0;
    }

    private static function log(string $message): void
    {
        \printf(
            "[%s] %s",
            (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            $message
        ) . \PHP_EOL;
    }
}