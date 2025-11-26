<?php

namespace App\Infrastructure\EventBus\Adapters;

use App\Infrastructure\EventBus\Contracts\RemoteEventAdapter;
use Illuminate\Support\Facades\Log;
use RdKafka\Conf;
use RdKafka\Producer;

class KafkaAdapter implements RemoteEventAdapter
{
    public function __construct(
        private readonly string $brokers,
        private readonly string $clientId,
    ) {
    }

    public function publish(string $topic, array $payload): void
    {
        $conf = new Conf();
        $conf->set('metadata.broker.list', $this->brokers);
        $conf->set('client.id', $this->clientId);

        $producer = new Producer($conf);

        $topic_handle = $producer->newTopic($topic);

        $message = json_encode($payload, JSON_THROW_ON_ERROR);
        $key = (string) ($payload['id'] ?? '');

        $topic_handle->produce(RD_KAFKA_PARTITION_UA, 0, $message, $key);

        $producer->poll(0);

        for ($flushRetries = 0; $flushRetries < 10; $flushRetries++) {
            $result = $producer->flush(10000);
            if (RD_KAFKA_RESP_ERR_NO_ERROR === $result) {
                Log::info('Kafka message published', ['topic' => $topic]);
                break;
            }
        }

        if (RD_KAFKA_RESP_ERR_NO_ERROR !== $result) {
            Log::error('Kafka publish failed', ['topic' => $topic, 'error_code' => $result]);
            throw new \RuntimeException('Failed to publish message to Kafka');
        }
    }
}

