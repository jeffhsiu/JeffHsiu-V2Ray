#!/bin/bash

# 參數處理
ip="$1"
index="$2"
path="$3"
alter_id=100
if [[ ${index:0:1} == "R" ]]
then
    index_start=${index:1:1}
    index_end=${index:3}
else
    index_start=${index}
    index_end=${index}
fi

create_v2ray_config()
{
	cat << EOF > /usr/local/etc/v2ray/config.json
{
    "log": {
        "access": "/var/log/v2ray/access.log",
        "error": "/var/log/v2ray/error.log",
        "loglevel": "warning"
    },
    "inbound": {
        "port": ${port},
        "protocol": "vmess",
        "settings": {
            "clients": [
                {
                    "id": "${v2ray_id}",
                    "level": 1,
                    "alterId": ${alter_id}
                }
            ]
        }
    },
    "outbound": {
        "protocol": "freedom",
        "settings": {}
    },
    "inboundDetour": [],
    "outboundDetour": [
        {
            "protocol": "blackhole",
            "settings": {},
            "tag": "blocked"
        }
    ],
    "routing": {
        "strategy": "rules",
        "settings": {
            "rules": [
                {
                    "type": "field",
                    "ip": [
                        "0.0.0.0/8",
                        "10.0.0.0/8",
                        "100.64.0.0/10",
                        "127.0.0.0/8",
                        "169.254.0.0/16",
                        "172.16.0.0/12",
                        "192.0.0.0/24",
                        "192.0.2.0/24",
                        "192.168.0.0/16",
                        "198.18.0.0/15",
                        "198.51.100.0/24",
                        "203.0.113.0/24",
                        "::1/128",
                        "fc00::/7",
                        "fe80::/10"
                    ],
                    "outboundTag": "blocked"
                }
            ]
        }
    }
}
EOF
}

create_vmess_URL_config()
{
	cat << EOF > /usr/local/etc/v2ray/vmess_qr.json
{
	"v": "2",
	"ps": "wechat:fastrabbit666-${index}",
	"add": "${ip}",
	"port": "${port}",
	"id": "${v2ray_id}",
	"aid": "${alter_id}",
	"net": "tcp",
	"type": "none",
	"host": "",
	"path": "",
	"tls": ""
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
 傳輸協議 (Network) = tcp
 偽裝類型 (header type) = none
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

for i in `seq ${index_start} ${index_end}`
do
    rm -f /usr/local/etc/v2ray/vmess_qr.json /usr/local/etc/v2ray/config.json
    index=`echo ${i}|awk '{printf("%02d\n",$0)}'`
    v2ray_id=$(uuidgen | tr "[:upper:]" "[:lower:]")
    port=`expr 5550 + ${index}`
    create_v2ray_config
    create_vmess_URL_config
    base64_vmess
    export_config
    export_qrcode
done
