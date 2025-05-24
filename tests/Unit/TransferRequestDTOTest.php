<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Services\Transfer\TransferRequestDTO;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TransferRequestDTOTest extends TestCase
{
    public function test_reference_is_auto_generated_if_missing()
    {
        $dto = TransferRequestDTO::fromArray([
            'amount' => 177.39,
            'currency' => 'SAR',
            'sender_account' => 'SA6980000204608016212908',
            'receiver_account' => 'SA6980000204608016211111',
            'receiver_name' => 'Jane Doe',
            'bank_code' => 'FDCSSARI'
        ]);

        $this->assertTrue(Str::isUuid($dto->reference));
    }

    public function test_date_defaults_to_now_if_missing()
    {
        $dto = TransferRequestDTO::fromArray([
            'amount' => 177.39,
            'currency' => 'SAR',
            'sender_account' => 'SA6980000204608016212908',
            'receiver_account' => 'SA6980000204608016211111',
            'receiver_name' => 'Jane Doe',
            'bank_code' => 'FDCSSARI'
        ]);

        $this->assertNotNull($dto->date);
    }

    public function test_get_formatted_date_returns_iso_format()
    {
        $dto = TransferRequestDTO::fromArray([
            'date' => Carbon::create(2025, 2, 25, 6, 33, 0),
            'amount' => 177.39,
            'currency' => 'SAR',
            'sender_account' => 'SA6980000204608016212908',
            'receiver_account' => 'SA6980000204608016211111',
            'receiver_name' => 'Jane Doe',
            'bank_code' => 'FDCSSARI'
        ]);

        $this->assertEquals('2025-02-25 06:33:00+00:00', $dto->getFormattedDate());
    }
}
