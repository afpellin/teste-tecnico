<?php

namespace Controllers;

use Database\Connection;

class PostagemController {
    public static function index() {
        try {
            $pdo = Connection::getConnection();
            $stmt = $pdo->query("
                SELECT p.*, u.nome as autor
                FROM postagens p
                LEFT JOIN usuarios u ON p.usuario_id = u.id
                ORDER BY p.created_at DESC
            ");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log('PDOException in PostagemController::index: ' . $e->getMessage());
            return [];
        }
    }

    public static function show($id) {
        try {
            $pdo = Connection::getConnection();
            $stmt = $pdo->prepare("
                SELECT p.*, u.nome as autor
                FROM postagens p
                LEFT JOIN usuarios u ON p.usuario_id = u.id
                WHERE p.id = :id
            ");
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log('PDOException in PostagemController::show: ' . $e->getMessage());
            return null;
        }
    }

    public static function store($dados) {
        session_start();
        if (!isset($_SESSION['usuario'])) {
            return json_encode(['sucesso' => false, 'erro' => 'Usuário não autenticado']);
        }

        if (empty($dados['titulo']) || empty($dados['conteudo'])) {
            return json_encode(['sucesso' => false, 'erro' => 'Título e conteúdo são obrigatórios']);
        }

        try {
            $pdo = Connection::getConnection();
            $stmt = $pdo->prepare("
                INSERT INTO postagens (usuario_id, titulo, conteudo) 
                VALUES (:usuario_id, :titulo, :conteudo)
            ");
            
            $stmt->bindParam(':usuario_id', $_SESSION['usuario']['id'], \PDO::PARAM_INT);
            $stmt->bindParam(':titulo', $dados['titulo']);
            $stmt->bindParam(':conteudo', $dados['conteudo']);
            
            if ($stmt->execute()) {
                return json_encode([
                    'sucesso' => true,
                    'mensagem' => 'Postagem criada com sucesso!'
                ]);
            }
            
            return json_encode(['sucesso' => false, 'erro' => 'Erro ao criar postagem']);
            
        } catch (\PDOException $e) {
            error_log('PDOException in PostagemController::store: ' . $e->getMessage());
            return json_encode(['sucesso' => false, 'erro' => 'Erro no banco de dados']);
        }
    }

    public static function update($id, $dados) {
        session_start();
        if (!isset($_SESSION['usuario'])) {
            return json_encode(['sucesso' => false, 'erro' => 'Usuário não autenticado']);
        }

        try {
            $pdo = Connection::getConnection();
            
            // Verifica se o usuário é o autor da postagem
            $stmt = $pdo->prepare("SELECT usuario_id FROM postagens WHERE id = :id");
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
            $stmt->execute();
            $postagem = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$postagem || $postagem['usuario_id'] != $_SESSION['usuario']['id']) {
                return json_encode(['sucesso' => false, 'erro' => 'Não autorizado']);
            }

            $stmt = $pdo->prepare("
                UPDATE postagens 
                SET titulo = :titulo, conteudo = :conteudo 
                WHERE id = :id
            ");
            
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
            $stmt->bindParam(':titulo', $dados['titulo']);
            $stmt->bindParam(':conteudo', $dados['conteudo']);
            
            if ($stmt->execute()) {
                return json_encode([
                    'sucesso' => true,
                    'mensagem' => 'Postagem atualizada com sucesso!'
                ]);
            }
            
            return json_encode(['sucesso' => false, 'erro' => 'Erro ao atualizar postagem']);
            
        } catch (\PDOException $e) {
            error_log('PDOException in PostagemController::update: ' . $e->getMessage());
            return json_encode(['sucesso' => false, 'erro' => 'Erro no banco de dados']);
        }
    }

    public static function destroy($id) {
        session_start();
        if (!isset($_SESSION['usuario'])) {
            return json_encode(['sucesso' => false, 'erro' => 'Usuário não autenticado']);
        }

        try {
            $pdo = Connection::getConnection();
            
            // Verifica se o usuário é o autor da postagem
            $stmt = $pdo->prepare("SELECT usuario_id FROM postagens WHERE id = :id");
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
            $stmt->execute();
            $postagem = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$postagem || $postagem['usuario_id'] != $_SESSION['usuario']['id']) {
                return json_encode(['sucesso' => false, 'erro' => 'Não autorizado']);
            }

            $stmt = $pdo->prepare("DELETE FROM postagens WHERE id = :id");
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return json_encode([
                    'sucesso' => true,
                    'mensagem' => 'Postagem excluída com sucesso!'
                ]);
            }
            
            return json_encode(['sucesso' => false, 'erro' => 'Erro ao excluir postagem']);
            
        } catch (\PDOException $e) {
            error_log('PDOException in PostagemController::destroy: ' . $e->getMessage());
            return json_encode(['sucesso' => false, 'erro' => 'Erro no banco de dados']);
        }
    }
}