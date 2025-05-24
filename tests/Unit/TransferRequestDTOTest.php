<?php

namespace Tests\Unit;

use Tests\TestCase;
use Tests\Factories\TransferRequestDTOFactory;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TransferRequestDTOTest extends TestCase
{
    public function test_reference_is_auto_generated_if_missing()
    {
        $dto = TransferRequestDTOFactory::make([
            'reference' => null
        ]);

        $this->assertTrue(Str::isUuid($dto->reference));
    }

    public function test_date_defaults_to_now_if_missing()
    {
        $dto = TransferRequestDTOFactory::make([
            'date' => null
        ]);

        $this->assertNotNull($dto->date);
        $this->assertInstanceOf(Carbon::class, $dto->date);
    }

    public function test_get_formatted_date_returns_iso_format()
    {
        $customDate = Carbon::create(2025, 2, 25, 6, 33, 0)->startOfSecond();
        $dto = TransferRequestDTOFactory::make([
            'date' => $customDate
        ]);

        $this->assertEquals('2025-02-25 06:33:00+00:00', $dto->getFormattedDate());
    }
}
