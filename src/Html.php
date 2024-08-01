<?php declare(strict_types=1);

namespace Framework\View;

use Framework\Dom\Document;
use Exception;
use Framework\Dom\Node;
use Framework\View\Html\Update as HtmlUpdate;
use Framework\View\Html\Block as HtmlBlock;
use Framework\View\Html\Php as HtmlPhp;
use Framework\View\Html\Separator as HtmlSeparator;
use Framework\View\Php\Template;

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
    public function __construct(private string $themeDirectory, private string $generatedDirectory)
    {
    }

    /**
     * @param string $layout
     * @return string
     * @throws Exception
     */
    public function render(string $layout): string
    {
        list($documentRoot, $documentList) = $this->load($layout);

        if (!$documentRoot) {
            throw new Exception('Root document is not defined');
        }

        $update = new HtmlUpdate($documentRoot);
        foreach ($documentList as $item) {
            $update->update($item);
        }

        (new HtmlBlock($documentRoot))->process();
        (new HtmlPhp($documentRoot))->process();
        (new HtmlSeparator($documentRoot, $this->generatedDirectory, $layout))->process();

        $file = $layout . '.php';
        $fileFull = $this->generatedDirectory . '/' . $layout . '.php';
        $fileFull = str_replace('\\', '/', $fileFull);
        $dir = dirname($fileFull);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $content = "<!DOCTYPE html>\n" . $documentRoot->render();
        file_put_contents($fileFull, $content);

        return (string) (new Template($this->generatedDirectory))->setTemplate($file);
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
        $documentFile = $this->themeDirectory . '/' . $template . '.html';
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
            $node->remove();
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
        $node->remove();

        return true;
    }
}