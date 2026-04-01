<?php

namespace App\Services;

use App\DTO\Toast;
use Exception;
use Illuminate\Http\JsonResponse;

class ResponseService extends Service
{
    public function json(
        bool $success,
        ?string $message = null,
        mixed $data = null,
        null|Toast|array $toast = null,
        ?string $redirectTo = null,
        ?string $html = null,
        ?int $statusCode = 200
    ): JsonResponse {
        if ($toast instanceof Toast) {
            $toast = $toast->data();
        }
        $responseArray = array_filter(get_defined_vars(), fn ($val) => $val !== null);

        return response()->json($responseArray)->setStatusCode($statusCode);
    }

    public function errors(array $errors): JsonResponse
    {
        return response()->json(['errors' => $errors])->setStatusCode(422);
    }

    public function exceptionToast(Exception $e): JsonResponse
    {
        return $this->json(success: false, toast: new Toast('error', $e->getMessage()));
    }
}
