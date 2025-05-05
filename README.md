
# Proyecto Twitch Analytics

Este proyecto se encarga de obtener y mostrar datos de análisis de Twitch (usuarios, streams y streams enriquecidos) mediante la integración con la API de Twitch. Se ha implementado utilizando PHP y se despliega utilizando un servidor de Heroku.

## Grupo Mikados

Mikel Pio

Asier Larrayoz

Julen Pareja

Ainhoa Lara

## Requisitos

- **Servidor:** Heroku
- **Software:**
  - PHP
  - Git
- **Credenciales:**
  - Acceso a la cuenta de GitHub (para clonar el repositorio vía SSH o HTTPS)
  - Claves y tokens de la API de Twitch (clientId y clientSecret)

### 1. Trabajar en local

#### Requisitos PHP

Debemos tener habilitadas las siguientes extensiones en PHP:
+ curl
+ pdo_pgsql
+ pgsql

##### Modo de habilitar las extensiones

+ Accedemos a nuestro archivo php.ini, situado donde tenemos la descarga de php.
+ Buscamos las extensiones (apareceran como ;extension=curl)
+ Descomentamos las extensiones que necesitamos (borramos ;)

#### Lanzamiento

Para implementar una nueva funcionalidad programala en tu ordenador con tu programa de preferencia, nosotros hemos utilizado phpStorm. Una vez finalizado el código, para ver como funciona, lanza el programa en local de la siguiente forma:

+ Desde la terminal de windows: Accede al directorio donde estan los archivos y ejecuta el siguiente comando: docker compose up --build (por defecto se lanza en localhost:8000)
+ Desde la terminal de phpStorm: Directamente ejecutamos el comando: docker compose up --build

Para poder realizar los endpoints, nosotros hemos utilizado postman. Hemos configurado unas pruebas en postman para poder probar todos los endpoints fácilmente. Hemos configurado un entorno para local y otro para desarrollo. Asi, podremos realizar las pruebas tanto en local (con localhost:8000) como en desarrollo con el enlace de nuestra api.

### 3. Desplegar una funcionalidad

1. Cuando la funcionalidad este lista realiza las pruebas de postman en local.
2. Una vez realizadas las pruebas en local, subir los cambios a una rama del repositorio.
3. Realizar un pull request y esperar a que lo apruebe otra persona.

## Ejecución

### 1. Ejecución en local
Una vez hemos lanzado el servidor en local (docker compose up --build), podemos realizar estas pruebas para ver que funciona correctamente:
Antes de hacer nada, debemos registrarnos en la api, para ello realizamos la siguiente petición:
POST http://localhost:8000/register
Le debemos introducir un email en el body en formato JSON, es decir:
```
{
  "email": "tucorreo@gmail.com"
}
```
Esto nos devolvera una api_key la cual necesitaremos para generar un token de acceso a nuestra API.
Una vez hemos obtenido la api_key generamos el token con la siguiente petición:
POST http://localhost:8000/token
Le debemos introducir nuestro correo y api key en el body:
```
{
  "email": "tucorreo@gmail.com",
  "api_key": "tu_api_key"
}
```
Ahora ya tenemos el token el cual podemos utilizar para realizar pruebas. Cada vez que realicemos una petición GET a nuestra api, tendremos que pasarle el token como header de autorización, es decir:
Authorization Bearer token
Asi, podremos realizar distintas pruebas:
+ http://localhost:8000/analytics/user?id=39518378
+ http://localhost:8000/analytics/streams/
+ http://localhost:8000/analytics/streams/enriched?limit=3
+ http://localhost:8000/analytics/topsofthetops
+ http://localhost:8000/analytics/topsofthetops?since=40

Además de estas pruebas, también puedes probar a dejar vacios o escribir una letra en los parámetros id, limit y since para ver que se muestran los errores correctamente.

Nosotros hemos configurado unas pruebas de postman para no tener que introducir la petición cada vez.

### 2. Ejecución en servidor público

Nosotros hemos hecho uso de Postman para realizar las peticiones.
Debemos realizar los pasos igual que en local, es decir, registrarnos y obtener un token, para poder realizar peticiones a nuestra API, como por ejemplo:
+ Caso 1: https://twitch-analytics-mikados-6959f6772683.herokuapp.com/analytics/user?id=39518378
+ Caso 2: https://twitch-analytics-mikados-6959f6772683.herokuapp.com/analytics/streams
+ Caso 3: https://twitch-analytics-mikados-6959f6772683.herokuapp.com/analytics/streams/enriched?limit=3
+ https://twitch-analytics-mikados-6959f6772683.herokuapp.com/analytics/topsofthetops
+ https://twitch-analytics-mikados-6959f6772683.herokuapp.com/analytics/topsofthetops?since=40

Pruebas adicionales:

+ https://twitch-analytics-mikados-6959f6772683.herokuapp.com/analytics/user?id=a
+ https://twitch-analytics-mikados-6959f6772683.herokuapp.com/analytics/streams/enriched?limit=a
+ https://twitch-analytics-mikados-6959f6772683.herokuapp.com/analytics/streams/enriched?limit=-1
+ https://twitch-analytics-mikados-6959f6772683.herokuapp.com/analytics/topsofthetops?since=a
+ https://twitch-analytics-mikados-6959f6772683.herokuapp.com/analytics/topsofthetops?since=-5
