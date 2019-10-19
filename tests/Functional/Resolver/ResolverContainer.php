<?php

declare(strict_types=1);

namespace Midnight\EasyGraphQl\Test\Functional\Resolver;

use Psr\Container\ContainerInterface;

final class ResolverContainer implements ContainerInterface
{
    /** @var array<string, mixed> */
    private $map;

    /**
     * @param array<string, mixed> $map
     */
    public function __construct(array $map)
    {
        $this->map = $map;
    }

    /**
     * @param string $id
     * @return mixed
     */
    public function get($id)
    {
        return $this->map[$id];
    }

    /**
     * @param string $id
     */
    public function has($id): bool
    {
        return isset($this->map[$id]);
    }
}
