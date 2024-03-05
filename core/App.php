<?php

namespace core;

use app\Models\AdminModel;
use app\Models\UserModel;
use core\Database\Database;
use core\Router\Request;
use core\Router\Response;
use core\Router\Router;
use core\Router\RoutesCollector;

class App
{
    public Router $router;
    public Request $request;
    public Response $response;
    public View $view;
    public Database $database;
    public Session $session;
    public UserModel|AdminModel|null $user;
    public bool $loggedIn = false;
    public static App $app;

    public function __construct(
        public RoutesCollector $routesCollector,
        public $config = [],
    )
    {
        self::$app = $this;
        $this->request = new Request();
        $this->response = new Response();
        $this->database = new Database($this->config['db']);
        $this->user = new UserModel();
        $this->session = new Session();
    }

    public function run(string $environment = 'cli'): void
    {
        if ($environment == 'web') {
            $this->runWeb();
        }
        echo "<p class='text-gray-500'>App process time: " . round(microtime(true)-START, 4) . "ms</p>";
    }

    public function runWeb(): void
    {
        $this->router = new Router($this->response, $this->request, $this->routesCollector);
        $this->router->dispatch();
    }
}