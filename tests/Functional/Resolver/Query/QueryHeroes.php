<?php

declare(strict_types=1);

namespace Midnight\EasyGraphQl\Test\Functional\Resolver\Query;

final class QueryHeroes
{
    /** @var array<int, array<string, mixed>> */
    private $heroes;

    /**
     * @param array<int, array<string, mixed>> $heroes
     */
    public function __construct(array $heroes)
    {
        $this->heroes = $heroes;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function __invoke(): array
    {
        return $this->heroes;
    }
}
