<?php

namespace App\Http\Services\Transfer;

use SimpleXMLElement;
use App\Http\Services\Transfer\TransferRequestDTO;
use App\Enums\Currency;
class TransferXmlBuilder
{
    protected SimpleXMLElement $xml;

    public function __construct()
    {
        $this->xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><PaymentRequestMessage></PaymentRequestMessage>');
    }

    public static function fromDTO(TransferRequestDTO $dto): self
    {
        return (new self)
            ->withTransferInfo($dto->reference, $dto->getFormattedDate(), $dto->amount, $dto->currency)
            ->withSender($dto->senderAccount)
            ->withReceiver($dto->bankCode, $dto->receiverAccount, $dto->receiverName)
            ->withNotes($dto->notes)
            ->withPaymentType($dto->paymentType)
            ->withChargeDetails($dto->chargeDetails);
    }

    public function withTransferInfo(string $reference, string $date, float $amount, string $currency): self
    {
        $node = $this->xml->addChild('TransferInfo');
        $node->addChild('Reference', $reference);
        $node->addChild('Date', $date);
        $precision = Currency::from($currency)->precision(); // ISO 4217 supported
        $node->addChild('Amount', number_format($amount, $precision, '.', ''));
        $node->addChild('Currency', $currency);
        return $this;
    }

    public function withSender(string $accountNumber): self
    {
        $node = $this->xml->addChild('SenderInfo');
        $node->addChild('AccountNumber', $accountNumber);
        return $this;
    }

    public function withReceiver(string $bankCode, string $accountNumber, string $beneficiaryName): self
    {
        $node = $this->xml->addChild('ReceiverInfo');
        $node->addChild('BankCode', $bankCode);
        $node->addChild('AccountNumber', $accountNumber);
        $node->addChild('BeneficiaryName', $beneficiaryName);
        return $this;
    }

    public function withNotes(?array $notes): self
    {
        // The Notes tag must not be present if there are notes.
        if (!empty($notes)) {
            $notesNode = $this->xml->addChild('Notes');
            foreach ($notes as $note) {
                $notesNode->addChild('Note', htmlspecialchars($note));
            }
        }
        return $this;
    }

    public function withPaymentType(?string $type): self
    {
        // The PaymentType tag must only be present if its value is other than 99 
        if ($type !== null && $type !== '99') {
            $this->xml->addChild('PaymentType', $type);
        }
        return $this;
    }

    public function withChargeDetails(?string $charges): self
    {
        // The ChargeDetails tag must only be present if it's value is other than SHA 
        if ($charges !== null && strtoupper($charges) !== 'SHA') {
            $this->xml->addChild('ChargeDetails', $charges);
        }
        return $this;
    }

    public function build(): string
    {
        return $this->xml->asXML();
    }
}
