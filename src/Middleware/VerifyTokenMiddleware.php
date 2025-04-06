<?php

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Psr\Http\Message\ResponseInterface as Response;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class VerifyTokenMiddleware
{
    protected $secret = 'gizliAnahtar123';

    public function __invoke(Request $request, Handler $handler): Response
    {
        $authHeader = $request->getHeaderLine('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode(['error' => 'Yetkisiz. Token gerekli.']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        $token = str_replace('Bearer ', '', $authHeader);

        try {
            $decoded = JWT::decode($token, new Key($this->secret, 'HS256'));
            // Token'dan gelen kullanıcıyı request'e ekle
            $request = $request->withAttribute('token', $decoded);
        } catch (\Exception $e) {
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode(['error' => 'Geçersiz token.']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        return $handler->handle($request);
    }
}
