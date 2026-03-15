# Mobile Shop POS Backend API

Backend API for a **Mobile Shop Point of Sale (POS) System** designed to manage inventory, sales, stock movements, and financial reports for mobile phone and accessories shops.

This backend is built using **Laravel** and provides a scalable **multi-tenant architecture** that supports multiple shops while keeping each shop’s data isolated.

---

# Project Overview

Mobile phone accessory shops manage a large variety of products such as:

* Mobile covers (brand + model specific)
* Chargers
* Cables
* Earphones
* Other accessories

Managing inventory manually becomes difficult due to:

* Many brands
* Multiple phone models
* Different buying and selling prices
* Frequent stock changes

This backend provides a structured system to manage all shop operations through a secure REST API.

---

# Key Features

### Multi-Shop Support (Multi-Tenant Architecture)

The system supports **multiple shops using the same platform**.

Each shop’s data is isolated using a `shop_id` field across all business tables.

Features include:

* Secure shop-level data isolation
* Independent inventory for each shop
* Independent sales reports
* Independent expense tracking

---

### Inventory Management

The API manages inventory using the hierarchy:

Category → Brand → Model → Product

Example structure:

Cover → Samsung → S21 → Silicon Cover

Features:

* Create and manage categories
* Manage mobile brands
* Manage phone models
* Add products with cost and selling prices
* Track product quantities
* Stock movement tracking
* Low stock monitoring

---

### Sales Management

The system allows efficient sales processing.

Features include:

* Create new sales
* Multiple products per sale
* Automatic profit calculation
* Payment methods support:

  * Cash
  * Card
  * QR payment
* Automatic stock deduction after sale

---

### Financial Tracking

The system automatically calculates important financial metrics:

* Total sales revenue
* Product-level profit
* Daily profit
* Monthly profit
* Expense tracking
* Net business profit

Profit calculation formula:

Profit = Selling Price − Cost Price

---

### Expense Management

Shop owners can track operational expenses.

Supported expense types:

* Rent
* Electricity
* Salary
* Internet
* Other expenses

Expenses are used to calculate **net profit**.

---

### Stock Movement Tracking

All stock changes are recorded in a **stock history table**.

Stock changes occur when:

* New inventory is added
* Products are sold
* Items are returned
* Manual adjustments are made

This provides full inventory traceability.

---

# Technology Stack

Backend Framework:

* Laravel

Database:

* MySQL

API Type:

* RESTful API

Authentication:

* Laravel authentication system

---

# Database Structure

Main tables used in the system:

* shops
* users
* categories
* brands
* models
* products
* stock
* sales
* sale_items
* expenses

Each table includes a **shop_id** column to ensure shop-level data isolation.

---

# API Modules

The backend API is organized into the following modules:

Authentication

* User login
* User roles (Owner / Cashier)

Inventory

* Categories
* Brands
* Models
* Products
* Stock management

Sales

* Create sales
* Manage sale items
* Calculate profit

Reports

* Sales reports
* Profit reports
* Stock reports

Expenses

* Expense creation
* Expense listing
* Expense reports

---

# User Roles

### Owner

Full system access:

* Inventory management
* Sales reports
* Profit analytics
* Expense tracking
* User management

### Cashier

Limited access:

* Create sales
* View product inventory
* Cannot view cost prices
* Cannot manage expenses

---

# Future Improvements

Planned enhancements include:

* Barcode scanning support
* Receipt printing
* Supplier management
* Advanced analytics dashboards
* Offline-first sync support
* Cloud backup system

---

# Author

Inas Nuzeer

Software Engineering Graduate
Mobile & Web Application Developer

---

# Note

This repository represents the **backend architecture of the POS system**.

Some production details may be omitted for security or client confidentiality.


<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
