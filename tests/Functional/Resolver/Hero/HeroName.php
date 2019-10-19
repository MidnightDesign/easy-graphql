<?php

declare(strict_types=1);

namespace Midnight\EasyGraphQl\Test\Functional\Resolver\Hero;

final class HeroName
{
    /**
     * @param array<string, mixed> $hero
     */
    public function __invoke(array $hero): string
    {
        return $hero['name'];
    }
}
