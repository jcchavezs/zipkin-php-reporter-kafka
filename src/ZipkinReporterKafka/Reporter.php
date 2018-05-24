<?php

namespace ZipkinReporterKafka;

use Exception;
use Kafka\Producer;
use Kafka\ProducerConfig;
use Zipkin\Recording\Span;
use Zipkin\Reporter as ZipkinReporter;
use Zipkin\Reporters\Metrics;
use Zipkin\Reporters\NoopMetrics;

final class Reporter implements ZipkinReporter
{
    const TOPIC_NAME = 'zipkin';

    /**
     * @var Producer
     */
    private $producer;

    /**
     * @var Metrics
     */
    private $reportMetrics;

    public function __construct(Producer $producer = null, array $config = null, Metrics $reporterMetrics = null)
    {
        $config = $config ?: [
            'broker_list' => '127.0.0.1:9092',
        ];

        $brokerList = ProducerConfig::getInstance()->getMetadataBrokerList();
        $producerConfig = ProducerConfig::getInstance();
        $producerConfig->setMetadataBrokerList($config['broker_list']);

        try {
            $this->producer = $producer ?: new Producer();
        } catch (Exception $e) {
            $this->producer = new NoopProducer();
        }

        if (!empty($brokerList)) {
            // If there is no previous broker list there is no way to remove
            // the previous one
            ProducerConfig::getInstance()->setMetadataBrokerList($brokerList);
        }

        $this->reportMetrics = $reporterMetrics ?: new NoopMetrics();
    }

    /**
     * @param array|Span[] $spans
     * @return void
     */
    public function report(array $spans)
    {
        if (empty($spans)) {
            return;
        }

        $message = [
            'topic' => self::TOPIC_NAME,
            'value' => json_encode(array_map(
                function (Span $span) {
                        return $span->toArray();
                },
                $spans
            )),
        ];

        $this->reportMetrics->incrementSpans(count($spans));
        $this->reportMetrics->incrementMessages();

        try {
            if (!$this->producer->send([$message])) {
                $this->reportMetrics->incrementSpansDropped(count($spans));
            }
        } catch (Exception $e) {
            $this->reportMetrics->incrementSpansDropped(count($spans));
            $this->reportMetrics->incrementMessagesDropped($e);
        }
    }
}
