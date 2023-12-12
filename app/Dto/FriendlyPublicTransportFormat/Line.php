<?php

namespace App\Dto\FriendlyPublicTransportFormat;

class Line
{
    public readonly string $type;
    public readonly string $id;
    public readonly ?string $fahrtNr;
    public readonly string $name;
    public readonly bool $public;
    public readonly string $adminCode;
    public readonly string $productName;
    public readonly string $mode;
    public readonly string $product;
    public readonly Operator $operator;

    public function __construct(object $data)
    {
        $this->type = "line";
        $this->id = $data->id;
        $this->fahrtNr = $data->fahrtNr;
        $this->name = $data->name;
        $this->public = $data->public;
        $this->adminCode = $data->adminCode;
        $this->productName = $data->productName;
        $this->mode = $data->mode;
        $this->product = $data->product;
        $this->operator = $data->operator;
    }
}
