<?php

namespace App;

use App\Exceptions\Http\HttpNotFoundException;
use App\Exceptions\Http\HttpRequestException;
use App\Models\Game;
use App\Http\Responses\OkResponse;


class Router
{
    public const METHOD_REGEX = '/^\/(\w+)/';


    /**
     * Routes to specified method.
     *
     * @return OkResponse
     * @throws HttpRequestException
     * @throws HttpNotFoundException
     * @throws Exceptions\Database\DatabaseInvalidPieceException
     */
    public static function init(): OkResponse
    {
        $uri = $_SERVER['REQUEST_URI'];
        $method = self::getMethod($uri);

        if (!isset($_REQUEST['id']) && $method !== 'create') {
            throw new HttpRequestException('No id passed', 400);
        }
        $gameId = (int)$_REQUEST['id'];

        $game = new Game($gameId);
        if (!method_exists($game, $method)) {
            throw new HttpNotFoundException('Method not found', 404);
        }

        return new OkResponse($game->$method());
    }


    /**
     * Gets method name from the given URI.
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