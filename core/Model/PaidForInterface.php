<?php

declare(strict_types=1);

namespace Answear\Payum\Model;

interface PaidForInterface
{
    public function getNumber(): string;

    public function getEmail(): string;

    public function getFirstName(): string;

    public function getSurname(): string;

    public function getPhone(): string;
}
