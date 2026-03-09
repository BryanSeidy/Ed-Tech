<?php

namespace App\Exceptions;

use RuntimeException;

class AlreadyEnrolledException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('L\'utilisateur est déjà inscrit à ce cours.');
    }
}
