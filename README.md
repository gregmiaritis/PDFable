# PDFable

An API that turns HTML, a URL, or base64-encoded HTML into a PDF.

## Installation

Use the **production** setup: `docker-compose.prod.yml`. The dev setup is only for working on the code.

```bash
cp src/.env.production.example src/.env.production

# Generate a key and a token, and put them in src/.env.production as APP_KEY and PDF_API_TOKEN
docker compose -f docker-compose.prod.yml build
docker compose -f docker-compose.prod.yml run --rm --no-deps --entrypoint php app artisan key:generate --show
openssl rand -hex 32

docker compose -f docker-compose.prod.yml up -d
```

The API is now on `http://localhost` (port 80; set `APP_PORT` to change it). It serves plain HTTP, so put it behind a proxy that handles HTTPS.

After changing code or config, run `docker compose -f docker-compose.prod.yml up -d --build`.

## Usage

Every request needs:

- `Authorization: Bearer <PDF_API_TOKEN>`
- `Content-Type: application/json`

The PDF comes back in the response body.

| Endpoint | Body |
|---|---|
| `POST /api/pdf` | `{ "html": "<h1>Hello</h1>" }` |
| `POST /api/pdf/url` | `{ "url": "https://example.com" }` |
| `POST /api/pdf/base64` | `{ "base64": "PGgxPkhlbGxvPC9oMT4=" }` |
| `GET /api/health` | No token needed |

```bash
curl -X POST http://localhost/api/pdf \
  -H "Authorization: Bearer $PDF_API_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"html": "<h1>Hello</h1>", "settings": {"format": "A4"}}' \
  -o hello.pdf
```

### Settings (optional)

Add a `settings` object to any request:

| Key | Values | Default |
|---|---|---|
| `format` | `Letter`, `Legal`, `Tabloid`, `Ledger`, `A0`–`A6` | `A4` |
| `width`, `height` | Custom page size (use both, and don't combine with `format`) | – |
| `unit` | `mm`, `cm`, `in`, `px` | `mm` |
| `landscape` | `true` / `false` | `false` |
| `margins` | `{ "top": 10, "right": 10, "bottom": 10, "left": 10 }` | – |
| `scale` | `0.1`–`2` | `1` |
| `print_background` | `true` / `false` | `true` |
| `pages` | e.g. `"1-3, 5"` | all |

### Errors

| Status | Meaning |
|---|---|
| `401` | Missing or wrong token |
| `415` | Body isn't JSON |
| `422` | Invalid input. The `errors` field lists each problem. |

`/api/pdf/url` rejects URLs that point to internal addresses (localhost, private networks, cloud metadata). Keep your token secret.

## Development

```bash
docker compose up -d
docker compose exec app npm install
docker compose exec app php artisan test
```

The dev server runs on `http://localhost:8080` and uses `src/.env`.
