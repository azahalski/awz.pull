# AWZ: Push and Pull (awz.pull)

### [Установка модуля](https://github.com/azahalski/awz.pull/tree/main/docs/install.md)

<!-- desc-start -->

Модуль содержит Api для организации ролевой модели прав доступа.

**Поддерживаемые редакции CMS Битрикс:**<br>
«Первый сайт», «Старт», «Стандарт», «Малый бизнес», «Эксперт», «Бизнес», «Корпоративный портал», «Энтерпрайз», «Интернет-магазин + CRM»

<!-- desc-end -->

<!-- nginx-start -->
## Пример настройки nginx

```editorconfig
location ~* ^/ws/ {
    rewrite ^/ws/(.*)$ /bitrix/subws/$1 break;
    #access_log  /var/log/nginx/post.log  logpost;
    access_log off;
    proxy_pass http://nodejs_sub;
    # http://blog.martinfjordvald.com/2013/02/websockets-in-nginx/
    # 12h+0.5
    proxy_max_temp_file_size 0;
    proxy_read_timeout 43800;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $replace_upgrade;
    proxy_set_header Connection $connection_upgrade;
}

location ~* ^/pub/mysecreturl/(.*?)$ {
    rewrite ^/pub/([0-9a-z]+)/(.*)$ /bitrix/pub/?CHANNEL_ID=$2 break;
    # IM doesn't wait
    #access_log  /var/log/nginx/post.log  logpost;
    proxy_ignore_client_abort on;
    proxy_pass http://nodejs_pub;
}

location ~* ^/api/mysecreturl/server-stat/(.*?)$ {
    rewrite ^/api/(.*)$ /server-stat/ break;
    access_log off;
    proxy_pass http://nodejs_pub;
    proxy_max_temp_file_size 0;
    proxy_read_timeout 43800;
}

location ~* ^/api/mysecreturl/nginx_status/ {
    stub_status on;
}
```

<!-- nginx-end -->

<!-- sett-start -->
## Настройка модуля

| Параметр                                                      | Приме                                                                            |
|---------------------------------------------------------------|----------------------------------------------------------------------------------|
| Адрес отправки сообщений (#CHANNEL_ID# - ид канала)           | https://push.zahalski.dev/pub/mysecreturl/#CHANNEL_ID#                           |
| Адрес подписки на каналы сообщений (#CHANNEL_ID# - ид канала) | wss://push.zahalski.dev/ws/?CHANNEL_ID=#CHANNEL_ID#&binaryMode=false&revision=19 |
| Адрес API NodeJs RTC сервера                                  | https://push.zahalski.dev/api/mysecreturl/                                       |
| Секретный ключ                                                | /etc/push-server/push-server*.json в секции security в параметре key             |

<!-- sett-end -->

<!-- cl-start -->
## История версий

https://github.com/azahalski/awz.pull/blob/master/CHANGELOG.md

<!-- cl-end -->