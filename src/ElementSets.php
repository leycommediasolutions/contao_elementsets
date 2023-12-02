<?php

namespace leycommediasolutions\contao_elementsets;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class ElementSets extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}