<?php declare(strict_types=1);

namespace Framework\View;

use Framework\Dom\Document;
use Exception;
use Framework\Dom\Node;
use Framework\View\Html\Update;

/**
 * ···························WWW.TERETA.DEV······························
 * ·······································································
 * : _____                        _                     _                :
 * :|_   _|   ___   _ __    ___  | |_    __ _        __| |   ___  __   __:
 * :  | |    / _ \ | '__|  / _ \ | __|  / _` |      / _` |  / _ \ \ \ / /:
 * :  | |   |  __/ | |    |  __/ | |_  | (_| |  _  | (_| | |  __/  \ V / :
 * :  |_|    \___| |_|     \___|  \__|  \__,_| (_)  \__,_|  \___|   \_/  :
 * ·······································································
 * ·······································································
 *
 * @class Framework\View\Html
 * @package Framework\View
 * @link https://tereta.dev
 * @author Tereta Alexander <tereta.alexander@gmail.com>
 */
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

        $update = new Update($documentRoot);
        foreach ($documentList as $item) {
            $update->update($item);
        }

        return "<!DOCTYPE html>\n" . $documentRoot->render();
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
        $nodeList = $document->getNodeList();

        $isRootDocument = true;
        foreach ($nodeList as $node) {
            $this->update($node, $documentRoot, $documentList);

            if (!$this->isRootDocument($node)) {
                $isRootDocument = false;
            }
        }

        if ($isRootDocument){
            $documentRoot = $document;
        } else {
            $documentList[] = $document;
        }

        return $documentList;
    }

    private function isRootDocument(Node $node): bool
    {
        if ($node->getName() !== 'meta' ||
            $node->getAttribute('name') !== 'backend-update' ||
            !$node->getAttribute('content')) {
            return true;
        }

        return false;
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