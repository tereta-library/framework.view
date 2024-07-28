<?php declare(strict_types=1);

namespace Framework\View\Html;

use Framework\Dom\Document;

class Php
{
    public function __construct(private Document &$document)
    {
    }

    public function process() {
        foreach($this->document->getNodeList() as $node) {
            $this->processPhp($node);
        }
    }

    private function processPhp($node)
    {
        $e=0;
    }
}