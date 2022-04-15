<?php

namespace App\Response;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class JsonErrorResponse extends Response 
{

    public static function sendError(string $message, int $httpCode = Response::HTTP_NOT_FOUND)
    {
        $data = [
            'error' => true,
            'message' => $message,
        ];

        return new JsonResponse($data, $httpCode);
    }


    public static function sendValidatorErrors($errors)
    {
        $messages = [];

        foreach ($errors as $violation) {
            $messages[$violation->getPropertyPath()] = $violation->getMessage();
        }
        return new JsonResponse($messages);
    }

}