
# Proyecto Twitch Analytics

Este proyecto se encarga de obtener y mostrar datos de análisis de Twitch (usuarios, streams y streams enriquecidos) mediante la integración con la API de Twitch. Se ha implementado utilizando PHP y se despliega en un servidor Apache alojado en AWS Lightsail.

## Grupo Mikados

Mikel Pio

Asier Larrayoz

Julen Pareja

Ainhoa Lara

## Requisitos

- **Servidor:** Acceso a AWS Lightsail
- **Software:**
  - Apache
  - PHP (con cURL habilitado)
  - Git
- **Credenciales:**
  - Acceso a la cuenta de GitHub (para clonar el repositorio vía SSH o HTTPS)
  - Claves y tokens de la API de Twitch (clientId y clientSecret)

## Instalación y Despliegue

### 1. Conectarse a la instancia

Accede a Amazon Lightsail y después accede a la instancia. 

Iniciar una nueva conexion desde la pagina web. Inmediatamente despues aparecera la terminal del servidor, donde podemos ver los archivos de configuración del servidor y obtener la información actualizada realizando un git pull.

Además de conectarse por la terminal, puedes usar tu propio cliente SSH. Por ejemplo, puedes descargar filezilla y obtener la clave ssh de la instancia para que sea más cómodo trabajar.

### 2. Trabajar en local

Para implementar una nueva funcionalidad programala en tu ordenador con tu programa de preferencia, nosotros hemos utilizado phpStorm. Una vez finalizado el código, para ver como funciona, lanza el programa en local de la siguiente forma:

+ Desde la terminal de windows: Accede al directorio donde estan los archivos y ejecuta el siguiente comando: php -S localhost:12000 (12000 es el puerto donde quieres que se lance el servidor)
+ Desde la terminal de phpStorm: Directamente ejecutamos el comando php -S localhost:12000

### 3. Desplegar una funcionalidad

1. Cuando la funcionalidad este lista subala al repositorio.
2. Conectate a la terminal del servidor público desde amazon lighsail y realiza un pull del repositorio para que se apliquen los cambios.

### 4. (Opcional) Trabajar en desarrollador

Puedes realizar una nueva funcionalidad, desplegarla, y probar como funciona en el servidor público. Esto no es muy conveniente, ya que, si algo falla, el fallo va a estar en el servidor público mientrás realizas las pruebas, por lo que, todos los usuarios tendrán problemas.

## Ejecución

### 1. Ejecución en local
Una vez hemos lanzado el servidor en local, podemos realizar estas pruebas para ver que funciona correctamente:
+ http://localhost:12000/analytics/user?id=39518378
+ http://localhost:12000/analytics/streams/
+ http://localhost:12000/analytics/streams/enriched?limit=3

Además de estas pruebas, también puedes probar a dejar vacios o escribir una letra en los parámetros id y limit para ver que se muestran los errores correctamente.

### 2. Ejecución en servidor público
+ Caso 1: http://35.180.210.32/analytics/user?id=39518378
+ Caso 2: http://35.180.210.32/analytics/streams
+ Caso 3: http://35.180.210.32/analytics/streams/enriched?limit=3

Pruebas adicionales:

+ http://35.180.210.32/analytics/user?id=a
+ http://35.180.210.32/analytics/streams/enriched?limit=a
+ http://35.180.210.32/analytics/streams/enriched?limit=-1
