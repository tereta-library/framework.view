<?php declare(strict_types=1);

namespace Framework\View\Html\Php;

use Framework\View\Html\Php\Interpolation\Variable;

/**
 * @class Framework\View\Html\Php\Interpolation
 */
class Interpolation
{
    /**
     * Return processed content
     * The {$variable} will be replaced with <?= $variable ?>
     *     Example: {$variable} => <?= $variable ?>
     *         {$variable['key']} => <?= $variable['key'] ?>
     *         {$variable->method()} => <?= $variable->method() ?>
     *         {$variable->method('argument')} => <?= $variable->method('argument') ?>
     *         {$variable->method('argument', 'argument')} => <?= $variable->method('argument', 'argument') ?>
     *
     * @param string $content
     * @return string
     */
    public function process(string $content): string
    {
        $variable = new Variable();
        while ($variable->find($content)) {
            $block = $variable->render();
            $content = substr_replace($content, $block, $variable->getPosition(), $variable->getLength());
        }

        return $content;
    }
}