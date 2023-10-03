<?php

declare(strict_types=1);
namespace SimpleProctoring;

use SimpleProctoring\Group;

class User {
    private readonly string $id;
    private readonly string $email;
    private readonly string $firstName;
    private readonly string $lastName;
    private ?array $groups = null;

    public function __construct(string $id, string $email, string $firstName, string $lastName, ?array $groups = null) {}

    public function getId(): string {
        return $this->id;
    }

    public function getEmail(): string {
        return $this->email;
    }

    public function getFirstName(): string {
        return $this->firstName;
    }

    public function getLastName(): string {
        return $this->lastName;
    }

    public function getFullName(): string {
        return $this->firstName . ' ' . $this->lastName;
    }

    public function getGroups(): array {
        $groups = [];

        if ($this->groups !== null) {
            foreach ($this->groups as $group) {
                if ($group instanceof Group) {
                    $groups[] = $group;
                }
            }
        }

        return $groups;
    }
}