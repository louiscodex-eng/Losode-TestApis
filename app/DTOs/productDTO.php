<?php

namespace App\DTOs;

readonly class ProductDTO
{
    public function __construct(
        public string $name,
        public string $description,
        public float $price,
        public int $stock_quantity,
        public string $status = 'active'
    ) {}
}