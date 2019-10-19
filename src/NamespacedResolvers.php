<?php

declare(strict_types=1);

namespace Midnight\EasyGraphQl;

use LogicException;
use Psr\Container\ContainerInterface;

use function class_exists;
use function sprintf;
use function ucfirst;

final class NamespacedResolvers implements ResolversInterface
{
    /** @var ContainerInterface */
    private $resolverContainer;
    /** @var string[] */
    private $namespaces;

    public function __construct(ContainerInterface $resolverContainer, string ...$namespaces)
    {
        $this->namespaces = $namespaces;
        $this->resolverContainer = $resolverContainer;
    }

    public function get(string $type, string $field): callable
    {
        foreach ($this->namespaces as $namespace) {
            $className = $namespace . $type . '\\' . $type . ucfirst($field);
            if (!class_exists($className)) {
                continue;
            }
            if (!$this->resolverContainer->has($className)) {
                continue;
            }
            return $this->resolverContainer->get($className);
        }
        throw new LogicException(sprintf('Could not find a resolver for %s.%s.', $type, $field));
    }
}
