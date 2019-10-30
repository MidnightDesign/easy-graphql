<?php

declare(strict_types=1);

namespace Midnight\EasyGraphQl;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

final class GraphQlServerOptions
{
    /** @var ResponseFactoryInterface|null */
    private $responseFactory;
    /** @var StreamFactoryInterface|null */
    private $streamFactory;
    /** @var bool */
    private $debug = false;

    public static function create(): self
    {
        return new self();
    }

    public function withResponseFactory(ResponseFactoryInterface $responseFactory): self
    {
        $clone = clone $this;
        $clone->responseFactory = $responseFactory;
        return $clone;
    }

    public function responseFactory(): ?ResponseFactoryInterface
    {
        return $this->responseFactory;
    }

    public function withStreamFactory(StreamFactoryInterface $streamFactory): self
    {
        $clone = clone $this;
        $clone->streamFactory = $streamFactory;
        return $clone;
    }

    public function streamFactory(): ?StreamFactoryInterface
    {
        return $this->streamFactory;
    }

    public function withDebugEnabled(): self
    {
        $clone = clone $this;
        $clone->debug = true;
        return $clone;
    }

    public function isDebugEnabled(): bool
    {
        return $this->debug;
    }
}
