<?php

namespace Alighorbani\Responder;

use Illuminate\Support\ServiceProvider;
use Alighorbani\Responder\Foundation\Responder;

class ResponderServiceProvider extends ServiceProvider
{
    public function register()
    {
        ResponderFacade::shouldProxyTo(Responder::class);
    }
}
