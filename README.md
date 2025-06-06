# Foodics Pay - Webhook Ingestion & XML Transfer Generator
A Laravel-based system for processing webhook-based bank transactions and generating standardized XML files for money transfers.

## Content
- [Definitions/Facts](#definitions-header)
- [Assumptions](#assumptions-header)
- [Features](#features-header)
- [Architecture](#architecture-header)
- [Design Patterns](#design-patterns-header)
- [Tech Stack](#tech-stack-header)
- [Setup/Run](#setup-run-header)
- [Usage](#usage-header)

<h2 id="definitions-header">Definitions/Facts</h2>

* General:
    1. It is acceptable to over-engineer the solution.
* Sending Money:
    1. Focus on generating the XML only.
    2. Ignore communication with the bank or tracking the transfer in the database are out of scope.

* Receiving Money: 
    1. the app should parse the transaction line and import it into the database.
    2. attaching it to the client in the process.
    3. the bank will sometimes report the same transaction twice or more.
    4. this should have no effect on the final state of a client's transactions list.
    5. Also the app must be able to stop processing webhooks (ingestion) without dropping the incoming webhooks.
    6. Amount (two decimals).


<h2 id="assumptions-header">Assumptions</h2>

* Client ID:
    - we will refer to the client as account. to narrow the scope. as the client might have multiple bank accounts.
    - will assume that the banks will send the account_id in the webhook payload as the first attribute.
    - the bank sent account_id is the same as our account_id.
    - we will consider the account_id with the transaction reference_id as a unique role.

* Transactions:
    - based on sending money definitions 1, and 2 no need for the double entry implementation.
    - we will focus only on saving the received transactions only.

* Currency and Amounts:
    - Also will assume that the banks will send a valid currency in the payload.
    - the Currency will be sent Code String(SAR) not Number(682).
    - and we will store the amount as amount_cents for easier transformation and calculations
    - then we can use ISO 4217 to display the decimal points.

* Webhooks:
We have added bank_account_id and currency to the payload as they were needed in the current setup
Assuming that any bank can provide them by default.
    - Foodics Bank  => "SA6980000204608016212908#20250615156,50#SAR#202506159000001#note/debt payment march/internal_reference/A462JE81"
    - Acme Bank     => "SA6980000204608016212908//156,50//SAR//202506159000001//20250615"

<h2 id="features-header">Features</h2>

- Bank-specific webhook payload ingestion (e.g., Foodics, Acme)
- Pluggable parsers via `WebhookParserInterface`
- Data deduplication using Redis cache
- Queue-based ingestion via RabbitMQ
- Feature flag control to enable/disable ingestion dynamically
- XML generation using Builder pattern
- Automated job queuing and scheduled worker toggling
- Enum-based currency and bank support
- Clean test architecture with Faker-powered test factories


<h2 id="architecture-header">Architecture</h2>

Receiving Money
```
[Client] ──> [POST /api/v1/webhook/{bank}]
                    │
                    ▼
        ┌─────────────────────────────┐
        │ Controller stores message   │
        │ in RabbitMQ (persistent)    │
        └─────────────────────────────┘
                    │
                    ▼
            RabbitMQ (webhook_queue)
                    │
        [Workers with feature flag check]
                    ▼
    If enabled → Parse → Check Deduplicate 
    (Redis then DB) → Save Transaction 
    If disabled → Job remains queued
```

Webhooks (ingestion) toggle
```
CMD ──> toggle-webhook-ingestion
                │
                ▼
    Update Redis & Feature Flags Table
                │
                ▼
    If enabled -> Ingest on queue_worker
        If disabled -> Return

CRON ──> control-webhook-worker
                │
    Checks status each min from Redis
                ▼
    Start/Stop supervisor queue_worker
```

Sending Money
```
[Client] ──> [POST /api/v1/transfer]
                │
                ▼
    ┌─────────────────────────────┐
    │  Controller call transfer   │
    │          xml builder        │
    └─────────────────────────────┘
                │
                ▼
        RabbitMQ (transfer_xml_queue)
                │
                ▼
    Store generated XML to storage
            
```

<h2 id="design-patterns-header">Design Patterns</h2>

- Strategy Pattern for parser selection
- Enum Binding for bank & currency logic
- Builder Pattern for XML generation
- DTOs for type-safe transfer payloads
- Feature Flags for dynamic toggling
- Queueable Jobs for async ingestion

<h2 id="tech-stack-header">Tech Stack</h2>

- PHP 8.2+
- Laravel 10+
- Nginx
- PostgreSQL
- RabbitMQ
- Redis
- Docker & Docker Compose
- Supervisor


<h2 id="setup-run-header">Setup/Run</h2>

Clone the Repository
```bash
git clone https://github.com/elmarzouki/foodics-pay.git 
cd foodics-pay
```
Using docker-compose:
```bash
cp .env.example .env
docker-compose up -d --build
docker-compose exec app composer install
docker exec -it foodics-pay-app php artisan migrate --seed
chmod +x scripts/control-webhook-worker.sh
```
## Test
```bash
docker exec -it foodics-pay-app php artisan migrate:fresh --env=testing

docker exec -it foodics-pay-app php artisan test
```
## Monitor
```bash
# check supervisor status
docker exec -it foodics-pay-app supervisorctl -s http://localhost:9001 status

# logs
docker-compose logs -f
docker logs foodics-pay-nginx -f
docker logs foodics-pay-rabbitmq -f

docker exec -it foodics-pay-app tail -f storage/logs/laravel.log
docker exec -it foodics-pay-app tail -f storage/logs/scheduler.log
docker exec -it foodics-pay-app tail -f storage/logs/worker.log
docker exec -it foodics-pay-app tail -f storage/logs/control-worker.log

# generated XML
docker exec -it foodics-pay-app cat storage/app/private/transfers/{transfer-ref}.xml
```

<h2 id="usage-header">Usage</h2>

health-check
```bash
curl http://localhost:8000/api/v1/health_check
```

Webhooks (ingestion) toggle
```bash
docker exec -it foodics-pay-app php artisan app:toggle-webhook-ingestion enable/disable
docker exec -it foodics-pay-app php artisan app:check webhook_ingestion # custom check for the feature flags
docker exec -it foodics-pay-app php artisan schedule:list
```

Receiving Money
```http
POST /api/v1/webhook/{bank} 
Content-Type: text/plain
```

```bash
curl -X POST http://localhost:8000/api/v1/webhook/foodics \
    -H "Content-Type: text/plain" \
    --data "SA6980000204608016212908#20250615156,50#SAR#202506159000001#note/debt payment march/internal_reference/A462JE81"


curl -X POST http://localhost:8000/api/v1/webhook/acme \
    -H "Content-Type: text/plain" \
    --data "SA6980000204608016212908//156,50//SAR//202506159000001//20250615"
```

Sending Money
```http
POST /api/v1/transfer
Content-Type: application/json
```
```bash
# reference and date are optional
curl -X POST http://localhost:8000/api/v1/transfer \
  -H "Content-Type: application/json" \
  -d '{
    "reference": "7b6c2616-2244-4f27-88e6-c9ea76eafc97",
    "date": "2025-06-01T12:00:00+03:00",
    "amount": 177.39,
    "currency": "SAR",
    "sender_account": "SA6980000204608016212908",
    "receiver_account": "SA6980000204608016211111",
    "receiver_name": "Jane Doe",
    "bank_code": "FDCSSARI",
    "notes": ["debt payment", "March"],
    "payment_type": "421",
    "charge_details": "RB"
}'
```

