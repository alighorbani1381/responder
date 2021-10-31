<?php

namespace Alighorbani\Responder;

use Alighorbani\Responder\Foundation\Responder;

class ResponderServiceProvider
{
    public function register()
    {
        ResponderFacade::shouldProxyTo(Responder::class);
    }
}
