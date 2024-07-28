<?php declare(strict_types=1);

namespace Framework\View\Html;

use Framework\Dom\Document;
use Framework\Dom\Node;

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
 * @class Framework\View\Html\Selector
 * @package Framework\View\Html
 * @link https://tereta.dev
 * @author Tereta Alexander <tereta.alexander@gmail.com>
 */
class Selector
{
    public function __construct(private Document $rootDocument)
    {
    }

    public function getBySelector(string $selectorString): ?Node
    {
        $selectorString = trim($selectorString);
        $selector = [];
        while(true) {
            if (!preg_match('/^(\>\s+)?[\#\.\w]+/', $selectorString, $matches)) {
                break;
            }

            $item = $matches[0];
            $selectorString = trim(substr($selectorString, strlen($item)));
            $selector[] = $item;
        }

        $htmlNode = null;
        foreach($this->rootDocument->getNodeTree() as $node) {
            if ($node->getName() == 'html') {
                $htmlNode = $node;
                break;
            }
        }

        $pointer = $htmlNode;

        foreach($selector as $item) {
            $item = trim($item);
            if (preg_match('/^\#/', $item)) {
                $item = substr($item, 1);
                $node = $this->getById($pointer, $item);
                $pointer = $node;
            } else {
                return null;
            }

            if (!$node) {
                return null;
            }
        }

        return $node;
    }

    /**
     * @param $pointer
     * @param string $id
     * @return Node|null
     */
    private function getById(&$pointer, string $id): ?Node
    {
        $internalPointer = $pointer;

        if ($internalPointer->getAttribute('id') == $id) {
            return $internalPointer;
        }

        foreach ($internalPointer->getChildren() as $child) {
            if ($child->getType() != Node::TYPE_TAG) {
                continue;
            }

            if ($node = $this->getById($child, $id)) {
                return $node;
            }
        }

        return null;
    }
}