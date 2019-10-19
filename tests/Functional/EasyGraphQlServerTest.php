<?php

declare(strict_types=1);

namespace Midnight\EasyGraphQl\Test\Functional;

use LogicException;
use Midnight\EasyGraphQl\EasyGraphQlServer;
use Midnight\EasyGraphQl\GraphQlServerOptions;
use Midnight\EasyGraphQl\NamespacedResolvers;
use Midnight\EasyGraphQl\Test\Functional\Resolver\Hero\HeroName;
use Midnight\EasyGraphQl\Test\Functional\Resolver\Query\QueryHeroes;
use Midnight\EasyGraphQl\Test\Functional\Resolver\ResolverContainer;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\ResponseFactory;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Stream;
use Zend\Diactoros\StreamFactory;

use function json_decode;
use function sprintf;

use const JSON_THROW_ON_ERROR;

final class EasyGraphQlServerTest extends TestCase
{
    /** @var EasyGraphQlServer */
    private $server;

    /**
     * @return mixed[]
     */
    protected static function responseJson(ResponseInterface $response): array
    {
        return json_decode((string)$response->getBody(), true, 512, JSON_THROW_ON_ERROR);
    }

    public function testSimple(): void
    {
        $data = $this->query('query GetHeroes { heroes { name } }');

        $expectedData = [
            'heroes' => [['name' => 'Luke Skywalker']],
        ];
        self::assertEquals($expectedData, $data);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $heroes = [['name' => 'Luke Skywalker']];
        $resolvers = new NamespacedResolvers(
            new ResolverContainer(
                [
                    QueryHeroes::class => new QueryHeroes($heroes),
                    HeroName::class => new HeroName(),
                ]
            ),
            'Midnight\EasyGraphQl\Test\Functional\Resolver\\'
        );
        $options = new GraphQlServerOptions(
            __DIR__ . '/schema.graphql',
            $resolvers,
            new ResponseFactory(),
            new StreamFactory()
        );
        $options = $options->withDebugEnabled();
        $this->server = new EasyGraphQlServer($options);
    }

    /**
     * @param mixed[]|null $variables
     * @param mixed[]|null $headers
     * @return mixed[]
     */
    protected function query(string $gql, ?array $variables = null, ?array $headers = null): array
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

    protected function processRequest(ServerRequestInterface $request): ResponseInterface
    {
        $response = $this->server->handleRequest($request);
        assert($response instanceof ResponseInterface);
        return $response;
    }

    /**
     * @param mixed[] $variables
     * @param array<string, string>|null $headers
     */
    protected function buildRequest(string $query, array $variables, ?array $headers = null): ServerRequestInterface
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
}
