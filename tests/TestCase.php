<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Sanctum statefulApi() solo añade el middleware de sesión si la petición
     * viene de un dominio stateful (cabecera Referer/Origin del front).
     * Sin esto, login() falla con "Session store not set on request".
     * Se replica aquí lo que envía el SPA real.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->withHeader('Referer', config('app.frontend_url'));
    }
}
