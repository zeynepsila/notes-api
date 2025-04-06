<?php

namespace App\Controllers;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AuthController
{
    protected $db;
    protected $secret = 'gizliAnahtar123'; 

    public function __construct($container)
{
    $this->db = $container['db'](); // ← dikkat! Fonksiyonu çağırdık
}


    public function register(Request $request, Response $response)
{
    $data = $request->getParsedBody();
    $username = $data['username'] ?? '';
    $password = $data['password'] ?? '';

    if (!$username || !$password) {
        $response->getBody()->write(json_encode([
            'error' => 'Kullanıcı adı ve şifre zorunludur.'
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);

    try {
        $stmt = $this->db->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->execute([$username, $hash]);

        $response->getBody()->write(json_encode([
            'message' => 'Kayıt başarılı'
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    } catch (\PDOException $e) {
        $error = $e->getCode() == 23000 ? 'Bu kullanıcı adı zaten kullanılıyor.' : $e->getMessage();
        $response->getBody()->write(json_encode(['error' => $error]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }
}
public function login(Request $request, Response $response)
    {
        $data = $request->getParsedBody();
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';

        if (!$username || !$password) {
            $response->getBody()->write(json_encode(['error' => 'Boş alan bırakmayınız.']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            $response->getBody()->write(json_encode(['error' => 'Hatalı kullanıcı adı veya şifre.']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        // Token oluştur
        $payload = [
            'id' => $user['id'],
            'username' => $user['username'],
            'iat' => time(),
            'exp' => time() + 3600 // 1 saat geçerli
        ];

        $jwt = JWT::encode($payload, $this->secret, 'HS256');

        $response->getBody()->write(json_encode(['token' => $jwt]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function me(Request $request, Response $response)
{
    $token = $request->getAttribute('token');

    $response->getBody()->write(json_encode([
        'id' => $token->id,
        'username' => $token->username
    ]));

    return $response->withHeader('Content-Type', 'application/json');
}


}
