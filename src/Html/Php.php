<?php declare(strict_types=1);

namespace Framework\View\Html;

use Framework\Dom\Document;
use Framework\Dom\Node;
use Framework\View\Html\Php\Node as PhpNode;

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
        if ($node->getType() != Node::TYPE_COMMENT) {
            return;
        }

        $tag = $node->getTag();
        $content = $tag->getContent();

        if (substr($content, 0, 2) != ': ') {
            return;
        }

        $phpNode = (new PhpNode($this->document))->import($node->export());
        $node->getParent()->replaceChild($node, $phpNode);
    }
}