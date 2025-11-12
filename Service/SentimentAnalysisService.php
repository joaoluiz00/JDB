<?php

/**
 * Service de Análise de Sentimento usando IA
 * 
 * Este serviço utiliza a API do Google Gemini para:
 * 1. Analisar sentimento de comentários (POSITIVO, NEGATIVO, NEUTRO)
 * 2. Gerar resumos textuais de múltiplos comentários
 * 
 * CONFIGURAÇÃO NECESSÁRIA:
 * - Obter chave API gratuita em: https://makersuite.google.com/app/apikey
 * - Definir a chave na constante GEMINI_API_KEY abaixo
 */
class SentimentAnalysisService {
    
    // CONFIGURAÇÃO: Insira sua chave API do Google Gemini aqui
    private const GEMINI_API_KEY = 'AIzaSyDQ3ITZjpbFV6wMqcGfL1vGWxfO36XZm5E';
    private const GEMINI_API_URL = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent';
    
    /**
     * Analisa o sentimento de um comentário
     * @param string $comentario O texto do comentário a ser analisado
     * @return string 'POSITIVO', 'NEGATIVO' ou 'NEUTRO'
     */
    public function analisarSentimento($comentario) {
        // Validação básica
        if (empty($comentario) || strlen(trim($comentario)) < 3) {
            return 'NEUTRO';
        }

        // Verifica se a API está configurada
        if (self::GEMINI_API_KEY === 'AIzaSyDQ3ITZjpbFV6wMqcGfL1vGWxfO36XZm5E') {
            // Fallback: análise simples baseada em palavras-chave
            return $this->analisarSentimentoSimples($comentario);
        }

        try {
            $prompt = "Analise o sentimento do seguinte comentário de produto e responda APENAS com uma das palavras: POSITIVO, NEGATIVO ou NEUTRO.\n\nComentário: \"" . $comentario . "\"\n\nSentimento:";
            
            $response = $this->chamarGeminiAPI($prompt);
            
            if ($response) {
                $sentimento = strtoupper(trim($response));
                // Valida a resposta
                if (in_array($sentimento, ['POSITIVO', 'NEGATIVO', 'NEUTRO'])) {
                    return $sentimento;
                }
            }
            
            // Fallback em caso de erro
            return $this->analisarSentimentoSimples($comentario);
            
        } catch (Exception $e) {
            error_log("Erro na análise de sentimento: " . $e->getMessage());
            return $this->analisarSentimentoSimples($comentario);
        }
    }

    /**
     * Gera um resumo textual de múltiplos comentários
     * @param array $comentarios Array de comentários
     * @return string Resumo gerado ou mensagem padrão
     */
    public function gerarResumo($comentarios) {
        if (empty($comentarios)) {
            return "Nenhuma avaliação disponível ainda.";
        }

        if (count($comentarios) === 1) {
            return "Há apenas 1 avaliação para este produto.";
        }

        // Verifica se a API está configurada
        if (self::GEMINI_API_KEY === 'AIzaSyDQ3ITZjpbFV6wMqcGfL1vGWxfO36XZm5E') {
            return $this->gerarResumoSimples($comentarios);
        }

        try {
            $textoComentarios = implode("\n- ", array_map(function($c) {
                return substr($c, 0, 200); // Limita tamanho
            }, $comentarios));

            $prompt = "Você é um assistente que analisa avaliações de produtos. Crie um resumo conciso e objetivo (máximo 150 palavras) dos seguintes comentários de clientes sobre um produto:\n\n- " . $textoComentarios . "\n\nResumo:";
            
            $response = $this->chamarGeminiAPI($prompt);
            
            if ($response && strlen($response) > 10) {
                return $response;
            }
            
            return $this->gerarResumoSimples($comentarios);
            
        } catch (Exception $e) {
            error_log("Erro ao gerar resumo: " . $e->getMessage());
            return $this->gerarResumoSimples($comentarios);
        }
    }

    /**
     * Chama a API do Google Gemini
     * @param string $prompt O prompt a ser enviado
     * @return string|null A resposta da API ou null em caso de erro
     */
    private function chamarGeminiAPI($prompt) {
        $url = self::GEMINI_API_URL . '?key=' . self::GEMINI_API_KEY;
        
        $data = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.3,
                'maxOutputTokens' => 500
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && $response) {
            $json = json_decode($response, true);
            if (isset($json['candidates'][0]['content']['parts'][0]['text'])) {
                return trim($json['candidates'][0]['content']['parts'][0]['text']);
            }
        }

        return null;
    }

    /**
     * Análise de sentimento simples baseada em palavras-chave (fallback)
     * @param string $comentario
     * @return string
     */
    private function analisarSentimentoSimples($comentario) {
        $comentario = strtolower($comentario);
        
        $palavrasPositivas = [
            'ótimo', 'otimo', 'excelente', 'maravilhoso', 'perfeito', 'adorei', 'amei',
            'bom', 'boa', 'legal', 'bacana', 'top', 'incrível', 'recomendo', 'qualidade',
            'satisfeito', 'feliz', 'melhor', 'super', 'gostei', 'lindo', 'bonito'
        ];
        
        $palavrasNegativas = [
            'ruim', 'péssimo', 'pessimo', 'horrível', 'terrível', 'decepcionante',
            'não gostei', 'nao gostei', 'fraco', 'defeito', 'problema', 'odiei',
            'insatisfeito', 'pior', 'lixo', 'arrependido', 'não recomendo', 'nao recomendo'
        ];

        $pontuacaoPositiva = 0;
        $pontuacaoNegativa = 0;

        foreach ($palavrasPositivas as $palavra) {
            if (strpos($comentario, $palavra) !== false) {
                $pontuacaoPositiva++;
            }
        }

        foreach ($palavrasNegativas as $palavra) {
            if (strpos($comentario, $palavra) !== false) {
                $pontuacaoNegativa++;
            }
        }

        if ($pontuacaoPositiva > $pontuacaoNegativa) {
            return 'POSITIVO';
        } elseif ($pontuacaoNegativa > $pontuacaoPositiva) {
            return 'NEGATIVO';
        } else {
            return 'NEUTRO';
        }
    }

    /**
     * Gera resumo simples baseado em estatísticas (fallback)
     * @param array $comentarios
     * @return string
     */
    private function gerarResumoSimples($comentarios) {
        $total = count($comentarios);
        $tamanhoMedio = 0;
        
        foreach ($comentarios as $comentario) {
            $tamanhoMedio += strlen($comentario);
        }
        $tamanhoMedio = round($tamanhoMedio / $total);

        return "Este produto possui {$total} avaliações de clientes. " .
               "Os comentários variam em extensão e conteúdo, fornecendo diferentes perspectivas sobre o produto. " .
               "Configure a API do Google Gemini para obter resumos mais detalhados e inteligentes.";
    }

    /**
     * Verifica se a API está configurada
     * @return bool
     */
    public static function isApiConfigurada() {
        return self::GEMINI_API_KEY !== 'AIzaSyDQ3ITZjpbFV6wMqcGfL1vGWxfO36XZm5E';
    }
}
