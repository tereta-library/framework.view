<?php declare(strict_types=1);

namespace Framework\View\Html\Block;

use Framework\Dom\Node as DomNode;

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
 * @class Framework\View\Html\Block\Node
 * @package Framework\View\Html\Block
 * @link https://tereta.dev
 * @author Tereta Alexander <tereta.alexander@gmail.com>
 */
class Node extends DomNode
{
    public function renderContent(): string
    {
        $return = '';
        foreach ($this->getChildren() as $child) {
            $return .= $child->render();
        }
        return $return;
    }
}