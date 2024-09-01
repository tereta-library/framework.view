<?php declare(strict_types=1);

namespace Framework\View\Html\Php;

use Framework\Dom\Node as DomNode;
use Framework\View\Html\Php\Renderer as PhpRenderer;
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
     * @var Renderer $phpRenderer
     */
    private PhpRenderer $phpRenderer;

    /**
     * @return void
     */
    protected function construct(): void
    {
        $this->phpRenderer = new PhpRenderer;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function render(): string
    {
        $tag = $this->getTag();
        $content = $tag->getContent();
        if (substr($content, 0, 2) != ': ') {
            return parent::render();
        }

        $content = substr($content, 2);

        if (preg_match('/^echo\s+(.+)\s*$/Ui', $content, $matches)) {
            $variable = $matches[1];
            $rendered = $this->phpRenderer->render($variable);

            return "<?php echo {$rendered} ?>";
        }

        if (preg_match('/^json\s+(.+)\s*$/Ui', $content, $matches)) {
            $variable = $matches[1];
            $rendered = $this->phpRenderer->render($variable);
            return "<?php echo json_encode({$rendered}) ?>";
        }

        if (preg_match('/^comment\s+(.+)\s*$/Ui', $content, $matches)) {
            $variable = $matches[1];
            $rendered = $this->phpRenderer->render($variable);
            return "<?php echo '<!-- ' . {$rendered} . '-->' ?>";
        }

        if (preg_match('/^if\s+(.+)\s*$/Ui', $content, $matches)) {
            $variable = $matches[1];
            $rendered = $this->phpRenderer->render($variable);
            return $this->renderIf($rendered);
        }

        if (preg_match('/^foreach\s+([^ ]+)\s*$/Ui', $content, $matches)) {
            $variable = $matches[1];
            $key = 'key';
            $as = 'item';
            $rendered = $this->phpRenderer->render($variable);
            return $this->renderForeach($rendered, $as, $key);
        }

        if (preg_match('/^foreach\s+(.+)\s+as\s+\$([a-z0-9_\-\[\]\'\"]+)\s*$/Ui', $content, $matches)) {
            $variable = $matches[1];
            $as = $matches[2];
            $rendered = $this->phpRenderer->render($variable);
            return $this->renderForeach($rendered, $as);
        }

        if (preg_match('/^foreach\s+(.+)\s+as\s+\$([a-z0-9_\-\[\]\'"]+)\s*=\>\s*\$([a-z0-9_\-\[\]\'"]+)\s*$/Ui', $content, $matches)) {
            $variable = $matches[1];
            $key = $matches[2];
            $as = $matches[3];
            $rendered = $this->phpRenderer->render($variable);
            return $this->renderForeach($rendered, $as, $key);
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
            return "<?php foreach (" . $variable . " as $" . $key . " => $" . $as . ") : ?>";
        }

        return "<?php foreach (" . $variable . " as $" . $as . ") : ?>";
    }

    /**
     * @param string $condition
     * @return string
     */
    private function renderIf(string $condition): string
    {
        return "<?php if ({$condition}): ?>";
    }
}