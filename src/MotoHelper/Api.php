<?php

namespace MotoHelper;

use Silex\Application;
use MotoHelper\Helper\Cookie;
use Silex\ControllerProviderInterface;
use MotoHelper\Entity\AccessToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class Api implements ControllerProviderInterface
{
    private $noAuthCalls = ['login','login_post'];
    
    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];

        Controller\Api\EmpresaController::addRoutes($controllers);
        Controller\Api\VeiculoController::addRoutes($controllers);
        Controller\Api\VeiculoAutorizacaoController::addRoutes($controllers);
        Controller\Api\MotoboyController::addRoutes($controllers);
        Controller\Api\MotoboyVeiculoController::addRoutes($controllers);

        $controllers->before(function (Request $request) use ($app) {
            $uri    = $request->get('_route');
            
            if (in_array($uri, $this->noAuthCalls)) {
                return;
            }
            
            try {
                $serviceToken = new Services\AccessTokenService($app['orm.em']->getRepository(AccessToken::class));
                
                $token = $serviceToken->getToken(Cookie::getCookie($app, $request));
            
                if (is_null($token)) {
                    $token = $serviceToken->getToken(Cookie::getCookieApp($app, $request));
                    if(is_null($token)){
                        return $this->getResponseUnauthorized($app, $request);
                    }
                }
                
                $app['token'] = $token;
            } catch (\Exception $ex) {
                $app['logger']->critical($ex->getMessage());
                return $this->getErrorResponse($app, Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        });
        
        $app->after(function (Request $request, Response $response) use ($app) {

            $method = $request->getMethod();

            if ($method == 'OPTIONS') {
                return new Response('', Response::HTTP_NO_CONTENT);
            }
            
        });
        
        return $controllers;
    }
    
    private function getResponseUnauthorized(Application $app, Request $request)
    {
        $response = $app->redirect('/login');

        if (strpos($request->headers->get('Accept'), 'application/json') !== false) {
            $response = new JsonResponse(Response::$statusTexts[Response::HTTP_UNAUTHORIZED], Response::HTTP_UNAUTHORIZED);
        }
        
        return $response;
    }

    private function getErrorResponse(Application $app, $code)
    {
        $acceptJson = (strpos($app['request']->headers->get('Accept'), 'application/json') === false);
        
        if ($code == 404) {
            return new Response($app['twig']->render('errors/404.html.twig'),  Response::HTTP_NOT_FOUND);
        }
        
        if ($code == 500) {
            $response = new JsonResponse(Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
            if ($acceptJson) {
                $response = new Response($app['twig']->render('errors/500.html.twig'),  Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            return $response;
        }

        if ($code == 401) {
            return $this->getResponseUnauthorized($app, $app['request']);
        }
        
        return null;
    }
}