
# 1 - Generar token a partir de nuestros datos de la api de twitch
    Datos twitch ASIER:
    asier@DESKTOP-PF9F0K5 MINGW64 ~
    $ curl -X POST "https://id.twitch.tv/oauth2/token" \
     -d "client_id=fdibhmkdzf1qf2zxf1ztmmp7ae7rlc" \
     -d "client_secret=t2of8gr7z8lue0bqokqozd9ndsoah9" \
     -d "grant_type=client_credentials"
    "access_token":"qg2qn7475t9t1e3k4oiqb4kp0uevqn","expires_in":5340687,"token_type":"bearer"}
    Datos twitch AINHOA:
    Ainhoa@MSI-AINHOA MINGW64 ~
    $ curl -X POST "https://id.twitch.tv/oauth2/token" \
     -d "client_id=myqofuuwfbtja3s178j5x7osa78xfh" \
     -d "client_secret=37cyiep3n136cgkupqnym38ifi5x1a" \
     -d "grant_type=client_credentials"
    "access_token":"c9v255gz2xi5xij9293ei6vaob999x","expires_in":5135961,"token_type":"bearer"}
Se ha generado un token, que es el que utilizaremos para poder utilizar la api de twitch, junto con nuestro id de cliente de twitch.

# 2 - Realización de pruebas en postman
# 3 - Generar códigos PHP


