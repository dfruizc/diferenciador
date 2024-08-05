<?php
use App\Model\db_channel\DbTelegram;
require __DIR__ . '/../../vendor/autoload.php';
# Version 1.0 12 julio 2024
use App\Engine\loadEnv;
use App\Model\enrolModel;

class getUsersEnrolCourse
{
    public function __construct()
    {
        new loadEnv();
    }

    public function getuserscourse()
    {
        try {
            //code...
            $start_time = microtime(true);
            $file = 'reporte/result_roles_extra.csv';
            $file_dif = 'reporte/result_pendientes_matricula.csv';
            $BDP = enrolModel::getenrol("USUARIO_LMS_ENROLL_T");

            echo "Se inicia el proceso de validacion matriculados BD VS ZAJUNA\n";

            // Crear archivos CSV y agregar encabezados
            $header = ['Ficha', 'User ID', 'Role ID', 'Matriculados en BD', 'Matriculados en Zajuna'];
            $fp = fopen($file, 'w');
            fputcsv($fp, $header);

            $fp_dif = fopen($file_dif, 'w');
            fputcsv($fp_dif, $header);

            $c = 0;
            $conditionCounter = 0;
            foreach ($BDP as $registros) {
                $courseid = $registros['courseid'];
                $courseidTotal = $registros['courseidTotal'];

                $requestData = [
                    'wstoken' => $_ENV['WSTOKEN'],
                    'wsfunction' => $_ENV['GET_COURSE_USERS'],
                    'courseid' => $courseid,
                    'moodlewsrestformat' => 'json'
                ];

                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, $_ENV['API_ZAJUNA']);
                curl_setopt($curl, CURLOPT_POST, 1);
                curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($requestData));
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

                $response = curl_exec($curl);
                $res = json_decode($response, true);
                $res2 = count($res);

                foreach ($res as $courses) {
                    $roleid = $courses['roles'][0]['roleid'];
                    $userid = $courses['id'];

                    if ($roleid != 5) {

                        $roleDescription = '';
                        if ($roleid == 19) {
                            $roleDescription = 'Acompañamiento Formacion';
                        } elseif ($roleid == 3) {
                            $roleDescription = 'Instructor';
                        } elseif ($roleid == 18) {
                            $roleDescription = 'Facilitador Acompañamiento';
                        } else {
                            $roleDescription = 'Otro';
                        }
                        $data = [
                            'Ficha' => $courseid,
                            'User ID rol diferente' => $userid,
                            'Role ID' => $roleDescription,
                            'Matriculados en BD' => $courseidTotal,
                            'Matriculados en Zajuna' => $res2,
                        ];

                        var_dump($data);
                        fputcsv($fp, $data);
                    }
                }

                if ($res2 < $courseidTotal || $res2 == 0) {
                    $conditionCounter++;
                    $data_dif = [
                        'Ficha' => $courseid,
                        'User ID' => '',
                        'Role ID' => '',
                        'Matriculados en BD' => $courseidTotal,
                        'Matriculados en Zajuna' => $res2
                    ];
                    var_dump($data_dif);
                    fputcsv($fp_dif, $data_dif);
                }

                $c++;
            }

            fclose($fp);
            fclose($fp_dif);
            $end_time = microtime(true);
            var_dump('Execution time: ' . round($end_time - $start_time, 2));
            echo "Proceso finalizado, se validaron $c fichas\n";
            $dif_matriculados ="la cantidad de cursos con número de usuarios matriculados inferior al de base de datos es $conditionCounter";
            echo "$dif_matriculados\n";
            if ($conditionCounter >= 0) {            
                $archivo = 'db_channel/telegram.txt';
                $fileHandle = fopen($archivo, 'w');
                // Verificar si se pudo abrir el archivo
                if ($fileHandle) {
                    // Escribir el contenido de la variable en el archivo
                    fwrite($fileHandle, $dif_matriculados);
                    // Cerrar el archivo
                    fclose($fileHandle);
            
                    echo "El archivo se ha creado exitosamente.";
                } else {
                    echo "No se pudo abrir el archivo para escritura.";
                }
            }
  ;
            sleep(5);
            shell_exec('python3 ./upload_reports.py');
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
}

$a = new getUsersEnrolCourse();
$a->getuserscourse();
$instanciate = new DbTelegram();