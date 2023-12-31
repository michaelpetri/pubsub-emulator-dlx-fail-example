<?php

declare(strict_types=1);

namespace MichaelPetri\PubSubExmulatorDlxFailExample;

use Google\Cloud\PubSub\PubSubClient;

final class Main
{
    public static function run(): int
    {
        $projectId = \getenv('GOOGLE_CLOUD_PROJECT');

        $client = new PubSubClient([
            'projectId' => $projectId,
        ]);

        self::log("Set project id to " . $projectId);

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

        $topic
            ->subscribe('subscription-with-dlx', [
                'messageRetentionDuration' => '86400s',
                'enableMessageOrdering' => true,
                'enableExactlyOnceDelivery' => false,
                'deadLetterPolicy' => [
                    'deadLetterTopic' => 'projects/emulator-project/topics/dead-letter-topic',
                    'maxDeliveryAttempts' => 5,
                ],
            ])
            ->create();

        self::log('Subscription with Dead Letter Topic created');

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