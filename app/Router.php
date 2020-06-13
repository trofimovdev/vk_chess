<?php

namespace App;

use App\Exceptions\Http\HttpNotFoundException;
use App\Models\Game;
use App\Http\Responses\BaseResponse;


class Router
{
    public const METHOD_REGEX = '/^\/(\w+)/';


    /**
     * Routes to specified method.
     *
     * @return BaseResponse
     * @throws HttpNotFoundException
     */
    public static function init(): BaseResponse
    {
        $uri = $_SERVER['REQUEST_URI'];
        $action = self::getMethod($uri);
        $game = new Game();

        if (!method_exists($game, $action)) {
            throw new HttpNotFoundException('Method not found', 404);
        }

        return $game->$action();
    }


    /**
     * Gets method name from the given URI
     *
     * @param string $uri
     * @return string
     * @throws HttpNotFoundException
     */
    private static function getMethod(string $uri): string
    {
        preg_match(self::METHOD_REGEX, $uri,$matches);
        if (count($matches) <= 1) {
            throw new HttpNotFoundException('Method not found', 404);
        }

        return $matches[1];
    }
}