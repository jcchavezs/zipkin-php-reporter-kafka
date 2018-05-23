<?php

namespace ZipkinReporterKafka;

class NoopProducer
{
    public function send()
    {
        return false;
    }
}
