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
 * @since 2020-2024
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @author Tereta Alexander <tereta.alexander@gmail.com>
 * @copyright 2020-2024 Tereta Alexander
 */
class Node extends DomNode
{
    /**
     * @return string
     */
    public function render(): string
    {
        $tag = $this->getTag();
        $content = $tag->getContent();
        if (substr($content, 0, 2) != ': ') {
            return parent::render();
        }

        $content = substr($content, 2);

        if (preg_match('/^echo\s+\$([a-z0-9_\-\[\]\'\"]+)\s*$/Ui', $content, $matches)) {
            $variable = $matches[1];
            return "<?php echo \${$variable} ?>";
        }

        if (preg_match('/^json\s+\$([a-z0-9_\-\[\]\'\"]+)\s*$/Ui', $content, $matches)) {
            $variable = $matches[1];
            return "<?php echo json_encode(\${$variable}) ?>";
        }

        if (preg_match('/^comment\s+\$([a-z0-9_\-\[\]\'\"]+)\s*$/Ui', $content, $matches)) {
            $variable = $matches[1];
            return "<?php echo '<!-- ' . \${$variable} . '-->' ?>";
        }

        if (preg_match('/^if\s+(.+)\s*$/Ui', $content, $matches)) {
            $variable = $matches[1];
            return $this->renderIf($variable);
        }

        if (preg_match('/^foreach\s+\$([a-z0-9_\-\[\]\'\"]+)\s+as\s+\$([a-z0-9_\-\[\]\'\"]+)\s*$/Ui', $content, $matches)) {
            $variable = $matches[1];
            $as = $matches[2];
            return $this->renderForeach($variable, $as);
        }

        if (preg_match('/^foreach\s+\$([a-z0-9_\-\[\]\'"]+)\s+as\s+\$([a-z0-9_\-\[\]\'"]+)\s*=\>\s*\$([a-z0-9_\-\[\]\'"]+)\s*$/Ui', $content, $matches)) {
            $variable = $matches[1];
            $key = $matches[2];
            $as = $matches[3];
            return $this->renderForeach($variable, $as, $key);
        }

        if (preg_match('/^\s*endif\s*$/Ui', $content, $matches)) {
            return "<?php endif ?>";
        }

        if (preg_match('/^\s*endforeach\s*$/Ui', $content, $matches)) {
            return "<?php endforeach ?>";
        }

        return parent::render();
    }

    /**
     * @param string $variable
     * @param string $as
     * @param string|null $key
     * @return string
     */
    private function renderForeach(string $variable, string $as, ?string $key = null): string
    {
        if ($key) {
            return "<?php foreach (isset($" . $variable . ") ? $" . $variable . " : [] as $" . $key . " => $" . $as . ") : ?>";
        }

        return "<?php foreach (isset($" . $variable . ") ? $" . $variable . " : [] as $" . $as . ") : ?>";
    }

    /**
     * @param string $condition
     * @return string
     */
    private function renderIf(string $condition): string
    {
        if (preg_match('/^\$([0-9a-zA-Z]+)(\[[\'"0-9a-zA-Z]+\])*$/Usi', $condition, $matches)) {
            return "<?php if (isset({$condition}) ? {$condition} : false): ?>";
        }

        return parent::render();
    }
}