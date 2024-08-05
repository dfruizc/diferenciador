import subprocess
import os

REPORT_FILE = 'telegram.txt'

try:
    # Verifica si el archivo existe antes de intentar leerlo
    if not os.path.isfile(REPORT_FILE):
        raise FileNotFoundError(f"El archivo {REPORT_FILE} no existe.")

    # Define el mensaje que se desea enviar
    with open(REPORT_FILE, 'r') as file:
        message = file.read()

    # Construye el comando bash para ejecutar el script con el mensaje
    bash_command = f'bash db_channel "{message}"'

    try:
        # Ejecuta el comando usando subprocess.run
        result = subprocess.run(bash_command, shell=True, capture_output=True, text=True, check=True)

        # Imprime el código de retorno y la salida estándar
        print('Return code:', result.returncode)
        print('stdout:', result.stdout)
    except subprocess.CalledProcessError as e:
        # Manejo de errores si el comando falla
        print(f"El comando falló con el código de salida {e.returncode}")
        print(f"stderr: {e.stderr}")
    except Exception as e:
        # Manejo de otros errores posibles durante la ejecución del comando
        print(f"Ocurrió un error durante la ejecución del comando: {str(e)}")

except FileNotFoundError as e:
    # Manejo del error de archivo no encontrado
    print(str(e))
except IOError as e:
    # Manejo de errores de E/S al leer el archivo
    print(f"Ocurrió un error de E/S al leer el archivo: {str(e)}")
except Exception as e:
    # Manejo de cualquier otro error
    print(f"Ocurrió un error inesperado: {str(e)}")