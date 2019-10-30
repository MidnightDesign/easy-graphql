<?php

declare(strict_types=1);

namespace Midnight\EasyGraphQl\Test\Functional;

use Midnight\EasyGraphQl\EasyGraphQlServer;
use Midnight\EasyGraphQl\NamespacedResolvers;
use Midnight\EasyGraphQl\Test\Functional\Resolver\Hero\HeroName;
use Midnight\EasyGraphQl\Test\Functional\Resolver\Query\QueryHeroes;
use Midnight\EasyGraphQl\Test\Functional\Resolver\ResolverContainer;
use Midnight\EasyGraphQl\Test\TestHelper;
use PHPUnit\Framework\TestCase;

final class EasyGraphQlServerTest extends TestCase
{
    /** @var TestHelper */
    private $helper;

    public function testSimple(): void
    {
        $data = $this->helper->query('query GetHeroes { heroes { name } }');

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
        $server = new EasyGraphQlServer(__DIR__ . '/schema.graphql', $resolvers);

        $this->helper = new TestHelper($server);
    }
}
