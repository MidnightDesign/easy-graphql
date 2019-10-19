<?php

declare(strict_types=1);

namespace Midnight\EasyGraphQl;

interface ResolversInterface
{
    public function get(string $type, string $field): callable;
}
