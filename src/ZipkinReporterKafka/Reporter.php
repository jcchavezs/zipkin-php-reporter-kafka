<?php

namespace ZipkinReporterKafka;

use Zipkin\Recording\Span as MutableSpan;
use Zipkin\Reporter as ZipkinReporter;
use Kafka\Producer;

final class Reporter implements ZipkinReporter
{
    const TOPIC_NAME = 'zipkin';

    /**
     * @var Producer
     */
    private $producer;

    public function __construct(Producer $producer = null)
    {
        $this->producer = $producer ?: new Producer();
    }

    /**
     * @param MutableSpan[] $spans
     * @return void
     */
    public function report(array $spans)
    {
        $this->producer->send(array_map(function (MutableSpan $span) {
            return [
                'topic' => self::TOPIC_NAME,
                'value' => $span->toArray(),
            ];
        }, $spans));
    }
}
