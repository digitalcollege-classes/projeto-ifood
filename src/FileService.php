<?php

declare(strict_types=1);

class FileService
{
    public static function save(string $filename): FileImport
    {
        $partes = explode('_', strtolower($filename));

        //isolar apenas o nome da empresa
        $empresaPartes = explode('/', $partes[0]);
        $empresa = array_pop($empresaPartes);
                
        //isolar apenas o tipo de processo (entrada ou saida)
        $tipo = explode('.', $partes[2])[0];
                
        // separando a data
        $ano = substr($partes[1], 0, 4);
        $mes = substr($partes[1], 4, 2);
        $dia = substr($partes[1], -2);
        
        $caminho = "data/{$empresa}/{$tipo}/{$ano}/{$mes}/{$dia}";
        
        //criando a pasta da empresa
        if (!is_dir($caminho)) {
            mkdir(
                directory: $caminho, 
                recursive: true
            );   
        } 
        
        $arquivoFinal = date('Y-m-d_His');
        
        //copiar o arquivo para a nova pasta
        copy(
            from: $filename,
            to: "{$caminho}/{$arquivoFinal}.csv"
        );
        
        return new FileImport($empresa, $tipo);
    }
    
    public static function remove(string $empresa, array $matriculasToDelete): void {
    
        $colaboradoresFilePath = "data/{$empresa}/colaboradores.csv";    

        $colaboradoresFile = fopen($colaboradoresFilePath, "a+");

        $linesToKeep = [];

        $lines = file($colaboradoresFilePath);

        foreach ($lines as $line) {
            
            $matricula = explode(';', $line)[0];
        
            if (!in_array($matricula, $matriculasToDelete)) {
                $linesToKeep[] = $line;
            }
        }
        
        ftruncate($colaboradoresFile, 0);

        foreach ($linesToKeep as $line) {
            fwrite($colaboradoresFile, $line);
        }

        fclose($colaboradoresFile);

        echo "Linhas removidas com sucesso." . PHP_EOL;
    }

    public static function getMatriculas($empresa, $nomeDoArquivo) {
        $matriculasToDelete = [];
        $linhas = file($nomeDoArquivo);
        foreach ($linhas as $linha) {
            if ($empresa === 'digitalcollege') {
                $partes = explode(';', $linha);
            } else {
                $partes = explode(',', $linha);
            }
            $matricula = $partes[0];
            $matriculasToDelete[] = $matricula;
        }
        return $matriculasToDelete;
    }
}
