<?php declare(strict_types=1);

namespace Framework\View;

use Framework\Dom\Document;
use Exception;
use Framework\Dom\Node;
use Framework\View\Html\Php\Interpolation;
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
     * @var string|null
     */
    private ?string $layout = null;

    /**
     * @var array
     */
    private array $blockIndex = [];

    /**
     * @var array
     */
    private array $blockList = [];

    /**
     * @var Interpolation $interpolation
     */
    private Interpolation $interpolation;

    /**
     * @var array $variables
     */
    private array $variables;

    /**
     * @param string $themeDirectory
     * @param string $generatedDirectory
     * @param string $cacheDirectory
     * @param array $dependencies
     * @param bool $useCache
     */
    public function __construct(
        private string $themeDirectory,
        private string $generatedDirectory,
        private string $cacheDirectory,
        private array $dependencies = [],
        private bool $useCache = true
    ) {
        $this->interpolation = new Interpolation;
    }

    /**
     * @param string $layout
     * @return $this
     * @throws Exception
     */
    public function initialize(string $layout): static
    {
        if ($this->layout === $layout) {
            return $this;
        }

        $this->layout = $layout;

        $documentRoot = $this->initializeUpdates($layout);

        (new HtmlBlock($documentRoot, $this->generatedDirectory))->process();
        (new HtmlPhp($documentRoot))->process();

        $this->fillBlockIndexes($documentRoot);

        /**
         * On separation of the blocks into files - clearing child blocks performing
         * After that process no tree actions allowed
         **/
        (new HtmlSeparator($documentRoot, $this->generatedDirectory, $layout))->process();

        $this->documentRoot = $documentRoot;

        return $this;
    }

    /**
     * @param string $layout
     * @return Document
     * @throws Exception
     */
    private function initializeUpdates(string $layout): Document
    {
        if ($this->useCache && $cached = $this->getCache("initialize.{$layout}")) {
            return unserialize($cached);
        }

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

        if ($this->useCache) {
            $this->setCache("initialize.{$layout}", serialize($documentRoot));
        }
        return $documentRoot;
    }

    /**
     * @param Document $documentRoot
     * @return void
     * @throws Exception
     */
    private function fillBlockIndexes(Document $documentRoot): void
    {
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
    }

    /**
     * @param string $key
     * @return string
     */
    private function getCacheFile(string $key): string
    {
        return "{$this->cacheDirectory}/{$key}.cache";
    }

    /**
     * @param string $key
     * @return string|null
     */
    private function getCache(string $key): ?string
    {
        $file = $this->getCacheFile($key);
        if (!is_file($file)) {
            return null;
        }

        return file_get_contents($file);
    }

    /**
     * @param string $key
     * @param string $value
     * @return $this
     */
    private function setCache(string $key, string $value): static
    {
        $file = $this->getCacheFile($key);
        $dir = dirname($file);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($file, $value);

        return $this;
    }

    /**
     * @param string $id
     * @return AbstractBlock|null
     * @throws Exception
     */
    public function getBlockById(string $id): ?AbstractBlock
    {
        if (!isset($this->blockIndex[$id])) {
            throw new Exception("Block with id {$id} not found");
        }

        return $this->blockIndex[$id]->getBlock() ?? null;
    }

    /**
     * @param string|array $keyVariables
     * @param array|null $variable
     * @return $this
     */
    public function assign(string|array $keyVariables, mixed $variable = null): static
    {
        $isArrayMode = (is_array($keyVariables) && $variable === null);
        foreach ($isArrayMode ? $keyVariables : [] as $key => $value) {
            $this->variables[$key] = $value;
        }

        if ($isArrayMode) {
            return $this;
        }

        $key = $keyVariables;
        $this->variables[$key] = $variable;
        return $this;
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

        $render = true;
        if ($this->useCache && is_file($fileFull)) {
            $documentRoot->render(false);
            $render = false;
        }

        if ($render && !is_file($fileFull)) {
            $content = "<!DOCTYPE html>\n" . $this->renderDocument($documentRoot);
            file_put_contents($fileFull, $content);
            $render = false;
        }

        if ($render) {
            $content = "<!DOCTYPE html>\n" . $this->renderDocument($documentRoot);
        }
        if ($render && $content !== file_get_contents($fileFull)) {
            file_put_contents($fileFull, $content);
        }

        return (string) (new Template($this->generatedDirectory))->assign($this->variables)->setTemplate($file);
    }

    /**
     * @param Document $document
     * @return string
     * @throws Exception
     */
    private function renderDocument(Document $document): string
    {
        return trim($this->interpolation->process($document->render()));
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