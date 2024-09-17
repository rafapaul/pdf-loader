<?php
// Define a pasta onde estão os arquivos PDF
$folder = "pasta_pdfs";

// Define o caminho para o arquivo XML
$xmlFile = 'lista.xml';

// Função para buscar o nome da unidade de saúde pelo ID
function getUnitNameById($id, $xmlFile) {
    if (file_exists($xmlFile)) {
        $xml = simplexml_load_file($xmlFile);
        foreach ($xml->Unidade as $unidade) {
            $unitId = (string)$unidade->id; // Corrigido para minúsculas
            // Remove zeros à esquerda para comparação
            $unitId = ltrim($unitId, '0');
            if ($unitId === $id) {
                return (string)$unidade->nome; // Corrigido para minúsculas
            }
        }
    }
    return 'Nome da unidade não encontrado.';
}

// Verifica se há uma solicitação de PDF específica
if (isset($_GET['pdf'])) {
    $pdfName = $_GET['pdf'];

    // Extrai o ID do nome do arquivo
    $idMatch = preg_match('/^(\d+)_/', $pdfName, $matches);
    if ($idMatch) {
        $id = $matches[1];
        // Remove zeros à esquerda para comparação
        $id = ltrim($id, '0');
        // Busca o nome da unidade de saúde pelo ID
        $unitName = getUnitNameById($id, $xmlFile);
        // Retorna o ID, nome da unidade e nome do arquivo PDF
        echo json_encode([
            'id' => $id,
            'unit_name' => $unitName,
            'pdf_name' => $pdfName
        ]);
    } else {
        echo json_encode([
            'id' => 'ID não encontrado.',
            'unit_name' => 'Nome da unidade não encontrado.',
            'pdf_name' => $pdfName
        ]);
    }
} else {
    // Verifica se a pasta existe
    if (is_dir($folder)) {
        $files = scandir($folder);
        // Remove "." e ".." da lista
        $pdf_files = array_diff($files, array('.', '..'));

        // Filtra apenas arquivos .pdf e inclui nome da unidade
        $pdf_files_with_unit = [];
        foreach ($pdf_files as $file) {
            if (strtolower(pathinfo($file, PATHINFO_EXTENSION)) === 'pdf') {
                // Extrai o ID do nome do arquivo
                $idMatch = preg_match('/^(\d+)_/', $file, $matches);
                if ($idMatch) {
                    $id = ltrim($matches[1], '0');
                    $unitName = getUnitNameById($id, $xmlFile);
                    $pdf_files_with_unit[] = [
                        'file' => $file,
                        'unit_name' => $unitName
                    ];
                } else {
                    $pdf_files_with_unit[] = [
                        'file' => $file,
                        'unit_name' => 'Nome da unidade não encontrado.'
                    ];
                }
            }
        }

        // Retorna a lista como JSON
        echo json_encode($pdf_files_with_unit);
    } else {
        // Se a pasta não existir, retorna um array vazio
        echo json_encode([]);
    }
}
?>
