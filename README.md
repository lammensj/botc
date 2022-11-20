# Faraday

## Setup
- `cp ./.docksal/etc/shared/mosquitto/mqttpasswd.txt.example ./.docksal/etc/shared/mosquitto/mqttpasswd.txt`
- `fin p up`

### MQTT
- `fin bash mosquitto`
- `mosquitto_passwd -c /shared/mosquitto/mqttpasswd.txt [USERNAME]`

### Node-RED
- https://shantanoo-desai.github.io/posts/technology/htpasswd-node-red-docker/
- `./.docksal/etc/nodered/config/nodered_pw_gen.sh [PASSWORD]`
