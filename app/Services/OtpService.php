<?php

namespace App\Services;

use App\Mail\OtpMail;
use App\Models\EmailOtp;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class OtpService
{
    public const RESET_PASSWORD = 'reset_password';

    public const VERIFY_EMAIL = 'verify_email';

    /** Minutos de validez del código. */
    public const TTL_MINUTES = 10;

    /** Intentos fallidos antes de que el código quede inservible. */
    public const MAX_ATTEMPTS = 3;

    /**
     * Genera un código nuevo y lo envía por correo.
     * Invalida cualquier código anterior del mismo propósito.
     */
    public function send(string $email, string $purpose): void
    {
        $this->clear($email, $purpose);

        $code = $this->generateCode();

        EmailOtp::create([
            'email' => $email,
            'purpose' => $purpose,
            'code_hash' => Hash::make($code),
            'attempts' => 0,
            'expires_at' => now()->addMinutes(self::TTL_MINUTES),
        ]);

        Mail::to($email)->send(new OtpMail(
            code: $code,
            purpose: $purpose,
            ttlMinutes: self::TTL_MINUTES,
            subjectLine: $purpose === self::VERIFY_EMAIL
                ? 'Confirma tu correo - Simulador SPGTH'
                : 'Restablece tu contraseña - Simulador SPGTH',
        ));
    }

    /** Comprueba el código. Si es correcto lo consume (un solo uso). */
    public function verify(string $email, string $purpose, string $code): bool
    {
        $otp = EmailOtp::where('email', $email)->where('purpose', $purpose)->first();

        if (! $otp || ! $otp->isUsable()) {
            return false;
        }

        if (! Hash::check($code, $otp->code_hash)) {
            $this->registerFailedAttempt($otp);

            return false;
        }

        $otp->update(['consumed_at' => now()]);

        return true;
    }

    public function clear(string $email, string $purpose): void
    {
        EmailOtp::where('email', $email)->where('purpose', $purpose)->delete();
    }

    /** random_int es criptográficamente seguro; el padding garantiza 6 dígitos. */
    private function generateCode(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    private function registerFailedAttempt(EmailOtp $otp): void
    {
        $attempts = $otp->attempts + 1;
        $otp->update(['attempts' => $attempts]);

        if ($attempts >= self::MAX_ATTEMPTS) {
            // Se agotaron los intentos: el código queda inservible aunque el tiempo siga vivo.
            $otp->update(['consumed_at' => now()]);
        }
    }
}
