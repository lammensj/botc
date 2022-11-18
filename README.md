# Faraday

## Setup
- `cp ./.env.example ./.env`
- `cp ./shared/mosquitto/mqttpasswd.txt.example ./shared/mosquitto/mqttpasswd.txt`
- `docker-compose up -d`

### MQTT
- `docker-compose exec mosquitto /bin/sh`
- `mosquitto_passwd -c /shared/mosquitto/mqttpasswd.txt [USERNAME]`

### Node-RED
- https://shantanoo-desai.github.io/posts/technology/htpasswd-node-red-docker/
- `./nodered/config/nodered_pw_gen.sh [PASSWORD]`
