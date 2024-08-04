<?php declare(strict_types=1);

namespace Framework\View\Html;

use Framework\Dom\Document;
use Framework\View\Html\Block\Node as BlockNode;
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
    public function __construct(private Document &$document, private string $themeDirectory)
    {
    }

    public function process() {
        foreach($this->document->getNodeList() as $node) {
            $this->processBlock($node);
        }
    }

    public function processBlock($node)
    {
        $blockClass = $node->getAttribute('data-backend-block');
        if (!$blockClass) return;

        if (substr($blockClass, 0, 1) != '\\') {
            $blockClass = "\\{$blockClass}";
        }

        $block = new BlockNode($this->document);
        $block->setBlock(new $blockClass($this->themeDirectory));
        $block->import($node->export());
        $node->getParent()->replaceChild($node, $block);
    }
}