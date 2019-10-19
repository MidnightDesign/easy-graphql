<?php

declare(strict_types=1);

namespace Midnight\EasyGraphQl\Test;

use LogicException;
use Midnight\EasyGraphQl\EasyGraphQlServer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Stream;

use function array_map;
use function assert;
use function implode;
use function json_decode;
use function json_encode;
use function sprintf;

use const JSON_THROW_ON_ERROR;

final class TestHelper
{
    /** @var EasyGraphQlServer */
    private $server;

    public function __construct(EasyGraphQlServer $server)
    {
        $this->server = $server;
    }

    /**
     * @return mixed[]
     */
    protected static function responseJson(ResponseInterface $response): array
    {
        return json_decode((string)$response->getBody(), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @param mixed[]|null $variables
     * @param mixed[]|null $headers
     * @return mixed[]
     */
    public function query(string $gql, ?array $variables = null, ?array $headers = null): array
    {
        $request = $this->buildRequest($gql, $variables ?? [], $headers);
        $response = $this->processRequest($request);
        $data = self::responseJson($response);
        if (isset($data['errors'])) {
            $messages = array_map(
                static function (array $error): string {
                    $message = $error['message'];
                    if (!isset($error['debugMessage'])) {
                        return $message;
                    }
                    return sprintf('%s (%s)', $message, $error['debugMessage']);
                },
                $data['errors']
            );
            throw new LogicException(implode(', ', $messages));
        }
        return $data['data'];
    }

    /**
     * @param mixed[] $variables
     * @param array<string, string>|null $headers
     */
    private function buildRequest(string $query, array $variables, ?array $headers = null): ServerRequestInterface
    {
        $requestBody = new Stream('php://memory', 'rw');
        $body = json_encode(
            [
                'operationName' => null,
                'query' => $query,
                'variables' => $variables,
            ],
            JSON_THROW_ON_ERROR
        );
        $requestBody->write($body);
        $request = (new ServerRequest())
            ->withParsedBody(
                [
                    'operationName' => null,
                    'query' => $query,
                    'variables' => $variables,
                ]
            )
            ->withMethod('POST')
            ->withHeader('Content-Type', 'application/json')
            ->withBody($requestBody);
        foreach ($headers ?? [] as $header => $value) {
            $request = $request->withAddedHeader($header, $value);
        }
        assert($request instanceof ServerRequestInterface);
        return $request;
    }

    private function processRequest(ServerRequestInterface $request): ResponseInterface
    {
        $response = $this->server->handleRequest($request);
        assert($response instanceof ResponseInterface);
        return $response;
    }
}
