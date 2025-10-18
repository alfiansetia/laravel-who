<?php

namespace App\Services;

class Breadcrumb
{
    public string $name;
    public ?string $url;
    public bool $active;

    public function __construct(string $name, ?string $url = null, bool $active = false)
    {
        $this->name = $name;
        $this->url = $url;
        $this->active = $active;
    }
}
