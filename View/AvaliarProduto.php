<?php
session_start();
if (!isset($_SESSION['id'])) { 
    header('Location: Index.php'); 
    die(); 
}

require_once __DIR__ . '/../Controller/ControllerAvaliacao.php';

$controller = new ControllerAvaliacao();
$userId = $_SESSION['id'];

// Obtém parâmetros
$tipoItem = $_GET['tipo'] ?? null;
$idItem = $_GET['id'] ?? null;

if (!$tipoItem || !$idItem) {
    header('Location: Home.php');
    die();
}

// Valida tipo de item
if (!in_array($tipoItem, ['carta', 'pacote', 'icone', 'papel_fundo'])) {
    header('Location: Home.php');
    die();
}

// Verifica se usuário comprou o produto
if (!$controller->usuarioComprouProduto($userId, $tipoItem, $idItem)) {
    $erro = "Você precisa comprar este produto antes de avaliá-lo.";
}

// Verifica se já avaliou
if ($controller->usuarioJaAvaliou($userId, $tipoItem, $idItem)) {
    $erro = "Você já avaliou este produto.";
}

// Obtém nome do produto
$nomeProduto = $controller->resolveItemNome($tipoItem, $idItem);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avaliar Produto - JDB</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="../Assets/style.css">
    <link rel="stylesheet" href="../Assets/avaliacoes.css">
    <style>
        .rating-stars {
            font-size: 3rem;
            color: #ddd;
            cursor: pointer;
            user-select: none;
        }
        .rating-stars .star {
            display: inline-block;
            transition: color 0.2s;
        }
        .rating-stars .star.active,
        .rating-stars .star:hover,
        .rating-stars .star:hover ~ .star {
            color: #ffc107;
        }
        .preview-images {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }
        .preview-item {
            position: relative;
            width: 100px;
            height: 100px;
        }
        .preview-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 5px;
        }
        .preview-item .remove-btn {
            position: absolute;
            top: -5px;
            right: -5px;
            background: red;
            color: white;
            border: none;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            cursor: pointer;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="Home.php">JDB</a>
        <div class="ml-auto">
            <a href="HistoricoCompras.php" class="btn btn-secondary">Voltar</a>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h3><i class="fas fa-star"></i> Avaliar Produto</h3>
                    </div>
                    <div class="card-body">
                        <?php if (isset($erro)): ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i> <?php echo $erro; ?>
                            </div>
                            <a href="HistoricoCompras.php" class="btn btn-primary">Voltar ao Histórico</a>
                        <?php else: ?>
                            <h5 class="mb-3">Produto: <strong><?php echo htmlspecialchars($nomeProduto); ?></strong></h5>
                            
                            <form id="formAvaliacao" enctype="multipart/form-data">
                                <input type="hidden" name="tipo_item" value="<?php echo htmlspecialchars($tipoItem); ?>">
                                <input type="hidden" name="id_item" value="<?php echo htmlspecialchars($idItem); ?>">
                                <input type="hidden" name="nota" id="notaInput" value="0">

                                <!-- Avaliação por estrelas -->
                                <div class="form-group">
                                    <label>Nota:</label>
                                    <div class="rating-stars" id="ratingStars">
                                        <span class="star" data-value="1">★</span>
                                        <span class="star" data-value="2">★</span>
                                        <span class="star" data-value="3">★</span>
                                        <span class="star" data-value="4">★</span>
                                        <span class="star" data-value="5">★</span>
                                    </div>
                                    <small class="form-text text-muted" id="notaTexto">Selecione uma nota de 1 a 5 estrelas</small>
                                </div>

                                <!-- Comentário -->
                                <div class="form-group">
                                    <label for="comentario">Comentário: <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="comentario" name="comentario" rows="5" 
                                              placeholder="Compartilhe sua experiência com este produto (mínimo 10 caracteres)" 
                                              required minlength="10"></textarea>
                                    <small class="form-text text-muted">
                                        <span id="contadorCaracteres">0</span>/500 caracteres
                                    </small>
                                </div>

                                <!-- Upload de imagens -->
                                <div class="form-group">
                                    <label for="imagens">Imagens (opcional):</label>
                                    <input type="file" class="form-control-file" id="imagens" name="imagens[]" 
                                           accept="image/*" multiple>
                                    <small class="form-text text-muted">
                                        Você pode adicionar até 5 imagens (máx. 5MB cada)
                                    </small>
                                    <div class="preview-images" id="previewImages"></div>
                                </div>

                                <div id="mensagemErro" class="alert alert-danger d-none"></div>
                                <div id="mensagemSucesso" class="alert alert-success d-none"></div>

                                <button type="submit" class="btn btn-primary btn-lg btn-block" id="btnEnviar">
                                    <i class="fas fa-paper-plane"></i> Enviar Avaliação
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            let notaSelecionada = 0;
            let imagensFiles = [];

            // Sistema de estrelas
            $('.star').hover(
                function() {
                    const value = $(this).data('value');
                    highlightStars(value);
                },
                function() {
                    highlightStars(notaSelecionada);
                }
            ).click(function() {
                notaSelecionada = $(this).data('value');
                $('#notaInput').val(notaSelecionada);
                highlightStars(notaSelecionada);
                
                const textos = ['Péssimo', 'Ruim', 'Regular', 'Bom', 'Excelente'];
                $('#notaTexto').text('Nota: ' + notaSelecionada + ' - ' + textos[notaSelecionada - 1]);
            });

            function highlightStars(value) {
                $('.star').each(function() {
                    if ($(this).data('value') <= value) {
                        $(this).addClass('active');
                    } else {
                        $(this).removeClass('active');
                    }
                });
            }

            // Contador de caracteres
            $('#comentario').on('input', function() {
                const length = $(this).val().length;
                $('#contadorCaracteres').text(length);
                if (length > 500) {
                    $(this).val($(this).val().substring(0, 500));
                    $('#contadorCaracteres').text(500);
                }
            });

            // Preview de imagens
            $('#imagens').on('change', function(e) {
                const files = Array.from(e.target.files);
                imagensFiles = files.slice(0, 5); // Máximo 5 imagens
                
                $('#previewImages').empty();
                imagensFiles.forEach((file, index) => {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const preview = $(`
                            <div class="preview-item">
                                <img src="${e.target.result}" alt="Preview">
                                <button type="button" class="remove-btn" data-index="${index}">×</button>
                            </div>
                        `);
                        $('#previewImages').append(preview);
                    };
                    reader.readAsDataURL(file);
                });
            });

            // Remover imagem do preview
            $(document).on('click', '.remove-btn', function() {
                const index = $(this).data('index');
                imagensFiles.splice(index, 1);
                
                // Atualiza o input file
                const dt = new DataTransfer();
                imagensFiles.forEach(file => dt.items.add(file));
                document.getElementById('imagens').files = dt.files;
                
                $(this).parent().remove();
            });

            // Submit do formulário
            $('#formAvaliacao').on('submit', function(e) {
                e.preventDefault();

                // Validações
                if (notaSelecionada === 0) {
                    mostrarErro('Por favor, selecione uma nota de 1 a 5 estrelas.');
                    return;
                }

                const comentario = $('#comentario').val().trim();
                if (comentario.length < 10) {
                    mostrarErro('O comentário deve ter pelo menos 10 caracteres.');
                    return;
                }

                // Envia o formulário
                const formData = new FormData(this);
                
                $('#btnEnviar').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Enviando...');

                $.ajax({
                    url: '../Processamento/ProcessAvaliacao.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            mostrarSucesso(response.message + '<br><br>' +
                                '<a href="VisualizarAvaliacoes.php?tipo=<?php echo $tipoItem; ?>&id=<?php echo $idItem; ?>" class="btn btn-info mt-3">' +
                                '<i class="fas fa-comments"></i> Ver Avaliações deste Produto</a> ' +
                                '<a href="HistoricoCompras.php" class="btn btn-secondary mt-3">' +
                                '<i class="fas fa-history"></i> Voltar ao Histórico</a>');
                            
                            // Limpar formulário
                            $('#formAvaliacao')[0].reset();
                            notaSelecionada = 0;
                            highlightStars(0);
                            $('#notaTexto').text('Selecione uma nota de 1 a 5 estrelas');
                            $('#previewImages').empty();
                            imagensFiles = [];
                            $('#contadorCaracteres').text('0');
                            
                            // Desabilitar botão de envio (já avaliou)
                            $('#btnEnviar').prop('disabled', true).html('<i class="fas fa-check"></i> Avaliação Enviada!');
                        } else {
                            mostrarErro(response.message);
                            $('#btnEnviar').prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Enviar Avaliação');
                        }
                    },
                    error: function() {
                        mostrarErro('Erro ao enviar avaliação. Tente novamente.');
                        $('#btnEnviar').prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Enviar Avaliação');
                    }
                });
            });

            function mostrarErro(mensagem) {
                $('#mensagemErro').text(mensagem).removeClass('d-none');
                $('#mensagemSucesso').addClass('d-none');
                $('html, body').animate({ scrollTop: $('#mensagemErro').offset().top - 100 }, 500);
            }

            function mostrarSucesso(mensagem) {
                $('#mensagemSucesso').html(mensagem).removeClass('d-none');
                $('#mensagemErro').addClass('d-none');
                $('html, body').animate({ scrollTop: $('#mensagemSucesso').offset().top - 100 }, 500);
            }
        });
    </script>
</body>
</html>
