<?php declare(strict_types=1);

namespace Framework\View\Html;

use Framework\Dom\Document;
use Framework\View\Html\Block\Node as BlockNode;

class Separator
{
    public function __construct(private Document &$document, private string $generatedDirectory, private string $layout)
    {
    }

    public function process() {
        $blockTemplates = [];
        $this->processSeparation($this->document->getNodeTree(), $blockTemplates);
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

            $file = $this->layout . '/' . ltrim(strtolower($node->getBlockIdentifier()), '\/') . '.php';
            $file = str_replace('\\', '/', $file);
            $fileFull = $this->generatedDirectory . '/' . $file;
            $dir = dirname($fileFull);
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
            $content = $node->renderContent(true);
            if (!is_file($fileFull) || file_get_contents($fileFull) != $content) {
                file_put_contents($fileFull, $content);
            }

            $node->setTemplate($file);
            $node->clearChildren();
        }
    }
}