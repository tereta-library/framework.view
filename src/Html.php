<?php declare(strict_types=1);

namespace Framework\View;

use Framework\Dom\Document;
use Exception;

class Html
{
    /**
     * @param string $theme Theme path
     */
    public function __construct(private string $theme)
    {
    }

    /**
     * @param string $template
     * @return string
     * @throws Exception
     */
    public function render(string $template): string
    {
        $document = file_get_contents($this->theme . '/' . $template . '.html');
        $html = '';
        $domTree = (new Document($document))->getTree();
        foreach ($domTree as $item){
            $html .= $item . "\n";
        }

        return $html;
    }
}