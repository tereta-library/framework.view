<?php declare(strict_types=1);

namespace Framework\View\Html\Php;

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
 * @class Framework\View\Html\Php\Node
 * @package Framework\View\Html\Php
 * @link https://tereta.dev
 * @author Tereta Alexander <tereta.alexander@gmail.com>
 */
class Node extends DomNode
{
    public function render(): string
    {
        $tag = $this->getTag();
        $content = $tag->getContent();
        if (substr($content, 0, 2) != ': ') {
            return parent::render();
        }

        $content = substr($content, 2);

        if (preg_match('/^render\s+\$([a-z0-9_\-\[\]\'\"]+)\s*$/', $content, $matches)) {
            $variable = $matches[1];
            return "<?php echo \${$variable} ?>";
        }

        if (preg_match('/^if\s+(.+)\s*$/U', $content, $matches)) {
            $variable = $matches[1];
            return $this->renderIf($variable);
        }

        if (preg_match('/^foreach\s+\$([a-z0-9_\-\[\]\'\"]+)\s+as\s+\$([a-z0-9_\-\[\]\'\"]+)\s*$/U', $content, $matches)) {
            $variable = $matches[1];
            $as = $matches[2];
            return $this->renderForeach($variable, $as);
        }

        if (preg_match('/^\s*endif\s*$/U', $content, $matches)) {
            return "<?php endif ?>";
        }

        if (preg_match('/^\s*endforeach\s*$/U', $content, $matches)) {
            return "<?php endforeach ?>";
        }

        return parent::render();
    }

    private function renderForeach(string $variable, string $as): string
    {
        return "<?php foreach $" . $variable . " as $" . $as . " : ?>";
    }

    private function renderIf(string $condition): string
    {
        return "<?php if ({$condition} !!!!!!!!): ?>";
    }
}