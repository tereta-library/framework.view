<?php declare(strict_types=1);

namespace Framework\View\Html;

use Framework\View\Html\Node\Tag as NodeTag;
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
 * @class Framework\View\Html\Parser
 * @package Framework\View
 * @link https://tereta.dev
 * @author Tereta Alexander <tereta.alexander@gmail.com>
 */
class Parser
{
    const MODE_TEXT = 0;
    const MODE_TAG = 1;
    const MODE_ATTRIBUTE = 2;

    private ?int $position = 0;

    private int $mode = self::MODE_TEXT;

    public function __construct(private string $document)
    {
    }

    /**
     * @return Node
     */
    public function decode()
    {
        $array = [];
        while($this->position !== null) {
            $item = $this->decodeItem();
            if ($item) {
                $array[] = $item;
            }
        }

        return $array;
    }

    private function decodeItem()
    {
        switch($this->mode) {
            case self::MODE_TEXT:
                return $this->parseText();
            case self::MODE_TAG:
                return $this->parseTag();
        }

        return null;
    }

    private function parseTag()
    {
        $initialPosition = $this->position;

        $openTag = preg_match('/^\<\s*([\w_-]+)(\s|\>){1}/Usi', substr($this->document, $initialPosition),$matchesOpen);
        $closeTag = preg_match('/^\<\s*\/\s*([\w_-]+)(\s|\>){1}/Usi', substr($this->document, $initialPosition), $matchesClose);

        if (!$openTag && !$closeTag) {
            throw new Exception('Invalid tag at position ' . $initialPosition . ' in document');
        }

        if ($openTag) {
            $matches = $matchesOpen;
        } elseif ($closeTag) {
            $matches = $matchesClose;
        }

        $tagName = $matches[1];
        $offset = strlen($matches[0]) + $this->position - 1;

        $attributes = [];
        while(preg_match(
            '/^\s*([\w_-]+)\s*=\s*/Usi',
            substr($this->document, $offset),
            $matches
        )) {
            $offset += strlen($matches[0]);
            $attribute = $matches[1];
            $value = $this->parseAttributeValue($this->document, $offset);
            $attributes[$attribute] = $value;
        }

        if (!preg_match(
            '/^[\s]*\>/Usi',
            substr($this->document, $offset),
            $matches
        )) {
            throw new Exception('Invalid tag at position ' . $offset . ' in document');
        }

        $offset = $offset + strlen($matches[0]);

        $this->mode = self::MODE_TEXT;
        $this->position = $offset;

        return (new NodeTag)->setName($tagName)->setPosition($initialPosition, $this->position)
            ->setAttributes($attributes);
    }

    private function parseAttributeValue(string &$document, int &$offset): string
    {
        $quote = substr($document, $offset, 1);
        $offset++;
        $initialOffset = $offset;
        $string = substr($document, $offset);

        $offsetInside = 0;
        while (true) {
            $offsetInside = strpos($string, $quote, $offsetInside);
            $escapeCount = 0;
            while (substr($string, $offsetInside - 1 - $escapeCount, 1) === '\\') {
                $escapeCount++;
            }

            if ($escapeCount % 2 === 0) {
                break;
            }

            $offsetInside = $offsetInside + 1;
        }
        $offset = $offset + $offsetInside + 1;

        return substr($document, $initialOffset, $offsetInside);
    }

    private function parseText()
    {
        $initialPosition = $this->position;
        $position = strpos($this->document, '<', $initialPosition);
        if ($position === false) {
            $this->position = null;
            $tag = substr($this->document, $initialPosition);
            if (!trim($tag)) return null;
            return new Node(
                "text",
                $tag
            );
        }

        $this->mode = self::MODE_TAG;

        $this->position = $position;
        $tag = substr($this->document, $initialPosition, $position - $initialPosition);

        if (!trim($tag)) return null;

        return new Node(
            "text",
            $tag
        );
    }
}