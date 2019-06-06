<?php

namespace Symfony\Component\Workflow\Routing;

class Place
{
    private $placeName;
    private $condition;

    public function __construct($placeName, $condition = null)
    {
        $this->placeName = $placeName;
        $this->condition = $condition;
    }

    public function getPlaceName(): string
    {
        return $this->placeName;
    }

    public function getCondition(): ?string
    {
        return $this->condition;
    }
}
