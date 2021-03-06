<?php

namespace ZipkinReporterKafka\Unit;

use Kafka\Producer;
use Prophecy\Argument;
use Zipkin\Propagation\TraceContext;
use Zipkin\Samplers\BinarySampler;
use Zipkin\TracingBuilder;
use ZipkinReporterKafka\Reporter;

final class ReporterTest extends \PHPUnit_Framework_TestCase
{
    const TRACE_ID = '0000000000abc123';
    const SPAN_ID = '0000000000abc123';
    const TOPIC = 'zipkin';

    public function testReportSuccess()
    {
        $producer = $this->prophesize(Producer::class);
        $producer->send(Argument::that(function ($messages) {
            $value = json_decode($messages[0]['value'], true);
            return $messages[0]['topic'] === self::TOPIC
                && $value[0]['traceId'] === self::TRACE_ID
                && $value[0]['parentId'] === self::SPAN_ID;
        }))->shouldBeCalled();

        $reporter = new Reporter($producer->reveal());

        $tracing = TracingBuilder::create()
            ->havingSampler(BinarySampler::createAsAlwaysSample())
            ->havingReporter($reporter)
            ->build();

        $context = TraceContext::create(self::TRACE_ID, self::SPAN_ID, null, true);

        $span = $tracing->getTracer()->nextSpan($context);
        $span->start();
        $span->finish();

        $tracing->getTracer()->flush();
    }
}
