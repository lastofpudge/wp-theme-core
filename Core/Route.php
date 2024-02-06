<?php

namespace Core;

use Timber\Timber;

class Route
{
    /**
     * Load a controller method and render the associated view.
     *
     * @param string $controller The fully qualified class name of the controller.
     * @param string $method The method to call on the controller.
     * @param string $view The view file to render.
     *
     */
    public static function load(string $controller, string $method, string $view): void
    {
        if (!class_exists($controller) || !method_exists($controller, $method)) {
            return;
        }

        $controllerInstance = new $controller();
        $data = $controllerInstance->$method();

        static::renderView($view, $data);
    }

    /**
     * Render a view with Timber.
     *
     * @param string $view The view file to render.
     * @param array $data The data to pass to the view.
     */
    private static function renderView(string $view, array $data): void
    {
        Timber::render('views/' . $view . '.twig', $data);
        exit;
    }

    /**
     * Render a view without a controller.
     *
     * @param string $view The view file to render.
     */
    public static function view(string $view): void
    {
        static::renderView($view, []);
    }

    /**
     * Register a REST API route, call the controller method, and return the data as JSON.
     *
     * @param string $controller The fully qualified class name of the controller.
     * @param string $method The method to call on the controller.
     * @param string $type The HTTP method for the route (e.g., 'GET', 'POST').
     * @param string $url The API URL to register.
     */
    public static function api(string $controller, string $method, string $type, string $url): void
    {
        add_action('rest_api_init', function () use ($controller, $method, $type, $url) {
            register_rest_route('api/', $url, array(
                'methods'  => $type,
                'callback' => function () use ($controller, $method, $type) {
                    if (!in_array(strtoupper($_SERVER['REQUEST_METHOD']), array_map('strtoupper', (array)$type))) {
                        return new \WP_Error('invalid_method', 'Invalid HTTP Method', array('status' => 405));
                    }

                    if (!class_exists($controller) || !method_exists($controller, $method)) {
                        return new \WP_Error('not_found', 'Controller or Method Not Found', array('status' => 404));
                    }

                    $controllerInstance = new $controller();

                    try {
                        $data = $controllerInstance->$method();
                        return new \WP_REST_Response($data, 200);
                    } catch (\Exception $e) {
                        return new \WP_Error('internal_error', $e->getMessage(), array('status' => 500));
                    }
                },
            ));
        });
    }
}
