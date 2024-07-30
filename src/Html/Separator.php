<?php declare(strict_types=1);

namespace Framework\View\Html;

use Framework\Dom\Document;
use Framework\View\Html\Block\Node as BlockNode;

class Separator
{
    public function __construct(private Document &$document)
    {
    }

    public function process() {
        $blockTemplates = [];
        $this->processSeparation($this->document->getNodeTree(), $blockTemplates);
        $e=0;
        $blockTemplates;
    }

    private function processSeparation(array $array=[], array &$blockTemplates) {
        foreach ($array as $node) {
            if ($node->getChildren()) {
                $this->processSeparation($node->getChildren(), $blockTemplates);
            }

            if (!($node instanceof BlockNode)) {
                continue;
            }

            $blockTemplates[] = [
                'class' => strtolower(get_class($node)),
                'html'  => $node->renderContent(),
                'node'  => $node
            ];

            $node->clearChildren();
        }
    }
}