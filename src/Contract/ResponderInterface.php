<?php

namespace Alighorbani\Responder\Contract;

interface ResponderInterface
{
    public function resourceResponse($data, $title = null, $targetCollection = null, $responseMaker = null);

    public function successfulResponse($success, $data, $messageTitle, $statusCode);

    public function serverError($message);

    public function unauthenticError();

    public function validationError($message, $errorsBag);
}
