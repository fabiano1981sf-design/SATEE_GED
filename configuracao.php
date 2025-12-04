<?php
/**
 * ARQUIVO: configuracao.php
 * DESCRIÇÃO: Interface para gerenciamento de categorias do sistema GED.
 */

// =================================================================
// 1. INCLUSÃO DE ARQUIVOS BASE
// =================================================================
require_once('config.php');
require_once('auth.php');
require_once('conexao.php');  // Fornece o objeto $pdo

if (!isset($pdo) || !($pdo instanceof PDO)) {
    die("Erro Crítico: O objeto de conexão \$pdo não está disponível.");
}

$mensagem_status = '';

// =================================================================
// 2. LÓGICA DE CADASTRO E EXCLUSÃO DE CATEGORIA (PDO)
// =================================================================

// Lógica de EXCLUSÃO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['excluir_id'])) {
    
    $excluir_id = (int)$_POST['excluir_id'];
    
    try {
        // Tenta excluir. A FOREIGN KEY em documentos_ged pode bloquear isso.
        $sql = "DELETE FROM categorias_ged WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([':id' => $excluir_id])) {
            if ($stmt->rowCount() > 0) {
                $mensagem_status = '<div class="alerta-sucesso">Categoria excluída com sucesso!</div>';
            } else {
                $mensagem_status = '<div class="alerta-erro">Erro: Categoria não encontrada ou ID inválido.</div>';
            }
        }
        
    } catch (\PDOException $e) {
        // Código 23000 é geralmente violação de FOREIGN KEY (Restrição de Integridade)
        if ($e->getCode() == 23000) {
             $mensagem_status = '<div class="alerta-erro">**ERRO DE INTEGRIDADE**: Esta categoria possui documentos associados. Exclua ou reclassifique os documentos antes de excluir a categoria.</div>';
        } else {
             $mensagem_status = '<div class="alerta-erro">Erro ao excluir: ' . $e->getMessage() . '</div>';
        }
    }
}


// Lógica de CADASTRO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nova_categoria']) && !isset($_POST['excluir_id'])) {
    
    $nova_categoria = trim($_POST['nova_categoria']);
    
    if (empty($nova_categoria)) {
        $mensagem_status = '<div class="alerta-erro">O nome da categoria não pode ser vazio.</div>';
    } else {
        try {
            $sql = "INSERT INTO categorias_ged (nome) VALUES (:nome)";
            $stmt = $pdo->prepare($sql);
            
            if ($stmt->execute([':nome' => $nova_categoria])) {
                $mensagem_status = '<div class="alerta-sucesso">Categoria **' . htmlspecialchars($nova_categoria) . '** cadastrada com sucesso!</div>';
            }
            
        } catch (\PDOException $e) {
            if ($e->getCode() == 23000) {
                 $mensagem_status = '<div class="alerta-erro">Erro: A categoria **' . htmlspecialchars($nova_categoria) . '** já existe.</div>';
            } else {
                 $mensagem_status = '<div class="alerta-erro">Erro ao cadastrar: ' . $e->getMessage() . '</div>';
            }
        }
    }
}


// =================================================================
// 3. RECUPERAR LISTA DE CATEGORIAS EXISTENTES (PDO)
// =================================================================
$categorias_list = [];
try {
    $stmt = $pdo->query("SELECT id, nome FROM categorias_ged ORDER BY nome ASC");
    $categorias_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (\PDOException $e) {
    $mensagem_status .= '<div class="alerta-erro">Erro ao carregar lista de categorias.</div>';
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>SATEE - Configurações do GED</title>
    <style>
        /* CSS BÁSICO */
        .alerta-sucesso { background-color: #d4edda; color: #155724; padding: 10px; margin-bottom: 15px; border: 1px solid #c3e6cb; border-radius: 4px; }
        .alerta-erro { background-color: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 15px; border: 1px solid #f5c6cb; border-radius: 4px; }
        .container { width: 90%; max-width: 1200px; margin: 20px auto; padding: 20px; border: 1px solid #ddd; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input[type="text"] { width: 100%; max-width: 400px; padding: 10px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; display: inline-block;}
        .form-group button { padding: 10px 15px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; margin-left: 10px; }
        table { width: 500px; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
        th { background-color: #e9ecef; }
        .btn-excluir { background-color: #dc3545; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; font-size: 0.9em; }
        
        /* LAYOUT (Para funcionar com Sidebar) */
        #main-wrapper { overflow: auto; width: 100%; }
        #sidebar-wrapper { float: left; width: 250px; } 
        #content-wrapper { margin-left: 260px; padding: 10px 20px; }
    </style>
    
    <script>
        // Função de confirmação para exclusão
        function confirmarExclusao(nome) {
            return confirm('Tem certeza que deseja EXCLUIR a categoria "' + nome + '"? Esta ação não pode ser desfeita e falhará se houver documentos associados.');
        }
    </script>
</head>
<body>

<?php 
include('header.php'); 
?>

<div id="main-wrapper">
    
    <div id="sidebar-wrapper">
        <?php 
        include('sidebar.php'); 
        ?>
    </div>

    <div id="content-wrapper">
        <div class="container">
            <h1><i class="fa fa-cog"></i> Configurações do GED</h1>
            
            <?php echo $mensagem_status; ?>

            <hr>
            
            <h2>Cadastro de Categoria</h2>
            <form method="POST">
                <div class="form-group">
                    <label for="nova_categoria">Nome da Categoria:</label>
                    <input type="text" name="nova_categoria" id="nova_categoria" required placeholder="Ex: Contratos, Portarias, Tutoriais">
                    <button type="submit">Cadastrar</button>
                </div>
            </form>
            
            <hr>

            <h2>Categorias Existentes</h2>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Ação</th> </tr>
                </thead>
                <tbody>
                    <?php
                    if (count($categorias_list) > 0) {
                        foreach($categorias_list as $cat) {
                            echo "<tr>";
                            echo "<td>{$cat['id']}</td>";
                            echo "<td>" . htmlspecialchars($cat['nome']) . "</td>";
                            
                            // Novo formulário para EXCLUSÃO
                            echo "<td>
                                <form method='POST' onsubmit='return confirmarExclusao(\"" . addslashes($cat['nome']) . "\");'>
                                    <input type='hidden' name='excluir_id' value='{$cat['id']}'>
                                    <button type='submit' class='btn-excluir'>Excluir</button>
                                </form>
                            </td>";
                            
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='3'>Nenhuma categoria cadastrada.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    
</div>
<?php 
// include('footer.php'); 
?>

</body>
</html>