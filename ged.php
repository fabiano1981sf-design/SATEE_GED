<?php
/**
 * ARQUIVO: ged.php
 * DESCRIÇÃO: Gerenciamento Eletrônico de Documentos (GED)
 */

// =================================================================
// 1. INCLUSÃO DE ARQUIVOS BASE
// =================================================================
require_once('config.php');   
require_once('auth.php');     
require_once('conexao.php');  // Este arquivo deve fornecer o objeto $pdo

if (!isset($pdo) || !($pdo instanceof PDO)) {
    die("Erro Crítico: O objeto de conexão \$pdo não está disponível.");
}

// Diretório onde os arquivos serão salvos no servidor
define('UPLOAD_DIR', 'documentos/ged/'); 

if (!is_dir(UPLOAD_DIR)) {
    if (!mkdir(UPLOAD_DIR, 0755, true)) {
        die("Erro: Não foi possível criar o diretório de upload. Verifique as permissões de pasta.");
    }
}


// =================================================================
// 2. LÓGICA DE EXCLUSÃO DE DOCUMENTOS (PDO)
// =================================================================

$mensagem_status = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['excluir_documento_id'])) {
    
    $documento_id = (int)$_POST['excluir_documento_id'];
    
    try {
        // 1. Recupera o caminho do arquivo no servidor para poder deletá-lo
        $stmt_select = $pdo->prepare("SELECT caminho_arquivo FROM documentos_ged WHERE id = :id");
        $stmt_select->execute([':id' => $documento_id]);
        $documento = $stmt_select->fetch();

        if ($documento) {
            $caminho_arquivo = $documento['caminho_arquivo'];

            // 2. Exclui o registro do banco de dados
            $stmt_delete = $pdo->prepare("DELETE FROM documentos_ged WHERE id = :id");
            $stmt_delete->execute([':id' => $documento_id]);
            
            if ($stmt_delete->rowCount() > 0) {
                
                // 3. Exclui o arquivo físico do servidor
                if (file_exists($caminho_arquivo) && unlink($caminho_arquivo)) {
                    $mensagem_status = '<div class="alerta-sucesso">Documento e arquivo excluídos com sucesso!</div>';
                } else {
                    $mensagem_status = '<div class="alerta-sucesso">Documento excluído do banco, mas o arquivo físico não foi encontrado/removido.</div>';
                }
            } else {
                $mensagem_status = '<div class="alerta-erro">Erro: Documento não encontrado no banco de dados.</div>';
            }
        } else {
            $mensagem_status = '<div class="alerta-erro">Erro: Documento não encontrado para exclusão.</div>';
        }

    } catch (\PDOException $e) {
        $mensagem_status = '<div class="alerta-erro">Erro ao excluir documento: ' . $e->getMessage() . '</div>';
    }
}


// =================================================================
// 3. LÓGICA DE UPLOAD DE DOCUMENTOS (PDO Prepared Statements)
// =================================================================
// (Mantida a lógica de upload anterior para evitar que o código seja quebrado)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['arquivo_upload'])) {
    
    $id_categoria = (int)$_POST['id_categoria'];
    $descricao    = $_POST['descricao'];

    $arquivo = $_FILES['arquivo_upload'];
    $nome_original = $arquivo['name'];
    $extensao = strtolower(pathinfo($nome_original, PATHINFO_EXTENSION));
    $nome_salvo = uniqid('GED_') . '_' . time() . '.' . $extensao; 
    $caminho_completo = UPLOAD_DIR . $nome_salvo;

    $extensoes_permitidas = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'];
    
    if (!in_array($extensao, $extensoes_permitidas)) {
        $mensagem_status = '<div class="alerta-erro">Erro: Tipo de arquivo **não permitido**.</div>';
    } elseif ($arquivo['error'] !== UPLOAD_ERR_OK) {
        $mensagem_status = '<div class="alerta-erro">Erro no upload: Código ' . $arquivo['error'] . '</div>';
    } else {
        if (move_uploaded_file($arquivo['tmp_name'], $caminho_completo)) {
            try {
                $sql = "INSERT INTO documentos_ged 
                        (nome_original, nome_salvo, caminho_arquivo, id_categoria, descricao) 
                        VALUES (:nome_original, :nome_salvo, :caminho_arquivo, :id_categoria, :descricao)";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':nome_original' => $nome_original,
                    ':nome_salvo' => $nome_salvo,
                    ':caminho_arquivo' => $caminho_completo,
                    ':id_categoria' => $id_categoria,
                    ':descricao' => $descricao
                ]);
                
                $mensagem_status = '<div class="alerta-sucesso">Documento **' . htmlspecialchars($nome_original) . '** salvo com sucesso!</div>';
                
            } catch (\PDOException $e) {
                unlink($caminho_completo);
                $mensagem_status = '<div class="alerta-erro">Erro ao salvar no banco (DB): ' . $e->getMessage() . '</div>';
            }
        } else {
            $mensagem_status = '<div class="alerta-erro">Erro ao mover o arquivo. Verifique as permissões de pasta.</div>';
        }
    }
}


// =================================================================
// 4. RECUPERAR DADOS PARA EXIBIÇÃO
// =================================================================
// (Mantida a lógica de recuperação de dados)
$categorias_options = "";
try {
    $stmt_cat = $pdo->query("SELECT id, nome FROM categorias_ged ORDER BY nome");
    while ($cat = $stmt_cat->fetch()) {
        $categorias_options .= "<option value=\"{$cat['id']}\">{$cat['nome']}</option>";
    }
} catch (\PDOException $e) { /* ignore */ }

$documentos_result = [];
try {
    $sql_documentos = "SELECT d.id, d.nome_original, d.descricao, d.data_upload, d.caminho_arquivo, c.nome as categoria 
                       FROM documentos_ged d
                       JOIN categorias_ged c ON d.id_categoria = c.id
                       ORDER BY d.data_upload DESC";
    
    $stmt_doc = $pdo->query($sql_documentos);
    $documentos_result = $stmt_doc->fetchAll(PDO::FETCH_ASSOC);
    
} catch (\PDOException $e) {
    $mensagem_status .= '<div class="alerta-erro">Erro ao carregar documentos.</div>';
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>SATEE - Gerenciamento Eletrônico de Documentos (GED)</title>
    <style>
        /* CSS BÁSICO DO LAYOUT E ESTILOS */
        #main-wrapper { overflow: auto; width: 100%; }
        #sidebar-wrapper { float: left; width: 250px; } 
        #content-wrapper { margin-left: 260px; padding: 10px 20px; }
        .container { width: 100%; max-width: 900px; margin: 20px auto; padding: 20px; border: 1px solid #ddd; }
        h1 { border-bottom: 2px solid #007bff; padding-bottom: 10px; }
        .alerta-sucesso { background-color: #d4edda; color: #155724; padding: 10px; margin-bottom: 15px; border: 1px solid #c3e6cb; border-radius: 4px; }
        .alerta-erro { background-color: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 15px; border: 1px solid #f5c6cb; border-radius: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border: 1px solid #ddd; }
        th { background-color: #e9ecef; }
        
        /* Estilo dos ícones de ação na tabela */
        .btn-acao { 
            display: inline-block; 
            padding: 5px 8px; 
            margin: 0 2px;
            border-radius: 4px;
            text-decoration: none;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 1em; /* Ajuste o tamanho do ícone */
        }
        .btn-download { background-color: #28a745; } /* Verde para Download */
        .btn-excluir { background-color: #dc3545; } /* Vermelho para Excluir */

        /* MODAL CSS (Mantido o CSS do popup) */
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.4); }
        .modal-content { background-color: #fefefe; margin: 15% auto; padding: 20px; border: 1px solid #888; width: 80%; max-width: 400px; text-align: center; border-radius: 8px; }
        .close-btn { color: #aaa; float: right; font-size: 28px; font-weight: bold; }
        .close-btn:hover, .close-btn:focus { color: #000; text-decoration: none; cursor: pointer; }
    </style>
    
    <script>
        // Função para mostrar o modal de confirmação
        function confirmarExclusao(id, nome) {
            document.getElementById('modal-nome-documento').textContent = nome;
            
            if(document.getElementById('form-upload')) {
                document.getElementById('form-upload').reset(); 
            }
            
            document.getElementById('input-excluir-id').value = id;
            
            document.getElementById('confirm-modal').style.display = 'block';
        }

        // Função para fechar o modal
        function fecharModal() {
            document.getElementById('confirm-modal').style.display = 'none';
        }

        // Fecha o modal se o usuário clicar fora dele
        window.onclick = function(event) {
            var modal = document.getElementById('confirm-modal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
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
            <h1><i class="fa fa-envelope"></i> Gerenciamento Eletrônico de Documentos (GED)</h1>
            
            <?php echo $mensagem_status; ?>

            <hr>
            
            <h2><span style="color: #007bff;">1.</span> Upload de Novo Documento</h2>
            <form method="POST" enctype="multipart/form-data" id="form-upload">
                <div class="form-group">
                    <label for="arquivo_upload">Arquivo:</label>
                    <input type="file" name="arquivo_upload" id="arquivo_upload" required>
                    <small>Extensões permitidas: PDF, DOCX, XLSX, JPG, PNG.</small>
                </div>
                
                <div class="form-group">
                    <label for="id_categoria">Categoria:</label>
                    <select name="id_categoria" id="id_categoria" required>
                        <option value="">-- Selecione uma Categoria --</option>
                        <?php echo $categorias_options; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="descricao">Descrição:</label>
                    <textarea name="descricao" id="descricao" rows="2" placeholder="Ex: Contrato de 2024 - Turma A"></textarea>
                </div>
                
                <button type="submit" style="background-color: #007bff; color: white; padding: 12px 20px; border: none; border-radius: 4px; cursor: pointer;">
                    Fazer Upload e Arquivar
                </button>
            </form>
            
            <hr>

            <h2><span style="color: #007bff;">2.</span> Documentos Arquivados</h2>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome do Arquivo</th>
                        <th>Categoria</th>
                        <th>Data Upload</th>
                        <th>Ações</th> </tr>
                </thead>
                <tbody>
                    <?php
                    if (count($documentos_result) > 0) {
                        foreach($documentos_result as $doc) {
                            $download_url = $doc['caminho_arquivo']; 

                            echo "<tr>";
                            echo "<td>{$doc['id']}</td>";
                            echo "<td>" . htmlspecialchars($doc['nome_original']) . "</td>";
                            echo "<td>{$doc['categoria']}</td>";
                            echo "<td>" . date("d/m/Y H:i", strtotime($doc['data_upload'])) . "</td>";
                            
                            // Coluna AÇÕES com ícones
                            echo "<td>";
                            
                            // Botão/Link de Download (Ícone de download/nuvem)
                            echo "<a href='{$download_url}' class='btn-acao btn-download' title='Baixar Documento' target='_blank'>";
                            echo "<i class='fa fa-download' aria-hidden='true'></i>"; 
                            echo "</a>";

                            // Botão de Excluir (Ícone de lixeira) que chama o modal
                            echo "<button type='button' class='btn-acao btn-excluir' title='Excluir Documento'
                                onclick='confirmarExclusao({$doc['id']}, \"" . addslashes(htmlspecialchars($doc['nome_original'])) . "\")'>";
                            echo "<i class='fa fa-trash' aria-hidden='true'></i>";
                            echo "</button>";
                            
                            echo "</td>";
                            
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5'>Nenhum documento arquivado.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    
</div>
<div id="confirm-modal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="fecharModal()">&times;</span>
        <h3><i class='fa fa-exclamation-triangle' style='color: #ffc107;'></i> Confirmação de Exclusão</h3>
        <p>Você tem certeza que deseja **EXCLUIR** o documento:</p>
        <p><strong><span id="modal-nome-documento" style="color: #dc3545;"></span></strong></p>
        <p>Esta ação removerá o registro do banco de dados e o arquivo físico.</p>
        
        <form method="POST" style="margin-top: 20px;">
            <input type="hidden" name="excluir_documento_id" id="input-excluir-id" value="">
            <button type="button" onclick="fecharModal()" style="padding: 10px 15px; margin-right: 10px; background-color: #6c757d; color: white; border: none; border-radius: 4px;">Cancelar</button>
            <button type="submit" style="padding: 10px 15px; background-color: #dc3545; color: white; border: none; border-radius: 4px;">Confirmar Exclusão</button>
        </form>
    </div>
</div>

<?php 
// include('footer.php'); 
?>

</body>
</html>