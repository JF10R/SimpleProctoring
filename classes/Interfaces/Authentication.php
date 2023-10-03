<?php
namespace SimpleProctoring\Interfaces;

use SimpleProctoring\User;

interface AuthenticationInterface {
    public function authenticate(): bool;
    public function getUser(): ?User;
}
?>