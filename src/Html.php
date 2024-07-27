<?php declare(strict_types=1);

namespace Framework\View;

use Framework\Dom\Document;
use Exception;
use Framework\Dom\Node;

class Html
{
    /**
     * @param string $theme Theme path
     */
    public function __construct(private string $theme)
    {
    }

    /**
     * @param string $template
     * @return string
     * @throws Exception
     */
    public function render(string $template): string
    {
        list($documentRoot, $documentList) = $this->load($template);

        $html = $documentRoot->render() . "\n-------------------------\n";

        foreach ($documentList as $item){
            $html .= $item . "\n---------------------\n";
        }

        return $html;
    }

    public function load(string $template): array
    {
        $documentList = [];
        $documentRoot = null;
        $this->loadItem($template, $documentRoot, $documentList);
        foreach ($documentList as $key => $document) {
            $documentList[$key] = $document;
        }

        return [$documentRoot, $documentList];
    }

    private function loadItem(string $template, ?Document &$documentRoot, array &$documentList): array
    {
        $documentFile = $this->theme . '/' . $template . '.html';
        $document = file_get_contents($documentFile);
        $document = (new Document($document, $documentFile));
        $nodeTree = $document->getNodeTree();
        $nodeList = $document->getNodeList();
        $tagList = $document->fetchTags();

        $isRootDocument = true;
        foreach ($nodeList as $node) {
            if ($this->update($node, $documentRoot, $documentList)) {
                $isRootDocument = false;
                break;
            }
        }

        if ($isRootDocument){
            $documentRoot = $document;
        } else {
            $documentList[] = $document;
        }

        return $documentList;
    }

    private function update(Node $node, ?Document &$documentRoot, array &$documentList = []): bool
    {
        if ($node->getName() !== 'meta' ||
            $node->getAttribute('name') != 'backend-update' ||
            !$node->getAttribute('content')) {
            return false;
        }

        $this->loadItem($node->getAttribute('content'), $documentRoot, $documentList);

        return true;
    }
}