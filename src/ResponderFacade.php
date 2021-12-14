<?php

namespace Alighorbani\Responder;

use Imanghafoori\SmartFacades\Facade;

/**
 * @see \Alighorbani\Responder\Foundation\BaseResponder
 * @method static successfulResponse(bool $success, null|array $data, string $messageTitle, int $statusCode)
 * @method static resourceResponse($resourceItems, string $messageOrAlias, string $resourceClass)
 * @method static serverError(string $message, null|array $data)
 */
class ResponderFacade extends Facade
{
    // silence is golden :)
}
