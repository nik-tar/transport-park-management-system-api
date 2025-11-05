# Transport Park Management System API

REST API for transport park management, including trucks, trailers, fleet sets (truck+trailer combinations). Service order management is not included yes.

## Tech Stack

- **PHP 8.4**
- **Symfony 7.3**
- **Doctrine ORM**
- **MariaDB 10.6.21**
- **Docker & Docker Compose**

## Requirements

- Docker 20.10+
- Docker Compose 2.0+

## Installation & Setup

### Option 1: Using Docker (Recommended)

1. **Clone the repository:**
```bash
git clone <repository-url>
cd transport-park-management-system-api
```

2. **Create .env file:**
```bash
cp .env.example .env
# Edit .env if needed with your custom values
```

2.1. **.env variables notes**

- You can specify custom local domain in `APP_DOMAIN`. For local domain to work you would need to update `hosts` file in your system.
Or you can configure reverse-proxy like Traefik and your dns-server.
- `WEB_ROOT` is the path which will be used as Symfony root directory. `/app` was used in this project. This directory is mounted to `php-fpm` and `nginx` containers to `/var/www/html`.

3. **Start containers:**
```bash
docker compose up -d
```

4. **Install dependencies:**
```bash
docker compose exec php composer install
```

5. **Run migrations:**
```bash
docker compose exec php bin/console doctrine:migrations:migrate --no-interaction
```

6. **API is available at:** `http://{APP_DOMAIN}:8080`

## API Documentation

### Base URL
- **Docker:** `http://{APP_DOMAIN}:8080/api`

### Endpoints

#### 1. Get Fleet List (Trucks, Trailers, Fleet Sets)

Returns list of all fleet units of one `type` with pagination and filtering.

```http
GET /api/fleets-list
```

**Query Parameters:**

| Parameter | Type    | Description                                    | Default |
|-----------|---------|------------------------------------------------|---------|
| `status`  | string  | Filter by status: `Works`, `Free`, `Downtime`  | null    |
| `type`    | string  | Filter by type: `Truck`, `Trailer`, `FleetSet` | `FleetSet`  |
| `page`    | integer | Page number (>= 1)                             | 1       |
| `limit`   | integer | Items per page (1-100)                         | 20      |

**Request Examples:**

```bash
# Get all "FleetSet"-type fleets (first page, 20 items). "FleetSet" type is default
curl http://{APP_DOMAIN}:8080/api/fleets-list

# Filter by "Works" status
curl http://{APP_DOMAIN}:8080/api/fleets-list?status=Works

# Filter by "Truck" type
curl http://{APP_DOMAIN}:8080/api/fleets-list?type=Truck

# Combined filters with pagination
curl "http://{APP_DOMAIN}:8080/api/fleets-list?status=Downtime&type=FleetSet&page=1&limit=10"
```

**Response (200 OK):**
```json
{
    "items": [
        {
            "id": "01K99HJH9FFJ4GSHWNAHVNKD9E",
            "type": "FleetSet",
            "status": "Downtime",
            "details": {
                "truck_id": "01K99HJH9FFJ4GSHWNAHVNKD8Z",
                "trailer_id": "01K99HJH9FFJ4GSHWNAHVNKD98",
                "drivers_count": 0,
                "is_in_service": true
            }
        },
        {
            "id": "01K99HJH9FFJ4GSHWNAHVNKD9F",
            "type": "FleetSet",
            "status": "Downtime",
            "details": {
                "truck_id": "01K99HJH9FFJ4GSHWNAHVNKD90",
                "trailer_id": "01K99HJH9FFJ4GSHWNAHVNKD99",
                "drivers_count": 2,
                "is_in_service": true
            }
        },
        {
            "id": "01K99HJH9FFJ4GSHWNAHVNKD9G",
            "type": "FleetSet",
            "status": "Downtime",
            "details": {
                "truck_id": "01K99HJH9FFJ4GSHWNAHVNKD91",
                "trailer_id": "01K99HJH9FFJ4GSHWNAHVNKD9A",
                "drivers_count": 0,
                "is_in_service": true
            }
        },
        {
            "id": "01K99HJH9FFJ4GSHWNAHVNKD9H",
            "type": "FleetSet",
            "status": "Downtime",
            "details": {
                "truck_id": "01K99HJH9FFJ4GSHWNAHVNKD92",
                "trailer_id": "01K99HJH9FFJ4GSHWNAHVNKD9B",
                "drivers_count": 2,
                "is_in_service": true
            }
        }
    ],
    "total": 4,
    "page": 1,
    "per_page": 20,
    "pages": 1
}
```

**Fleet Statuses:**
- **`Works`** - Working (for FleetSet: has drivers, vehicles operational)
- **`Free`** - Free (not in service, no drivers assigned)
- **`Downtime`** - Downtime (in service)

**Validation Errors (400 Bad Request):**
```json
{
  "error": "Invalid status. Allowed values: works, free, downtime"
}
```

---

## Data Structure

### Truck
- `id` (ULID) - Unique identifier
- `model` (string) - Model
- `brand` (string) - Brand
- `plate` (string) - Plate/registration number
- `in_service` (boolean) - Under maintenance
- `created_at` (datetime) - Creation date
- `updated_at` (datetime) - Update date

### Trailer
- `id` (ULID) - Unique identifier
- `plate` (string) - Plate/registration number
- `in_service` (boolean) - Under maintenance
- `created_at` (datetime) - Creation date
- `updated_at` (datetime) - Update date

### Fleet Set
- `id` (ULID) - Unique identifier
- `truck_id` (ULID) - Truck ID
- `trailer_id` (ULID) - Trailer ID
- `created_at` (datetime) - Creation date
- `updated_at` (datetime) - Update date

### Driver
- `id` (ULID) - Unique identifier
- `name` (string) - Driver name
- `created_at` (datetime) - Creation date
- `updated_at` (datetime) - Update date

**Business Rules:**
- Fleet set has `Works` status if vehicles are operational AND there is at least 1 driver
- Fleet set has `Free` status if vehicles are operational AND there are no drivers
- Fleet set has `Downtime` status if truck OR trailer is under maintenance
- Relation between `Driver` and `Fleet Set` is `Many-to-Many`

### Service Order
- `id` (ULID) - Unique identifier
- `status` (enum) - Order status
- `subject_type` (string) - The type of entity linked to the order (Truck/Trailer/Fleet Set)
- `subject_id` (ULID) - Linked entity ID
- `created_at` (datetime) - Creation date
- `updated_at` (datetime) - Update date

**Business Rules:**
- Only one entity (of Truck/Trailer/Fleet Set) can be linked to the order

---

## Testing

Not implemented.
