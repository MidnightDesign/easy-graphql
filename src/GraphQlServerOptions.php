<?php

declare(strict_types=1);

namespace Midnight\EasyGraphQl;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

final class GraphQlServerOptions
{
    /** @var string */
    private $schemaFile;
    /** @var ResolversInterface */
    private $resolvers;
    /** @var ResponseFactoryInterface */
    private $responseFactory;
    /** @var StreamFactoryInterface */
    private $streamFactory;
    /** @var bool */
    private $debug;

    public function __construct(
        string $schemaFile,
        ResolversInterface $resolvers,
        ResponseFactoryInterface $responseFactory,
        StreamFactoryInterface $streamFactory
    ) {
        $this->schemaFile = $schemaFile;
        $this->resolvers = $resolvers;
        $this->responseFactory = $responseFactory;
        $this->streamFactory = $streamFactory;
    }

    public function schemaFile(): string
    {
        return $this->schemaFile;
    }

    public function resolvers(): ResolversInterface
    {
        return $this->resolvers;
    }

    public function responseFactory(): ResponseFactoryInterface
    {
        return $this->responseFactory;
    }

    public function streamFactory(): StreamFactoryInterface
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
