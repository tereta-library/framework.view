<?php declare(strict_types=1);

namespace Framework\View;

use Framework\Dom\Document;
use Exception;
use Framework\Dom\Node;
use Framework\View\Html\Update as HtmlUpdate;
use Framework\View\Html\Block as HtmlBlock;
use Framework\View\Html\Php as HtmlPhp;
use Framework\View\Html\Separator as HtmlSeparator;

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
    private array $loadedUpdates = [];

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

        $update = new HtmlUpdate($documentRoot);
        foreach ($documentList as $item) {
            $update->update($item);
        }

        (new HtmlBlock($documentRoot))->process();
        (new HtmlPhp($documentRoot))->process();
        (new HtmlSeparator($documentRoot))->process();

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
        if (!is_file($documentFile)) throw new Exception('Template file not found: ' . $documentFile);
        if (in_array($template, $this->loadedUpdates)) return throw new Exception('Update file already loaded: ' . $documentFile);
        $this->loadedUpdates[] = $template;

        $document = file_get_contents($documentFile);
        $document = (new Document($document, $documentFile));
        $nodeList = $document->getNodeList();

        $isRootDocument = false;
        foreach ($nodeList as $node) {
            $this->update($node, $documentRoot, $documentList);

            if ($this->isRootDocument($node)) {
                $isRootDocument = true;
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
            $node->getAttribute('name') !== 'backend-layout' ||
            !$node->getAttribute('content')) {
            return false;
        }

        $backendLayout = $node->getAttribute('content');
        if ($backendLayout === 'root') {
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