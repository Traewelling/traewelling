<?php

namespace App\Dto\FriendlyPublicTransportFormat;

class Operator
{
    public readonly string $type;
    public readonly string $id;
    public readonly string $name;

    public function __construct(object $data)
    {
        $this->type = "operator";
        $this->id = $data->id;
        $this->name = $data->name;
    }
}
