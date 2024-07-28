<?php declare(strict_types=1);

namespace Framework\View\Html;

use Framework\Dom\Document;
use Framework\Dom\Node;
use Framework\View\Html\Select;

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

    public function update(Document $updateDocument) {
        foreach($updateDocument->getNodeList() as $node) {
            $this->moveContent($node);
        }
    }

    private function moveContent(Node $item): void {
        $selector = $item->getAttribute('data-backend-content');
        if (!$selector) return;

        $rootElement = $this->selector->getBySelector($selector);
        $rootElement->clearChildren();
        foreach ($item->getChildren() as $child) {
            $rootElement->addChildren($child);
        }
    }
}