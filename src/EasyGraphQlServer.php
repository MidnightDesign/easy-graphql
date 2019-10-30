<?php

declare(strict_types=1);

namespace Midnight\EasyGraphQl;

use GraphQL\Executor\ExecutionResult;
use GraphQL\Executor\Promise\Promise;
use GraphQL\Server\Helper;
use GraphQL\Server\RequestError;
use GraphQL\Server\ServerConfig;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Utils\BuildSchema;
use Http\Discovery\Psr17FactoryDiscovery;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

use function file_get_contents;
use function fopen;

final class EasyGraphQlServer
{
    /** @var ResolversInterface */
    private $resolvers;
    /** @var ResponseFactoryInterface */
    private $responseFactory;
    /** @var StreamFactoryInterface */
    private $streamFactory;
    /** @var Helper */
    private $helper;
    /** @var ServerConfig */
    private $config;

    public function __construct(
        string $schemaFile,
        ResolversInterface $resolvers,
        ?GraphQlServerOptions $options = null
    ) {
        $options = $options ?? GraphQlServerOptions::create();
        $this->resolvers = $resolvers;
        $this->responseFactory = $options->responseFactory() ?? Psr17FactoryDiscovery::findResponseFactory();
        $this->streamFactory = $options->streamFactory() ?? Psr17FactoryDiscovery::findStreamFactory();
        $this->config = new ServerConfig();
        if ($options->isDebugEnabled()) {
            $this->config->setDebug(true);
        }
        $this->config->setSchema(BuildSchema::build(file_get_contents($schemaFile)));
        $this->config->setFieldResolver([$this, 'resolve']);
        $this->helper = new Helper();
    }

    /**
     * @throws RequestError
     */
    public function handleRequest(ServerRequestInterface $request): ResponseInterface
    {
        $response = $this->responseFactory->createResponse();
        $stream = $this->streamFactory->createStreamFromResource(fopen('php://memory', 'rwb'));
        $response = $response->withBody($stream);
        return $this->helper->toPsrResponse($this->executeRequest($request), $response, $stream);
    }

    /**
     * @param mixed $source
     * @param array<string, mixed> $args
     * @param mixed $context
     * @return mixed
     */
    public function resolve($source, array $args, $context, ResolveInfo $info)
    {
        $resolver = $this->resolvers->get($info->parentType->name, $info->fieldName);
        return $resolver($source, $args, $context, $info);
    }

    /**
     * @return ExecutionResult|ExecutionResult[]|Promise
     * @throws RequestError
     */
    private function executeRequest(ServerRequestInterface $request)
    {
        $operations = $this->helper->parsePsrRequest($request);
        return is_array($operations)
            ? $this->helper->executeBatch($this->config, $operations)
            : $this->helper->executeOperation($this->config, $operations);
    }
}
