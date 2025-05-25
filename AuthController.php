<?php

namespace Controllers;

use Database\Connection;
use PDOException;
use Exception;

class AuthController
{
    public static function cadastrar($dados)
    {
        // Validação dos dados de entrada
        if (empty($dados['nome']) || empty($dados['email']) || empty($dados['senha'])) {
            return json_encode([
                'sucesso' => false,
                'mensagem' => 'Todos os campos são obrigatórios'
            ]);
        }

        // Validação do formato do email
        if (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
            return json_encode([
                'sucesso' => false,
                'mensagem' => 'Formato de email inválido'
            ]);
        }

        // Validação da força da senha
        if (strlen($dados['senha']) < 6) {
            return json_encode([
                'sucesso' => false,
                'mensagem' => 'A senha deve ter pelo menos 6 caracteres'
            ]);
        }

        try {
            $pdo = Connection::getConnection();

            // Verifica se email já existe
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = :email");
            $stmt->bindParam(':email', $dados['email']);
            
            if (!$stmt->execute()) {
                throw new Exception('Erro ao verificar email existente');
            }

            if ($stmt->fetch()) {
                return json_encode([
                    'sucesso' => false,
                    'mensagem' => 'Este email já está cadastrado'
                ]);
            }

            // Cria hash da senha
            $senhaHash = password_hash($dados['senha'], PASSWORD_BCRYPT);
            if (!$senhaHash) {
                throw new Exception('Erro ao gerar hash da senha');
            }

            // Insere novo usuário
            $stmt = $pdo->prepare("
                INSERT INTO usuarios (nome, email, senha) 
                VALUES (:nome, :email, :senha)
            ");
            
            $stmt->bindParam(':nome', $dados['nome']);
            $stmt->bindParam(':email', $dados['email']);
            $stmt->bindParam(':senha', $senhaHash);

            if ($stmt->execute()) {
                return json_encode([
                    'sucesso' => true,
                    'mensagem' => 'Usuário cadastrado com sucesso!',
                    'dados' => [
                        'nome' => $dados['nome'],
                        'email' => $dados['email']
                    ]
                ]);
            }

            throw new Exception('Erro ao executar inserção no banco de dados');

        } catch (PDOException $e) {
            error_log('PDOException in AuthController: ' . $e->getMessage());
            return json_encode([
                'sucesso' => false,
                'mensagem' => 'Erro no banco de dados',
                'debug' => (ENVIRONMENT === 'development') ? $e->getMessage() : null
            ]);
            
        } catch (Exception $e) {
            error_log('Exception in AuthController: ' . $e->getMessage());
            return json_encode([
                'sucesso' => false,
                'mensagem' => 'Erro ao processar cadastro',
                'debug' => (ENVIRONMENT === 'development') ? $e->getMessage() : null
            ]);
        }
    }

    public static function login($dados)
    {
        // Validação básica
        if (empty($dados['email']) || empty($dados['senha'])) {
            return json_encode([
                'sucesso' => false,
                'mensagem' => 'Email e senha são obrigatórios'
            ]);
        }

        try {
            $pdo = Connection::getConnection();

            // Busca usuário pelo email
            $stmt = $pdo->prepare("
                SELECT id, nome, email, senha 
                FROM usuarios 
                WHERE email = :email
                LIMIT 1
            ");
            
            $stmt->bindParam(':email', $dados['email']);
            
            if (!$stmt->execute()) {
                throw new Exception('Erro ao buscar usuário');
            }

            $usuario = $stmt->fetch();

            // Verifica credenciais
            if (!$usuario || !password_verify($dados['senha'], $usuario['senha'])) {
                return json_encode([
                    'sucesso' => false,
                    'mensagem' => 'Credenciais inválidas'
                ]);
            }

            // Cria sessão
            session_start();
            $_SESSION['usuario'] = [
                'id' => $usuario['id'],
                'nome' => $usuario['nome'],
                'email' => $usuario['email']
            ];

            return json_encode([
                'sucesso' => true,
                'mensagem' => 'Login realizado com sucesso!',
                'usuario' => [
                    'id' => $usuario['id'],
                    'nome' => $usuario['nome'],
                    'email' => $usuario['email']
                ]
            ]);

        } catch (PDOException $e) {
            error_log('PDOException in AuthController: ' . $e->getMessage());
            return json_encode([
                'sucesso' => false,
                'mensagem' => 'Erro no banco de dados'
            ]);
            
        } catch (Exception $e) {
            error_log('Exception in AuthController: ' . $e->getMessage());
            return json_encode([
                'sucesso' => false,
                'mensagem' => 'Erro ao processar login'
            ]);
        }
    }

    public static function verificarSessao()
    {
        session_start();
        return isset($_SESSION['usuario']);
    }

    public static function logout()
    {
        session_start();
        session_unset();
        session_destroy();
        return true;
    }
}