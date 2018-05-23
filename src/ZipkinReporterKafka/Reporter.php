<?php

namespace ZipkinReporterKafka;

use Kafka\ProducerConfig;
use RuntimeException;
use Zipkin\Recording\Span as MutableSpan;
use Zipkin\Reporter as ZipkinReporter;
use Kafka\Producer;
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
     * @var
     */
    private $reportMetrics;

    public function __construct(Producer $producer = null, array $config = null, Metrics $reporterMetrics = null)
    {
        $config = $config ?: [
            'broker_list' => '127.0.0.1:9092',
        ];

        $brokerList = ProducerConfig::getInstance()->getMetadataBrokerList();

        $kConfig = ProducerConfig::getInstance();
        $kConfig->setMetadataBrokerList($config['broker_list']);

        $this->producer = $producer ?: new Producer();

        if (!empty($brokerList)) {
            ProducerConfig::getInstance()->setMetadataBrokerList($brokerList);
        }

        $this->reportMetrics = $reporterMetrics ?: new NoopMetrics();
    }

    /**
     * @param array|MutableSpan[] $spans
     * @return void
     */
    public function report(array $spans)
    {
        $messages = [];

        foreach ($spans as $span) {
            $messages[] = [
                'topic' => self::TOPIC_NAME,
                'value' => json_encode($span->toArray()),
            ];
        }

        $this->reportMetrics->incrementSpans(count($spans));
        $this->reportMetrics->incrementMessages();

        try {
            if (!$this->producer->send($messages)) {
                $this->reportMetrics->incrementSpansDropped(count($spans));
            }
        } catch (RuntimeException $e) {
            $this->reportMetrics->incrementSpansDropped(count($spans));
            $this->reportMetrics->incrementMessagesDropped($e);
        }
    }
}
