<?php

namespace api;

use core\main\FrameworkMain;

class ApiRouter
{

    protected array $apiRouter;

    public function __construct()
    {
        $this->apiRouter = [
            'demo' => [],
        ];
    }

    public function AllApiRouters()
    {
        return $this->apiRouter;
    }

    public function existRoute($route)
    {
        return array_key_exists($route, $this->apiRouter);
    }

    public function getMiddlewares(String $route){
        return $this->apiRouter[$route];
    }
}
