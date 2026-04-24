<?php

namespace App\DTOs;

readonly class RegisterDto
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public string $role = 'customer' // default role for new users
    ) {}

    public static function fromRequest($request): self
    {
        return new self(
            name: $request->validated('name'),
            email: $request->validated('email'),
            password: $request->validated('password'),
            role: $request->validated('role') ?? 'customer' // default to 'customer' if role is not provided
        );
    }
}