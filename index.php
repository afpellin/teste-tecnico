<?php
session_start();
require_once 'vendor/autoload.php';

use Controllers\AuthController;
use Controllers\PostagemController;

header('Content-Type: application/json');

$rota = $_GET['rota'] ?? '';

try {
    switch($rota) {
        case 'cadastro':
            echo AuthController::cadastrar($_POST);
            break;
        case 'login':
            echo AuthController::login($_POST);
            break;
        case 'postagens':
            echo json_encode(PostagemController::index());
            break;
        case 'postagem':
            $id = $_GET['id'] ?? null;
            if ($id) {
                echo json_encode(PostagemController::show($id));
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'ID não fornecido']);
            }
            break;
        case 'criar_postagem':
            echo PostagemController::store($_POST);
            break;
        case 'editar_postagem':
            $id = $_GET['id'] ?? null;
            if ($id) {
                echo PostagemController::update($id, $_POST);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'ID não fornecido']);
            }
            break;
        case 'excluir_postagem':
            $id = $_GET['id'] ?? null;
            if ($id) {
                echo PostagemController::destroy($id);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'ID não fornecido']);
            }
            break;
        case 'verifica_sessao':
            echo json_encode(['logado' => isset($_SESSION['usuario'])]);
            break;
        case 'logout':
            session_destroy();
            echo json_encode(['sucesso' => true]);
            break;
        default:
            echo json_encode(['mensagem' => 'API de Postagens']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro interno no servidor: ' . $e->getMessage()]);
}