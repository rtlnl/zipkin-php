<?php

namespace Zipkin;

use Zipkin\Recording\SpanMap;
use function Zipkin\Timestamp\is_valid_timestamp;

class Recorder
{
    /**
     * @var Endpoint
     */
    private $endpoint;

    /**
     * @var SpanMap
     */
    private $spanMap;

    /**
     * @var Reporter
     */
    private $reporter;

    /**
     * @var bool
     */
    private $noop;

    /**
     * Recorder constructor.
     * @param Endpoint $endpoint
     * @param Reporter $reporter
     * @param bool $isNoop
     */
    public function __construct(
        Endpoint $endpoint,
        Reporter $reporter,
        $isNoop
    ) {
        $this->endpoint = $endpoint;
        $this->reporter = $reporter;
        $this->noop = $isNoop;
        $this->spanMap = SpanMap::create();
    }

    public static function createAsNoop()
    {
        return new self(Endpoint::createAsEmpty(), null,true);
    }

    /**
     * @param TraceContext $context
     * @return float
     */
    public function getTimestamp(TraceContext $context)
    {
        $span = $this->spanMap->get($context);

        if ($span !== null && $span->getTimestamp() !==  null) {
            return $span->getTimestamp();
        }

        return null;
    }

    /**
     * @param TraceContext $context
     * @param float $timestamp
     */
    public function start(TraceContext $context, $timestamp)
    {
        $span = $this->spanMap->getOrCreate($context, $this->endpoint);
        $span->start($timestamp);
    }

    /**
     * @param TraceContext $context
     * @param string $name
     * @return void
     */
    public function setName(TraceContext $context, $name)
    {
        if ($this->noop) {
            return;
        }

        $span = $this->spanMap->getOrCreate($context, $this->endpoint);
        $span->setName($name);
    }

    /**
     * @param TraceContext $context
     * @param string $kind
     */
    public function setKind(TraceContext $context, $kind)
    {
        if ($this->noop) {
            return;
        }

        $span = $this->spanMap->getOrCreate($context, $this->endpoint);
        $span->setKind($kind);
    }

    /**
     * @param TraceContext $context
     * @param $timestamp
     * @param $value
     */
    public function annotate(TraceContext $context, $timestamp, $value)
    {
        if ($this->noop) {
            return;
        }

        $span = $this->spanMap->getOrCreate($context, $this->endpoint);
        $span->annotate($timestamp, $value);
    }

    public function tag(TraceContext $context, $key, $value)
    {
        if ($this->noop) {
            return;
        }

        $span = $this->spanMap->getOrCreate($context, $this->endpoint);
        $span->tag($key, $value);
    }

    public function setRemoteEndpoint(TraceContext $context, Endpoint $remoteEndpoint)
    {
        if ($this->noop) {
            return;
        }

        $span = $this->spanMap->getOrCreate($context, $this->endpoint);
        $span->setRemoteEndpoint($remoteEndpoint);
    }

    public function finish(TraceContext $context, $finishTimestamp)
    {
        $span = $this->spanMap->remove($context);

        if ($span !== null) {
            $span->finish($finishTimestamp);
        }
    }

    public function abandon(TraceContext $context)
    {
        $this->spanMap->remove($context);
    }

    public function flush(TraceContext $context)
    {
        $span = $this->spanMap->remove($context);

        if ($span !== null && !$this->noop) {
            $span->finish();

            $this->reporter->report([$span]);
        }
    }

    public function flushAll()
    {
        $this->reporter->report($this->spanMap->removeAll());
    }
}