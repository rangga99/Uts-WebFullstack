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

## API Endpoints (v1)

All API routes are prefixed with `/api/v1` and protected with Laravel Sanctum where noted. Some routes require the user to have the `member` or `admin` role as indicated.

- Authentication
	- POST /api/v1/auth/login — Login (public) 
    example Json Request 
    {"email":"411253003@undira.ac.id","password":"password","device_name":"TestPC"}
	- POST /api/v1/auth/logout — Logout (auth)
	- GET  /api/v1/auth/me — Current user (auth)

- Rooms
	- GET  /api/v1/rooms — List rooms
	- GET  /api/v1/rooms/{room} — Room detail
	- GET  /api/v1/rooms/{room}/availability — Check room availability

- Equipment
	- GET  /api/v1/equipment — List equipment
	- GET  /api/v1/equipment/checkouts/my — My equipment checkouts
	- GET  /api/v1/equipment/{equipment} — Equipment detail
	- POST /api/v1/equipment/{equipment}/checkout — Checkout equipment (requires role: member,admin)
	- POST /api/v1/checkouts/{checkout}/return — Return equipment

- Bookings
	- POST /api/v1/bookings — Create booking (requires role: member,admin)
	- GET  /api/v1/bookings/my — Current user's bookings
	- GET  /api/v1/bookings/{booking} — Booking detail
	- POST /api/v1/bookings/{booking}/cancel — Cancel booking

- Admin (requires role: admin)
	- GET  /api/v1/admin/dashboard/stats — Dashboard statistics

	- Equipment (admin)
		- POST   /api/v1/admin/equipment — Create equipment
		- PUT    /api/v1/admin/equipment/{equipment} — Update equipment
		- DELETE /api/v1/admin/equipment/{equipment} — Delete equipment
		- GET    /api/v1/admin/equipment/checkouts — All equipment checkouts

	- Rooms (admin)
		- POST   /api/v1/admin/rooms — Create room
		- PUT    /api/v1/admin/rooms/{room} — Update room
		- DELETE /api/v1/admin/rooms/{room} — Delete room

	- Bookings (admin)
		- GET  /api/v1/admin/bookings — List bookings
		- PUT  /api/v1/admin/bookings/{booking}/confirm — Confirm booking
		- PUT  /api/v1/admin/bookings/{booking}/status — Update booking status

	- Members (admin)
		- GET   /api/v1/admin/members — List members
		- POST  /api/v1/admin/members — Create member
		- PATCH /api/v1/admin/members/{user}/toggle — Toggle member active state

