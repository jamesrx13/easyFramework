<?php

namespace api;

class ApiRouter
{

    protected array $apiRouter = [
        'demo' => [],
        'clientes' => [],
    ];

    public function AllApiRouters()
    {
        return $this->apiRouter;
    }

    public function existRoute($route)
    {
        return array_key_exists($route, $this->apiRouter);
    }
}
