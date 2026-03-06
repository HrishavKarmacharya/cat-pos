<?php

namespace App\Exceptions;

use Exception;

class MissingLayoutException extends Exception
{
    public function __construct($layoutName, $code = 0, Exception $previous = null)
    {
        $message = "Layout file not found: '{$layoutName}'. Please ensure the layout file exists in the 'resources/views/layouts' directory.";
        parent::__construct($message, $code, $previous);
    }
}
