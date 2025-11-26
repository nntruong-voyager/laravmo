<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use RdKafka\Conf;
use RdKafka\KafkaConsumer;

class KafkaConsumeCommand extends Command
{
    protected $signature = 'eventbus:kafka-consume {topic?}';

    protected $description = 'Stream messages from Kafka topics defined in config/eventbus.php';

    public function handle(): int
    {
        $config = config('eventbus');
        $topics = $this->argument('topic')
            ? [$this->argument('topic')]
            : array_values($config['topics']);

        $kafkaBrokers = env('KAFKA_BROKERS', 'kafka:9092');
        $groupId = $config['consumer_group_id'];

        try {
            $conf = new Conf();
            $conf->set('bootstrap.servers', $kafkaBrokers);
            $conf->set('group.id', $groupId);
            $conf->set('auto.offset.reset', 'earliest');
            $conf->set('enable.auto.commit', 'true');

            $consumer = new KafkaConsumer($conf);
            $consumer->subscribe($topics);

            $this->info(sprintf('Kafka consumer connected to brokers: %s', $kafkaBrokers));
            $this->info(sprintf('Consumer group: %s', $groupId));
            $this->info('Listening on topics: ' . implode(', ', $topics));

            // Fetch messages in a loop
            while (true) {
                $message = $consumer->consume(120 * 1000); // 120 seconds timeout

                switch ($message->err) {
                    case RD_KAFKA_RESP_ERR_NO_ERROR:
                        $payload = $message->payload;
                        Log::info('Kafka message received', [
                            'topic' => $message->topic_name,
                            'partition' => $message->partition,
                            'offset' => $message->offset,
                            'payload' => $payload,
                        ]);
                        $this->line(sprintf(
                            '[%s:%s@%s] %s',
                            $message->topic_name,
                            $message->partition,
                            $message->offset,
                            $payload
                        ));
                        break;

                    case RD_KAFKA_RESP_ERR__PARTITION_EOF:
                        $this->comment('No more messages; will wait for more');
                        break;

                    case RD_KAFKA_RESP_ERR__TIMED_OUT:
                        $this->comment('Timed out');
                        break;

                    default:
                        throw new \Exception($message->errstr(), $message->err);
                }
            }
        } catch (\Exception $e) {
            $this->error('Error consuming messages: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
