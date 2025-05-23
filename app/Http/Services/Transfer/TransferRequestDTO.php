<?php

namespace App\Http\Services\Transfer;

use Carbon\Carbon;
use Illuminate\Support\Str;
class TransferRequestDTO
{
    public string $reference;
    public Carbon|string $date;

    public function __construct(
        ?string $reference=null,
        Carbon|string $date='',
        public float $amount,
        public string $currency,
        public string $senderAccount,
        public string $receiverAccount,
        public string $receiverName,
        public string $bankCode,
        public ?array $notes = null,
        public ?string $paymentType = null,
        public ?string $chargeDetails = null,
    ) {
        $this->reference = $reference ?? (string) Str::uuid();
        $this->date = $date ?: now();
    }

    public function getFormattedDate(): string
    {
        return $this->date instanceof Carbon
            ? $this->date->format('Y-m-d H:i:sP')
            : (string) $this->date;
    }

    public function toArray(): array
    {
        return [
            'reference' => $this->reference,
            'date' => $this->getFormattedDate(),
            'amount' => $this->amount,
            'currency' => $this->currency,
            'sender_account' => $this->senderAccount,
            'receiver_account' => $this->receiverAccount,
            'receiver_name' => $this->receiverName,
            'bank_code' => $this->bankCode,
            'notes' => $this->notes,
            'payment_type' => $this->paymentType,
            'charge_details' => $this->chargeDetails,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['reference'] ?? null,
            $data['date'] ?? '',
            $data['amount'],
            $data['currency'],
            $data['sender_account'],
            $data['receiver_account'],
            $data['receiver_name'],
            $data['bank_code'],
            $data['notes'] ?? null,
            $data['payment_type'] ?? null,
            $data['charge_details'] ?? null,
        );
    }

}
