# Restaurant Order Queue API

A backend service for a restaurant ordering system featuring **kitchen capacity throttling**, **VIP prioritization**, **auto-completion worker**, **pickup time suggestions**, and a **full PHPUnit test suite**.

Built using **PHP 8**, **SQLite**, **Composer**, and a custom lightweight router.

---

##  Features

###  Core Requirements
- Create Order (`POST /orders`)
- List Active Orders (`GET /orders/active`)
- Complete Order (`POST /orders/{id}/complete`)
- Persistent storage using SQLite
- Kitchen capacity limit (default: 5 active orders)
- VIP orders bypass the limit

###  Bonus Features (Completed)
- Suggest next available pickup time when kitchen is full  
- VIP priority queue (VIP orders always float to the top)
- Background auto-completion worker (completes orders older than 10 minutes)
- Full PHPUnit test suite (5 tests)



---

##  Project Structure

```
restaurant-api/
├── bin/
│   ├── migrate.php
│   └── worker.php
│
├── public/
│   └── index.php
│
├── src/
│   ├── Controllers/
│   │   └── OrderController.php
│   ├── Services/
│   │   └── OrderService.php
│   └── Infrastructure/
│       ├── Database.php
│       └── Router.php
│
├── storage/
│   ├── orders.sqlite
│   └── orders_test.sqlite
│
├── tests/
│   └── OrderServiceTest.php
│
├── vendor/
│
├── composer.json
├── composer.lock
├── phpunit.xml
└── .gitignore
```

---

##  Requirements

- PHP 8.0+  
- Composer  
- SQLite  

---

##  Installation

Clone the repository:

```bash
git clone https://github.com/ganyklive-alt/restaurant-api.git
cd restaurant-api
```

Install dependencies:

```bash
composer install
```

Create database & tables:

```bash
php bin/migrate.php
```

---

## ▶ Running the API Locally

Start PHP built‑in development server:

```bash
php -S localhost:8000 -t public
```

API available at:  
 http://localhost:8000/orders/active

---

##  API Endpoints

---

### 1️ Create Order  
`POST /orders`

#### Request Body:
```json
{
  "items": ["burger", "fries"],
  "pickup_time": "2025-09-26T12:30:00Z",
  "VIP": true
}
```

#### Response:
- `201 Created` → order accepted  
- `429 Too Many Orders` → kitchen full  
- VIP bypasses limit  
- Next available pickup time suggested  

---

### 2️ List Active Orders  
`GET /orders/active`

Returns all active orders, VIP first.

---

### 3️ Complete Order  
`POST /orders/{id}/complete`

- `200 OK` → success  
- `404 Not Found` → invalid ID  

---

##  Background Worker

Automatically completes orders older than **10 minutes**.

Run:

```bash
php bin/worker.php
```

Example log:

```
 Checking active orders...
✔ Auto-completing order ID 3
```

---

##  Running Tests

```bash
vendor/bin/phpunit
```

Covers:
- Capacity enforcement  
- VIP priority  
- Pickup-time suggestion  
- Order completion  
- Test database isolation  

---

##  Technical Highlights

- Lightweight custom routing  
- Clean service/controller separation  
- SQLite persistent database  
- Auto-background order completion logic  
- VIP priority queue  
- PHPUnit testing workflow  

---

##  Author

**Gani K Live (ALT)**  
Backend Developer — PHP | REST APIs  

GitHub: https://github.com/ganyklive-alt
