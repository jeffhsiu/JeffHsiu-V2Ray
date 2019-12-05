#!/bin/bash

# 參數處理
ip="$1"
port="$2"
index="$3"
path="$4"
host="$5"
alter_id=100
v2ray_id=$(uuidgen | tr "[:upper:]" "[:lower:]")

create_v2ray_config()
{
	cat << EOF > /usr/local/etc/v2ray/config.json
{
    "inbounds": [
    {
        "port": ${port},
        "protocol": "vmess",
        "settings": {
            "clients": [
            {
                "id": "${v2ray_id}",
                "alterId": ${alter_id}
            }
            ]
        },
        "streamSettings": {
            "network": "ws",
            "security": "tls",
            "tlsSettings": {
                "serverName": "${host}",
                "allowInsecure": true,
                "certificates": [
                {
                    "certificateFile": "/etc/v2ray/ssl/*.jeffhsiu.com_chain.crt",
                    "keyFile": "/etc/v2ray/ssl/*.jeffhsiu.com_key.key"
                }
                ]
            },
            "wsSettings": {
                "path": "/",
                "headers": {}
            }
        },
        "tag": "",
        "sniffing": {
            "enabled": true,
            "destOverride": [
            "http",
            "tls"
            ]
        }
    }
    ],
    "outbounds": [
    {
        "protocol": "freedom",
        "settings": {}
    },
    {
        "protocol": "blackhole",
        "settings": {},
        "tag": "blocked"
    }
    ],
    "routing": {
        "rules": [
        {
            "ip": [
            "geoip:private"
            ],
            "outboundTag": "blocked",
            "type": "field"
        },
        {
            "outboundTag": "blocked",
            "protocol": [
            "bittorrent"
            ],
            "type": "field"
        }
        ]
    }
}
EOF
}

create_vmess_URL_config()
{
	cat << EOF > /usr/local/etc/v2ray/vmess_qr.json
{
    "v": "2",
    "ps": "${index}-wechat:fastrabbit666",
    "add": "${ip}",
    "port": "${port}",
    "id": "${v2ray_id}",
    "aid": "${alter_id}",
    "net": "ws",
    "type": "none",
    "host": "${host}",
    "path": "/",
    "tls": "tls",
    "method": "auto",
    "allowInsecure": true,
    "security": "tls",
    "streamSettings": {
        "tlsSettings": {
                "allowInsecure": true
            }
    }
}
EOF
}

base64_vmess()
{
    if [[ "$OSTYPE" == "linux-gnu" ]]; then
        # Linux
        vmess="vmess://$(cat /usr/local/etc/v2ray/vmess_qr.json | base64 -w 0)"
    elif [[ "$OSTYPE" == "darwin"* ]]; then
        # Mac OSX
        vmess="vmess://$(cat /usr/local/etc/v2ray/vmess_qr.json | base64 -b 0)"
    else
        # Unknown.
        vmess="vmess://$(cat /usr/local/etc/v2ray/vmess_qr.json | base64 -w 0)"
    fi
}

export_config()
{
	if [ ! -d ${path} ]; then
		mkdir -p ${path}
	fi

    cat << EOF > ${path}/config-${index}.txt
------------- V2Ray 配置信息 -------------
 地址 (Address) = ${ip}
 端口 (Port) = ${port}
 用户ID (User ID / UUID) = ${v2ray_id}
 額外ID (Alter Id) = ${alter_id}
 傳輸協議 (Network) = ws + tls
 偽裝類型 (header type) = none
 域名 (host) = ${host}
 路徑 (path) = /
 TLS允許不安全 (allowInsecure) = true
------------------ END ------------------


---------- V2Ray vmess URL -------------
${vmess}
EOF
}

export_qrcode()
{
	if [ ! -d ${path} ]; then
		mkdir -p ${path}
	fi

	link="http://api.jeffhsiu.com/qrcode/create?size=460&error=Q&text=${vmess}"
	wget -O ${path}/qrcode-${index}.png ${link}
}

rm -f /usr/local/etc/v2ray/vmess_qr.json /usr/local/etc/v2ray/config.json
create_v2ray_config
create_vmess_URL_config
base64_vmess
export_config
export_qrcode
