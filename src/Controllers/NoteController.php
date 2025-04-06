<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class NoteController
{
    protected $db;

    public function __construct($container)
    {
        $this->db = $container['db']();
    }

    public function create(Request $request, Response $response)
    {
        $data = $request->getParsedBody();
        $content = $data['content'] ?? '';

        $token = $request->getAttribute('token');
        $userId = $token->id;

        if (!$content) {
            $response->getBody()->write(json_encode(['error' => 'Not içeriği boş olamaz.']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $stmt = $this->db->prepare("INSERT INTO notes (user_id, content) VALUES (?, ?)");
        $stmt->execute([$userId, $content]);

        $response->getBody()->write(json_encode(['message' => 'Not eklendi.']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    }

    public function list(Request $request, Response $response)
    {
        $token = $request->getAttribute('token');
        $userId = $token->id;

        $stmt = $this->db->prepare("SELECT id, content, created_at FROM notes WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$userId]);
        $notes = $stmt->fetchAll();

        $response->getBody()->write(json_encode($notes));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function update(Request $request, Response $response, array $args)
{
    $noteId = $args['id'];
    $data = $request->getParsedBody();
    $content = $data['content'] ?? '';

    $token = $request->getAttribute('token');
    $userId = $token->id;

    if (!$content) {
        $response->getBody()->write(json_encode(['error' => 'Not içeriği boş olamaz.']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    // Not gerçekten bu kullanıcıya mı ait?
    $stmt = $this->db->prepare("SELECT * FROM notes WHERE id = ? AND user_id = ?");
    $stmt->execute([$noteId, $userId]);
    $note = $stmt->fetch();

    if (!$note) {
        $response->getBody()->write(json_encode(['error' => 'Bu nota erişim yetkiniz yok.']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
    }

    // Güncelle
    $stmt = $this->db->prepare("UPDATE notes SET content = ? WHERE id = ?");
    $stmt->execute([$content, $noteId]);

    $response->getBody()->write(json_encode(['message' => 'Not güncellendi.']));
    return $response->withHeader('Content-Type', 'application/json');
}
public function delete(Request $request, Response $response, array $args)
{
    $noteId = $args['id'];
    $token = $request->getAttribute('token');
    $userId = $token->id;

    $stmt = $this->db->prepare("SELECT * FROM notes WHERE id = ? AND user_id = ?");
    $stmt->execute([$noteId, $userId]);
    $note = $stmt->fetch();

    if (!$note) {
        $response->getBody()->write(json_encode(['error' => 'Bu nota erişim yetkiniz yok.']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
    }

    $stmt = $this->db->prepare("DELETE FROM notes WHERE id = ?");
    $stmt->execute([$noteId]);

    $response->getBody()->write(json_encode(['message' => 'Not silindi.']));
    return $response->withHeader('Content-Type', 'application/json');
}
public function show(Request $request, Response $response, array $args)
{
    $noteId = $args['id'];
    $token = $request->getAttribute('token');
    $userId = $token->id;

    $stmt = $this->db->prepare("SELECT id, content, created_at FROM notes WHERE id = ? AND user_id = ?");
    $stmt->execute([$noteId, $userId]);
    $note = $stmt->fetch();

    if (!$note) {
        $response->getBody()->write(json_encode(['error' => 'Bu nota erişim yetkiniz yok.']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
    }

    $response->getBody()->write(json_encode($note));
    return $response->withHeader('Content-Type', 'application/json');
}

}
