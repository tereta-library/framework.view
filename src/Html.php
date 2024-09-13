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
use Framework\View\Html\Block\Node as HtmlBlockNode;
use Framework\View\Php\Abstract\Block as AbstractBlock;

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
 * @since 2020-2024
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @author Tereta Alexander <tereta.alexander@gmail.com>
 * @copyright 2020-2024 Tereta Alexander
 */
class Html
{
    /**
     * @var Document|null
     */
    private ?Document $documentRoot = null;

    /**
     * @var array
     */
    private array $loadedUpdates = [];

    /**
     * @var string
     */
    private string $layout;

    /**
     * @var array
     */
    private array $blockIndex = [];

    /**
     * @var array
     */
    private array $blockList = [];

    /**
     * @param string $themeDirectory
     * @param string $generatedDirectory
     * @param array $dependencies
     */
    public function __construct(private string $themeDirectory, private string $generatedDirectory, private array $dependencies = [])
    {
    }

    /**
     * @param string $layout
     * @return $this
     * @throws Exception
     */
    public function initialize(string $layout): static
    {
        $this->layout = $layout;
        list($documentRoot, $documentList) = $this->load($layout);

        if (!$documentRoot && count($documentList) == 1) {
            $documentRoot = $documentList[0];
        }

        if (!$documentRoot) {
            throw new Exception('Root document is not defined');
        }

        $update = new HtmlUpdate($documentRoot);
        foreach ($documentList as $item) {
            $update->update($item);
        }

        (new HtmlBlock($documentRoot, $this->generatedDirectory))->process();
        (new HtmlPhp($documentRoot))->process();

        $this->blockIndex = [];
        $this->blockList = [];
        foreach ($documentRoot->getNodeList() as $item) {
            if (!$item instanceof HtmlBlockNode) {
                continue;
            }

            $this->blockList[] = $item;

            if ($item->getAttribute('id')) {
                $this->blockIndex[$item->getAttribute('id')] = $item;
            }
        }

        // On separation of the blocks into files - clearing child blocks performing
        // After that process no tree actions allowed
        (new HtmlSeparator($documentRoot, $this->generatedDirectory, $layout))->process();

        $this->documentRoot = $documentRoot;

        return $this;
    }

    /**
     * @param string $id
     * @return AbstractBlock|null
     */
    public function getBlockById(string $id): ?AbstractBlock
    {
        return $this->blockIndex[$id]->getBlock() ?? null;
    }

    /**
     * @param string|null $layout
     * @return string
     * @throws Exception
     */
    public function render(?string $layout = null): string
    {
        if ($layout) {
            $this->initialize($layout);
        }

        if (!$layout && $this->layout) {
            $layout = $this->layout;
        }

        foreach ($this->blockList as $item) {
            $item->getBlock()->initialize($this);
        }

        $documentRoot = $this->documentRoot;
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

    /**
     * @param string $template
     * @return array
     * @throws Exception
     */
    public function load(string $template): array
    {
        $documentList = [];
        $documentRoot = null;
        $this->loadItem($template, $documentRoot, $documentList);
        // @todo: looks like some artifact or not needed - to remove
        //foreach ($documentList as $key => $document) {
        //    $documentList[$key] = $document;
        //}

        return [$documentRoot, $documentList];
    }

    /**
     * @param string $template
     * @param Document|null $documentRoot
     * @param array $documentList
     * @param bool $isDependency
     * @return array
     * @throws Exception
     */
    private function loadItem(string $template, ?Document &$documentRoot, array &$documentList, bool $isDependency = false): array
    {
        $dependency = [];
        if (!$isDependency) {
            list($documentFile, $dependency) = $this->getFile($template);
        } else {
            $documentFile = $template;
        }

        if (!is_file($documentFile)) {
            throw new Exception('Template file not found: ' . $documentFile);
        }
        if (in_array($template, $this->loadedUpdates)) {
            return throw new Exception('Update file already loaded: ' . $documentFile);
        }
        $this->loadedUpdates[] = $template;

        $document = file_get_contents($documentFile);
        $document = (new Document($document, $documentFile));
        $nodeList = $document->getNodeList();

        $allowDependency = true;
        foreach ($nodeList as $node) {
            if ($node->getName() == 'meta' && $node->getAttribute('name') == 'backend-allowDependency') {
                $allowDependency = in_array($node->getAttribute('content'), ['true', 'yes']);
                break;
            }
        }

        if (!$allowDependency) {
            $dependency = [];
        }

        $dependency = array_reverse($dependency);
        foreach ($dependency as $dependencyFile) {
            $this->loadItem($dependencyFile, $documentRoot, $documentList, true);
        }

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

    /**
     * @param string $template
     * @return array
     */
    private function getFile(string $template): array
    {
        $resultDependency = [];
        foreach ($this->dependencies as $dependency) {
            $file = "{$dependency}/layout/{$template}.html";
            if (!is_file($file)) {
                continue;
            }
            $resultDependency[] = $file;
        }

        $initialFile = $this->themeDirectory . '/' . $template . '.html';
        if (is_file($initialFile)) {
            return [$initialFile, $resultDependency];
        }

        if ($resultDependency && $resultDependency[0]) {
            $file = array_shift($resultDependency);
            return [$file, $resultDependency];
        }

        return [$initialFile, $resultDependency];
    }

    /**
     * @param Node $node
     * @return bool
     */
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

    /**
     * Do update and add layouts
     *
     * @param Node $node
     * @param Document|null $documentRoot
     * @param array $documentList
     * @return bool
     * @throws Exception
     */
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