<?php

namespace App\DTOs;

readonly class LoginDto
{
    public function __construct(
        public string $email,
        public string $password
    ) {}

    
    public static function fromRequest($request): self
    {
        return new self(
            email: $request->validated('email'),
            password: $request->validated('password')
        );
    }
}