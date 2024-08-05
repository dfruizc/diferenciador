<?php
namespace App\Model\db_channel;

require_once __DIR__ . '/../../../vendor/autoload.php';
use RuntimeException;

class DbTelegram
{
    public function __construct()
    {
        try {
            
            //? Ruta del archivo de reporte
            $reportFile = __DIR__ . '/telegram.txt';

            //? Verificar si el archivo existe y leer su contenido
            if (!file_exists($reportFile)) {
                throw new RuntimeException("El archivo $reportFile no existe.");
            }

            $message = file_get_contents($reportFile);
            if ($message === false) {
                throw new RuntimeException("Error al leer el archivo $reportFile.");
            }

            //? Construir y ejecutar el comando bash
            $output = shell_exec('cd db_channel && bash db_channel "' . $message . '" 2>&1');
            //! Ejecutar la shell para el archivo de forma individual
            // $output = shell_exec('bash db_channel "' . $message . '" 2>&1');


            
            //? Verificar si shell_exec devolvió null
            if ($output === null) {
                throw new RuntimeException("Error al ejecutar el comando bash.");
            }

            //? Imprimir la salida del comando
            echo 'Output: ' . $output . PHP_EOL;

        } catch (RuntimeException $e) {
            //? Manejo de errores
            echo 'Ocurrió un error: ' . $e->getMessage() . PHP_EOL;
        }
    }
}

//! Instancia la clase para ejecutar el código de forma individual
// new DbTelegram();
