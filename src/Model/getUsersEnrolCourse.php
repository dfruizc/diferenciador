<?php
require __DIR__ . '/../../vendor/autoload.php';
use App\Engine\loadEnv;
use App\Model\enrolModel;

class getUsersEnrolCourse
{
    private $chunkSize = 100; // Tamaño del lote para procesar solicitudes en bloque
    
    public function __construct()
    {
        new loadEnv();
    }
    
    public function getuserscourse()
    {
        $start_time = microtime(true);
        $file = 'reporte/result_c.csv';
        $file_dif = 'reporte/result_dif_c.csv';
        $BDP = enrolModel::getenrol("USUARIO_LMS_ENROLL_T");

        echo "Se inicia el proceso de validacion matriculados BD VS ZAJUNA\n";

        $header = ['Ficha', 'User ID', 'Role ID', 'Matriculados en BD', 'Matriculados en Zajuna', 'Instructor en BD', 'Instructor en Zajuna'];
        $fp = fopen($file, 'w');
        fputcsv($fp, $header);

        $fp_dif = fopen($file_dif, 'w');
        fputcsv($fp_dif, $header);

        $c = 0;
        $curl = curl_init();

        // Configurar CURL para mantener la conexión abierta
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_URL, $_ENV['API_ZAJUNA']);

        foreach (array_chunk($BDP, $this->chunkSize) as $chunk) {
            $responses = $this->batchRequest($chunk, $curl);

            foreach ($responses as $i => $response) {
                $res = json_decode($response, true);
                $courseid = $chunk[$i]['courseid'];
                $courseidTotal = $chunk[$i]['courseidTotal'];
                $courseidTotal = $chunk[$i]['courseidTotal'];
                $res2 = count($res);
                

                foreach ($res as $courses) {
                    $roleid = $courses['roles'][0]['roleid'];
                    $userid = $courses['id'];
                    
                    if ($roleid != 5) { 
                        $data = [
                            'Ficha' => $courseid,
                            'User ID diferente' => $userid,
                            'Role ID' => ($roleid == 19 ? 'Apoyo Formacion':'Otro') ||  ($roleid == 3 ? 'Instructor':'Otro instructor xd'),
                            'Matriculados en BD' => $courseidTotal,
                            'Matriculados en Zajuna' => $res2,
                            //'Instructor' =>  $roleid == 3 ? 'Black mamba' : "No es instructor"                           
                        ];
                        var_dump($data);
                        fputcsv($fp, $data);
                    }

                    // if ($roleid == 5) {
                    //     $data = [
                    //         'Ficha' => $courseid,
                    //         'User ID' => $userid,
                    //         'Role ID' => $roleid,
                    //         'Matriculados en BD' => $courseidTotal,
                    //         'Matriculados en Zajuna' => $res2
                    //     ];
                    //     var_dump($data);
                    //     fputcsv($fp, $data);
                    // }
                }

                if ($res2 < $courseidTotal) {
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
            }

            $c += count($chunk);
        }

        curl_close($curl);
        fclose($fp);
        fclose($fp_dif);
        $end_time = microtime(true);
        var_dump('Execution time' . round($end_time - $start_time, 2));
        echo "Proceso finalizo, se validaron $c fichas\n";
    }

    private function batchRequest($chunk, $curl)
    {
        $responses = [];
        foreach ($chunk as $registros) {
            $requestData = [
                'wstoken' => $_ENV['WSTOKEN'],
                'wsfunction' => $_ENV['GET_COURSE_USERS'],
                'courseid' => $registros['courseid'],
                'moodlewsrestformat' => 'json'
            ];

            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($requestData));
            $responses[] = curl_exec($curl);
        }

        return $responses;
    }
}

$a = new getUsersEnrolCourse();
$a->getuserscourse();

