<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Enums\Bank;

class BankTest extends TestCase
{

    public function test_bank_from_string_returns_enum(): void
    {
        $this->assertEquals(Bank::FOODICS, Bank::fromString('foodics'));
        $this->assertEquals(Bank::ACME, Bank::fromString('ACME'));
        $this->assertNull(Bank::fromString('unknown'));
    }
}
