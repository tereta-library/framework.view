<?php declare(strict_types=1);

namespace Framework\View\Html;

use Framework\Dom\Document;
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
 * @class Framework\View\Html\Update
 * @package Framework\View\Html
 * @link https://tereta.dev
 * @author Tereta Alexander <tereta.alexander@gmail.com>
 */
class Update
{
    /**
     * @var Selector $selector
     */
    private Selector $selector;

    /**
     * @param Document $rootDocument
     */
    public function __construct(private Document $rootDocument)
    {
        $this->selector = new Selector($this->rootDocument);
    }

    /**
     * @param Document $updateDocument
     * @return void
     * @throws Exception
     */
    public function update(Document $updateDocument) {
        foreach($updateDocument->getNodeList() as $node) {
            $this->moveContent($node);
            $this->addContent($node);
            $this->replaceNode($node);
        }
    }

    /**
     * @param Node $item
     * @return void
     */
    public function replaceNode(Node $item): void {
        $selector = $item->getAttribute('data-backend-replace');
        if (!$selector) return;

        $rootElement = $this->selector->getBySelector($selector);
        $rootElement->getParent()->replaceChild($rootElement, $item);
    }

    /**
     * @param Node $item
     * @return void
     */
    private function moveContent(Node $item): void {
        $selector = $item->getAttribute('data-backend-content');
        if (!$selector) return;

        $rootElement = $this->selector->getBySelector($selector);
        $rootElement->clearChildren();
        foreach ($item->getChildren() as $child) {
            $child->setParent($rootElement);
            $rootElement->addChildren($child);
        }
    }

    private function addContent(Node $item): void {
        $selector = $item->getAttribute('data-backend-add');
        if (!$selector) return;

        $rootElement = $this->selector->getBySelector($selector);
        $item->setParent($rootElement);
        $rootElement->addChildren($item);
        $item->setAttribute('data-backend-add', null);
    }
}