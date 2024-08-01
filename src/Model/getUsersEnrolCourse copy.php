<?php
require __DIR__ . '/../../vendor/autoload.php';
//namespace App\Model;
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
        $file = 'result_c.txt';
        $file_dif = 'result_dif_c.txt';
        $BDP = enrolModel::getenrol("USUARIO_LMS_ENROLL_C");

        //$r= count($BDP);
        echo "Se inicia el proceso de validacion matriculados BD VS ZAJUNA  n\n";
        $resultbd = array();
        $c = 0;

        foreach ($BDP as $registros) {
            $resultbd[] = $registros['courseid'];
            $resultbdidtotal[] = $registros['courseidTotal'];

            $requestData = [
                'wstoken' => $_ENV['WSTOKEN'],
                'wsfunction' => $_ENV['GET_COURSE_USERS'],
                'courseid' => $resultbd[$c],
                'moodlewsrestformat' => 'json'

            ];

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $_ENV['API_ZAJUNA']);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($requestData));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($curl);
            $res = json_decode($response, true);
            $datos = array();
            $datorol = array();
            //if ($response != null) {
                $res2 = count($res);

                $cr = 0;
                foreach ($res as $courses) {
                    $datos[] = $courses['roles'][0]['roleid'];
                    //var_dump($datos[$cr]);
                    $datorol[] = $courses['id'];
                    if ($datos[$cr] != 5  and $datos[$cr] != 3) {

                        echo "*************** Ficha # $resultbd[$c] ******************\n\n";
                        echo "cantidad de matriculados en la base de datos $resultbdidtotal[$c]\n\n";
                        $arch = "El usuario con ID :  $datorol[$cr]  tiene un rol diferente numero:  $datos[$cr]\n\n";
                        echo $arch;
                        echo ("numero de matriculados en zajuna..... $res2\n\n");
                        file_put_contents($file, 'Ficha N°: ' . $resultbd[$c] . ': ' . $arch, FILE_APPEND);
                    }
                    $cr++;
                }
                if ($res2 < $resultbdidtotal[$c] && $res2 = 0) {

                    $ficha = "*************** Ficha # $resultbd[$c] ******************\n\n";
                    $users_bd = "* cantidad de matriculados en la base de datos $resultbdidtotal[$c]\n\n";
                    $users_zajuna = ("* numero de matriculados en zajuna..... $res2\n\n");
                    echo $ficha;
                    echo $users_bd;
                    echo $users_zajuna;

                    file_put_contents($file_dif, 'Ficha N°: ' . $ficha . $users_bd . $users_zajuna, FILE_APPEND);
                }

                $c++;
            //}
        }

        echo ("Proceso finalizo, se validaron..... $c  fichas\n\n");
    }
}
$a = new getUsersEnrolCourse;
$a->getuserscourse();
