<?php declare(strict_types=1);

namespace Framework\View\Html;

use Framework\Dom\Document;
use Framework\View\Html\Block\Node as BlockNode;
use Framework\Dom\Node;
use Exception;

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
 * @class Framework\View\Html\Block
 * @package Framework\View\Html
 * @link https://tereta.dev
 * @since 2020-2024
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @author Tereta Alexander <tereta.alexander@gmail.com>
 * @copyright 2020-2024 Tereta Alexander
 */
class Block
{
    /**
     * @param Document $document
     * @param string $themeDirectory
     */
    public function __construct(private Document &$document, private string $themeDirectory)
    {
    }

    /**
     * @return void
     * @throws Exception
     */
    public function process(): void {
        foreach($this->document->getNodeList() as $key => $node) {
            $this->processBlock($node);
        }
    }

    /**
     * @param Node $node
     * @return void
     * @throws Exception
     */
    public function processBlock(Node $node): void
    {
        $blockClass = $node->getAttribute('data-backend-block');
        if (!$blockClass) return;

        $blockData = trim($node->getAttribute('data-backend-data'));
        if ($blockData && substr($blockData, 0, 1) != '{' && substr($blockData, -1, 1) != '}') {
            $blockData = "{{$blockData}}";
        }

        if (substr($blockClass, 0, 1) != '\\') {
            $blockClass = "\\{$blockClass}";
        }

        $block = new BlockNode($this->document);
        $block->setBlock(new $blockClass($blockData ? json_decode($blockData, true) : [], $this->themeDirectory));
        $block->import($node->export());
        $node->getParent()->replaceChild($node, $block);
    }
}