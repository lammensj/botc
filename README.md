# SPARC by Drupal

## Intro
SPARC stands for Solar Powered, Advanced, Renewable Control. In essense, it's an idea about the consumption of electricty, coming from solar panels, but without the need for cloud-connected devices. It can propose schedules, based on estimated output and the power requirements of a set of appliances.

It's become a tool which solves the question "How can I optimise my self-consumption of electricity?". By using Drupal, it leverages a set of modules for time-based storage, processing and logging the output.

### Disclaimer
I am not responsible, nor can I be kept accountable for any change that might occur in your home situation as a result of what you've learned here. If you suddenly find yourself in charge of the entire household because you found this valuable and wanted to try it yourself, that's on you 😉

# Setup
- `cp ./.docksal/etc/shared/mosquitto/mqttpasswd.tpl.txt ./.docksal/etc/shared/mosquitto/mqttpasswd.txt`
- `fin p up`

### MQTT
- `fin bash mosquitto`
- `mosquitto_passwd -c /shared/mosquitto/mqttpasswd.txt [USERNAME]`

### Node-RED
- https://shantanoo-desai.github.io/posts/technology/htpasswd-node-red-docker/
- `./.docksal/etc/nodered/config/nodered_pw_gen.sh [PASSWORD]`
