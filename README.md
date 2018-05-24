# Zipkin PHP Reporter Kafka

[![Build Status](https://travis-ci.org/jcchavezs/zipkin-php-reporter-kafka.svg?branch=master)](https://travis-ci.org/jcchavezs/zipkin-php-reporter-kafka)
[![Latest Stable Version](https://poser.pugx.org/jcchavezs/zipkin-reporter-kafka/v/stable)](https://packagist.org/packages/jcchavezs/zipkin-reporter-kafka)
[![Total Downloads](https://poser.pugx.org/jcchavezs/zipkin-reporter-kafka/downloads)](https://packagist.org/packages/jcchavezs/zipkin-reporter-kafka)
[![License](https://poser.pugx.org/jcchavezs/zipkin-reporter-kafka/license)](https://packagist.org/packages/jcchavezs/zipkin-reporter-kafka)

Zipkin Reporter over Kafka transport.

This library uses `nmred/kafka-php` under version `0.2.0.8`. This library
is wide used but has some limitations in terms of configuration as it uses
a global singleton. In the other hand, this library does not require to install
any PHP extension which makes its usage very convenient.

## Install

```bash
composer require jcchavezs/zipkin-reporter-kafka
```

## Usage

```php
<?php

$config = [
    'broker_list' => 'kafkahost:9092',
];

$reporter = new ZipkinReporterKafka\Reporter(null, $config);

TracingBuilder::create()->havingReporter($reporter)->build();
```

