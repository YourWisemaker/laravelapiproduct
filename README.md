# Product Rental API

A scalable and optimized API that supports product rentals with regional pricing, built with Laravel 12.

## Overview

This API service allows users to:
- Retrieve product details including attributes and values
- Get rental periods (3, 6, 12 months) available for products
- Access regional pricing information for products
- Filter products by region and rental period

## Database Schema

The database schema consists of the following tables:

- **Products**: Stores product details (name, description, SKU)
- **Attributes**: Defines product attributes (color, size, etc.)
- **AttributeValues**: Stores values for each attribute
- **RentalPeriods**: Defines available rental durations (3, 6, 12 months)
- **Regions**: Stores region/country information (Singapore, Malaysia, etc.)
- **ProductPricing**: Links products with regions and rental periods, storing the price for each combination

## Model Relationships

The following Eloquent models and relationships are implemented:

- **Product**: Has many ProductPricing, Belongs to many AttributeValues
- **Attribute**: Has many AttributeValues
- **AttributeValue**: Belongs to Attribute, Belongs to many Products
- **RentalPeriod**: Has many ProductPricing
- **Region**: Has many ProductPricing
- **ProductPricing**: Belongs to Product, Region, and RentalPeriod

## API Documentation

This API is documented using Swagger/OpenAPI. After starting the application, you can access the interactive API documentation at:

```
http://localhost:8000/api/documentation
```

Note: You may need to regenerate the documentation if you make changes to the API:
```bash
php artisan l5-swagger:generate
```

The Swagger UI provides a comprehensive, interactive view of all API endpoints with the ability to test them directly from the browser.

## API Endpoints

```
# Product Endpoints
GET /api/v1/products - List all products (with pagination)
GET /api/v1/products?region_id=1 - Filter products by region
GET /api/v1/products?rental_period_id=1 - Filter products by rental period
GET /api/v1/products/{product_id} - Get a specific product's details
GET /api/v1/products/{product_id}/pricing - Get pricing for a specific product
GET /api/v1/products/{product_id}/pricing?region_id=1 - Filter product pricing by region
GET /api/v1/products/{product_id}/pricing?rental_period_id=1 - Filter product pricing by rental period

# Reference Data Endpoints
GET /api/v1/regions - Get all available regions
GET /api/v1/rental-periods - Get all available rental periods
```

## Setup Instructions

### Prerequisites

- PHP 8.1 or higher
- Composer
- MySQL or SQLite
- L5-Swagger (installed via Composer)

### Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/yourusername/laravelapiproduct.git
   cd laravelapiproduct
   ```

2. Install dependencies:
   ```bash
   composer install
   ```

3. Set up environment:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. Configure database in .env file:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=your_database_name
   DB_USERNAME=your_database_username
   DB_PASSWORD=your_database_password
   ```

   Or for SQLite:
   ```
   DB_CONNECTION=sqlite
   DB_DATABASE=/absolute/path/to/database.sqlite
   ```

5. Run migrations and seed the database:
   ```bash
   php artisan migrate:fresh --seed
   ```

6. Generate API documentation:
   ```bash
   php artisan l5-swagger:generate
   ```

7. Start the development server:
   ```bash
   php artisan serve
   ```

8. Access the API documentation at:
   ```
   http://localhost:8000/api/documentation
   ```

### Running Tests

The project includes comprehensive tests for all API endpoints. To run the tests:

```bash
php artisan test
```

For testing, the application uses SQLite in-memory database to ensure tests run quickly and do not affect your development database.

## Bonus Features

1. **API Filtering**: Products can be filtered by region and rental period
2. **Unit Tests**: Comprehensive test suite for API validation and functionality
3. **Pagination**: Implemented for product listings to efficiently handle large datasets
4. **Swagger API Documentation**: Interactive API documentation with the ability to test endpoints directly from the browser

## Sample Responses

### Product Listing

```json
{
  "current_page": 1,
  "data": [
    {
      "id": 1,
      "name": "Test Product",
      "description": "Test Description",
      "sku": "TEST-001",
      "is_active": true,
      "created_at": "2025-04-08T03:04:58.000000Z",
      "updated_at": "2025-04-08T03:04:58.000000Z",
      "attribute_values": [...],
      "pricing": [...]
    }
  ],
  "first_page_url": "http://localhost:8000/api/v1/products?page=1",
  "from": 1,
  "last_page": 1,
  "last_page_url": "http://localhost:8000/api/v1/products?page=1",
  "links": [...],
  "next_page_url": null,
  "path": "http://localhost:8000/api/v1/products",
  "per_page": 15,
  "prev_page_url": null,
  "to": 1,
  "total": 1
}
```

### Product Pricing

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "region": {
        "id": 1,
        "name": "North America",
        "code": "NA"
      },
      "rental_period": {
        "id": 1,
        "name": "Daily",
        "days": 1
      },
      "price": 50
    }
  ]
}
```


