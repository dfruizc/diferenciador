<?php
///traer la informacion de la base de datos a un modelo
namespace App\Model;

class enrolModel
{
    public static function getenrol(string $usuarioenroll)
    {
        //conexion replica 
        $host = $_ENV['HOST_REPLICA'];
        $port = $_ENV['PORT_REPLICA'];
        $dbname = $_ENV['DB_REPLICA'];
        $user = $_ENV['USER_REPLICA'];
        $password = $_ENV['PASS_REPLICA'];
        //verification connecton
        try {
            //Definición de conexión a la DB
            $conn = new \PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
            $conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            // consulta a la BD de integracion para traer los enrol pediente por crear en LMS   
            // $sql = "SELECT \"courseid\", COUNT(*) AS \"courseidTotal\" FROM \"INTEGRACION\".\"$usuarioenroll\" limit 100";
            /*$sql = "SELECT \"courseid\", COUNT(*) AS \"courseidTotal\" 
        FROM \"INTEGRACION\".\"$usuarioenroll\" --where \"courseid\"=528 
        WHERE \"courseid\" >= 1000
        GROUP BY \"courseid\" ORDER BY \"courseid\" 
        LIMIT 1000";*/
            $sql = "SELECT E.\"courseid\", COUNT(*) AS \"courseidTotal\"
            FROM \"INTEGRACION\".\"V_FICHA_CARACTERIZACION_B\" F
            JOIN \"TEMPORAL\".\"USUARIO_LMS_ENROLL_C\" E
            ON F.\"LMS_ID\" = E.\"courseid\"
            WHERE F.\"FIC_FCH_INICIALIZACION\" > to_date('2024-06-01','YYYY-MM-DD') AND E.\"LMS_ESTADO\" = 2
            GROUP BY E.\"courseid\"
            ORDER BY E.\"courseid\"";

            //var_dump($sql);

            //Se prepara y se ejecuta la query
            $stmt = $conn->prepare($sql);
            $stmt->execute();

            //Se recibe la respuesta
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            //Se retorna la respuesta de la DB
            return $result;
        } catch (\Throwable $th) { //Se captura el error
            throw $th; //Se arroja el error
        } finally {
            // Cerrar la conexión
            $conn = null;
        }
    }

    public static function getInstructor(string $usuarioenroll)
    {
        //conexion replica 
        $host = $_ENV['HOST_REPLICA'];
        $port = $_ENV['PORT_REPLICA'];
        $dbname = $_ENV['DB_REPLICA'];
        $user = $_ENV['USER_REPLICA'];
        $password = $_ENV['PASS_REPLICA'];
        //verification connecton
        try {
            //Definición de conexión a la DB
            $conn = new \PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
            $conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            $sql = "SELECT E.\"courseid\", EI.\"userid\"
            FROM \"INTEGRACION\".\"V_FICHA_CARACTERIZACION_B\" F
            JOIN \"TEMPORAL\".\"USUARIO_LMS_ENROLL_C\" E
            ON F.\"LMS_ID\" = E.\"courseid\"
            JOIN \"INTEGRACION\".\"USUARIO_LMS_ENROLL_C\" EI
            ON F.\"LMS_ID\" = EI.\"courseid\"
            WHERE F.\"FIC_FCH_INICIALIZACION\" > TO_DATE('2024-07-01','YYYY-MM-DD') AND EI.\"roleid\" = 3
            GROUP BY E.\"courseid\", EI.\"userid\"
            ORDER BY E.\"courseid\"";
            //var_dump($sql);

            //Se prepara y se ejecuta la query
            $stmt = $conn->prepare($sql);
            $stmt->execute();

            //Se recibe la respuesta
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            //Se retorna la respuesta de la DB
            return $result;
        } catch (\Throwable $th) { //Se captura el error
            throw $th; //Se arroja el error
        } finally {
            // Cerrar la conexión
            $conn = null;
        }
    }
}
