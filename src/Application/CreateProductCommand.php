<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Application;

class CreateProductCommand implements Command
{
    public function __construct(
        public readonly string $name,
    ) {
    }
}
