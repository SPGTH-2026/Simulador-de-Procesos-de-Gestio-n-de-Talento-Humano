<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Entorno de desarrollo

### Requisitos

- PHP 8.3+
- Composer
- MySQL (este proyecto **no** usa SQLite)

### Instalación

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
```

### Bases de datos

Se usan **dos** bases separadas:

| Base | Uso |
|------|-----|
| `bd_spgth` | Desarrollo. La puebla `--seed` con las cuentas demo. |
| `bd_spgth_testing` | Pruebas. `phpunit.xml` apunta aquí. Nunca se toca `bd_spgth`. |

`bd_spgth_testing` **debe existir antes de correr las pruebas**. Si no existe,
la suite falla con `unknown database` o `SQLSTATE[HY000]`. Se crea una vez:

```sql
CREATE DATABASE bd_spgth_testing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Las pruebas usan MySQL, no SQLite, porque la extensión `pdo_sqlite` no está
disponible en el entorno de desarrollo. Las migraciones se ejecutan solas
sobre esa base en cada corrida.

### Pruebas

```bash
php artisan test
```

### Frontend y CORS

El frontend vive en otro repositorio y en desarrollo corre en
`http://localhost:5173`. El backend permite ese origen vía CORS con
credenciales, usando `FRONTEND_URL` (o `CORS_ALLOWED_ORIGINS` si necesitas
varios orígenes). El frontend debe usar siempre `localhost`, nunca mezclado
con `127.0.0.1`: las cookies distinguen host, no puerto.

### Login con Google

Requiere credenciales propias en `.env`:

```
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=http://localhost:8000/api/auth/google/callback
```

El `redirect_uri` debe coincidir **exactamente** con el registrado en Google
Cloud Console.

### Correo (códigos OTP)

Los códigos de verificación de correo y de recuperación de contraseña se envían
por email. En desarrollo se puede dejar `MAIL_MAILER=log`: el código se escribe
en `storage/logs/laravel.log` y **no** llega a ningún buzón. Para envío real con
Gmail:

1. Activar la verificación en dos pasos en la cuenta de Google.
2. Generar una **contraseña de aplicación** en
   `myaccount.google.com/apppasswords` (16 caracteres, sin espacios).
3. Configurar en `.env`:

```
MAIL_MAILER=smtp
MAIL_SCHEME=null
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu-cuenta@gmail.com
MAIL_PASSWORD=contraseña-de-aplicación-sin-espacios
MAIL_FROM_ADDRESS="tu-cuenta@gmail.com"
MAIL_FROM_NAME="Simulador SPGTH"
```

`MAIL_FROM_ADDRESS` debe ser el **mismo** correo autenticado, o Gmail rechaza el
envío. `MAIL_SCHEME=null` deja que Laravel use STARTTLS en el puerto 587; **no**
usar `tls` (Symfony solo acepta `smtp` o `smtps`).

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

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
