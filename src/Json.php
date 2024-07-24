<?php declare(strict_types=1);

namespace Framework\View;

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
 * @class Framework\View\Json
 * @package Framework\View
 * @link https://tereta.dev
 * @author Tereta Alexander <tereta.alexander@gmail.com>
 */
class Json
{
    /**
     * @param array|object|string|int $data
     * @return string
     */
    public function render(array|object|string|int $data): string
    {
        header('Content-Type: application/json; charset=utf-8');

        return json_encode($data);
    }
}