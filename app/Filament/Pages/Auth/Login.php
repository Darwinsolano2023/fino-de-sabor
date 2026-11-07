<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Support\Components\Attributes\Unexposed;
use Illuminate\Contracts\Support\Htmlable;

class Login extends BaseLogin
{
    #[Unexposed]
    public function getHeading(): string | Htmlable | null
    {
        // Si se esta pidiendo el codigo de verificacion en dos pasos, se deja el texto original.
        return filled($this->userUndertakingMultiFactorAuthentication)
            ? parent::getHeading()
            : 'Bienvenido';
    }

    #[Unexposed]
    public function getSubheading(): string | Htmlable | null
    {
        return filled($this->userUndertakingMultiFactorAuthentication)
            ? parent::getSubheading()
            : 'Ingresa con tu cuenta para continuar.';
    }
}
