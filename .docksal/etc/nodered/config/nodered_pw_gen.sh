#!/usr/bin/env bash
SCRIPT_DIR="$(cd -- "$(dirname "$0")"; pwd -P)"

function nodered_pw_gen() {
    local NODERED_AUTH_ADMIN=$(htpasswd -nb -B -C 8 admin "$1")
    export NODERED_AUTH_ADMIN
    touch $SCRIPT_DIR/node-red.env
    envsubst '${NODERED_AUTH_ADMIN}' < $SCRIPT_DIR/node-red.tpl.env > $SCRIPT_DIR/node-red.env
    unset NODERED_AUTH_ADMIN
}

nodered_pw_gen $1
