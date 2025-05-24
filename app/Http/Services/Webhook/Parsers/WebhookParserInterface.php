<?php

namespace App\Http\Services\Webhook\Parsers;

interface WebhookParserInterface
{
    public function parse(string $raw): array;

}
