<?php

namespace App\Message;

class NewImportMessage
{
    protected $import_id;


    public function __construct(string $import_id)
    {
        $this->import_id = $import_id;
    }

    public function getImportId(): string
    {
        return $this->import_id;
    }
}
