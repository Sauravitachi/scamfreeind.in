<?php

namespace App\Foundation;

use App\DTO\Toast;
use Illuminate\Routing\Controllers\HasMiddleware;
use InvalidArgumentException;

abstract class Controller implements HasMiddleware
{
    /**
     * Helper variable to store toast data when doing conditionally
     */
    protected null|string|array $toast = null;

    /**
     * Helper function to set toast flash data.
     *
     * @param  Toast|string  $type  The type of toast or a Toast object.
     * @param  string|null  $message  Optional message if $type is a string.
     */
    protected function flashToast(Toast|string $type, ?string $message = null): void
    {
        if ($type instanceof Toast) {
            $toastArray = $type->data();
        } else {
            throw_if($message === null, InvalidArgumentException::class, 'message parameter is required!');
            $toastArray = compact('type', 'message');
        }

        session()->flash('toast', $toastArray);
    }

    /**
     * Permission middleware for resource controller methods
     */
    public static function middleware(): array
    {
        return [];
    }
}
