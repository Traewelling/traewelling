<?php

namespace App\Dto\FriendlyPublicTransportFormat;

class Products
{
    public readonly bool $nationalExpress;
    public readonly bool $national;
    public readonly bool $regionalExp;
    public readonly bool $regional;
    public readonly bool $suburban;
    public readonly bool $bus;
    public readonly bool $ferry;
    public readonly bool $subway;
    public readonly bool $tram;
    public readonly bool $taxi;

    public function __construct()
    {
        $this->nationalExpress = false;
        $this->national = false;
        $this->regionalExp = false;
        $this->regional = false;
        $this->suburban = false;
        $this->bus = false;
        $this->ferry = false;
        $this->subway = false;
        $this->tram = true;
        $this->taxi = false;
    }
}
