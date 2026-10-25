# Render evidence — 2026-10-25 (plan v1.1 HYG-204)

## Default render (docker compose -f docker-compose.yml config)
```yaml
name: sakip
services:
  app:
    build:
      context: /Users/macbook/Developer/php/sakip
      dockerfile: Dockerfile
    container_name: sakip-app
    depends_on:
      mysql:
        condition: service_healthy
        required: true
      redis:
        condition: service_started
        required: true
    environment:
      APP_DEBUG: "false"
      APP_ENV: local
      APP_KEY: base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=
      APP_NAME: SAKIP
      APP_URL: http://localhost
      BROADCAST_DRIVER: log
      CACHE_DRIVER: file
      DB_CONNECTION: mysql
      DB_DATABASE: /Users/macbook/Developer/php/sakip/database/database.sqlite
      DB_HOST: mysql
      DB_PASSWORD: 'y'
      DB_PORT: "3306"
      DB_USERNAME: x
      FILESYSTEM_DISK: local
      LOG_CHANNEL: daily
      LOG_LEVEL: warning
      MAIL_ENCRYPTION: tls
      MAIL_FROM_ADDRESS: noreply@sakip.local
      MAIL_FROM_NAME: SAKIP
      MAIL_HOST: smtp.mailtrap.io
      MAIL_MAILER: smtp
      MAIL_PASSWORD: "null"
      MAIL_PORT: "2525"
      MAIL_USERNAME: "null"
      QUEUE_CONNECTION: sync
      REDIS_HOST: redis
      REDIS_PASSWORD: z
      REDIS_PORT: "6379"
      SESSION_DRIVER: file
      SESSION_ENCRYPT: "true"
      SESSION_LIFETIME: "120"
      SESSION_SECURE_COOKIE: "true"
    networks:
      sakip-network: null
    ports:
      - mode: ingress
        host_ip: 127.0.0.1
        target: 80
        published: "80"
        protocol: tcp
    restart: unless-stopped
    volumes:
      - type: bind
        source: /Users/macbook/Developer/php/sakip/storage/app
        target: /var/www/html/storage/app
        bind: {}
      - type: bind
        source: /Users/macbook/Developer/php/sakip/storage/logs
        target: /var/www/html/storage/logs
        bind: {}
      - type: bind
        source: /Users/macbook/Developer/php/sakip/docker/php/php.ini
        target: /usr/local/etc/php/conf.d/custom.ini
        read_only: true
        bind: {}
    working_dir: /var/www/html
  mysql:
    container_name: sakip-mysql
    environment:
      MYSQL_DATABASE: /Users/macbook/Developer/php/sakip/database/database.sqlite
      MYSQL_PASSWORD: 'y'
      MYSQL_ROOT_PASSWORD: w
      MYSQL_USER: x
    healthcheck:
      test:
        - CMD
        - mysqladmin
        - ping
        - -h
        - localhost
        - -u
        - root
        - -pw
      timeout: 5s
      interval: 10s
      retries: 5
      start_period: 30s
    image: mysql:8.0
    networks:
      sakip-network: null
    ports:
      - mode: ingress
        host_ip: 127.0.0.1
        target: 3306
        published: "3306"
        protocol: tcp
    restart: unless-stopped
    volumes:
      - type: bind
        source: /Users/macbook/Developer/php/sakip/docker/mysql/data
        target: /var/lib/mysql
        bind: {}
      - type: bind
        source: /Users/macbook/Developer/php/sakip/docker/mysql/my.cnf
        target: /etc/mysql/conf.d/custom.cnf
        read_only: true
        bind: {}
      - type: bind
        source: /Users/macbook/Developer/php/sakip/docker/mysql/backups
        target: /backups
        bind: {}
      - type: bind
        source: /Users/macbook/Developer/php/sakip/docker/mysql/init
        target: /docker-entrypoint-initdb.d
        read_only: true
        bind: {}
  queue:
    build:
      context: /Users/macbook/Developer/php/sakip
      dockerfile: Dockerfile
    command:
      - php
      - artisan
      - queue:work
      - --tries=3
      - --timeout=90
      - --sleep=3
      - --max-jobs=1000
    container_name: sakip-queue
    depends_on:
      mysql:
        condition: service_started
        required: true
      redis:
        condition: service_started
        required: true
    environment:
      APP_DEBUG: "false"
      APP_ENV: local
      APP_KEY: base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=
      APP_NAME: SAKIP
      CACHE_DRIVER: redis
      DB_CONNECTION: mysql
      DB_DATABASE: /Users/macbook/Developer/php/sakip/database/database.sqlite
      DB_HOST: mysql
      DB_PASSWORD: 'y'
      DB_PORT: "3306"
      DB_USERNAME: x
      QUEUE_CONNECTION: redis
      REDIS_HOST: redis
      REDIS_PASSWORD: z
      REDIS_PORT: "6379"
      SESSION_DRIVER: redis
    networks:
      sakip-network: null
    restart: unless-stopped
    volumes:
      - type: bind
        source: /Users/macbook/Developer/php/sakip/storage/logs
        target: /var/www/html/storage/logs
        bind: {}
  redis:
    command:
      - redis-server
      - --appendonly
      - 'yes'
      - --requirepass
      - z
    container_name: sakip-redis
    healthcheck:
      test:
        - CMD
        - redis-cli
        - -a
        - z
        - ping
      timeout: 3s
      interval: 10s
      retries: 3
    image: redis:7-alpine
    networks:
      sakip-network: null
    ports:
      - mode: ingress
        host_ip: 127.0.0.1
        target: 6379
        published: "6379"
        protocol: tcp
    restart: unless-stopped
    volumes:
      - type: volume
        source: redis-data
        target: /data
        volume: {}
      - type: bind
        source: /Users/macbook/Developer/php/sakip/docker/redis/redis.conf
        target: /usr/local/etc/redis/redis.conf
        read_only: true
        bind: {}
  scheduler:
    build:
      context: /Users/macbook/Developer/php/sakip
      dockerfile: Dockerfile
    command:
      - sh
      - -c
      - " while true; do\n  php artisan schedule:run --verbose --no-interaction &\n
        \ sleep 60\ndone "
    container_name: sakip-scheduler
    depends_on:
      mysql:
        condition: service_started
        required: true
      redis:
        condition: service_started
        required: true
    environment:
      APP_DEBUG: "false"
      APP_ENV: local
      APP_KEY: base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=
      APP_NAME: SAKIP
      CACHE_DRIVER: redis
      DB_CONNECTION: mysql
      DB_DATABASE: /Users/macbook/Developer/php/sakip/database/database.sqlite
      DB_HOST: mysql
      DB_PASSWORD: 'y'
      DB_PORT: "3306"
      DB_USERNAME: x
      QUEUE_CONNECTION: redis
      REDIS_HOST: redis
      REDIS_PASSWORD: z
      REDIS_PORT: "6379"
      SESSION_DRIVER: redis
    networks:
      sakip-network: null
    restart: unless-stopped
    volumes:
      - type: bind
        source: /Users/macbook/Developer/php/sakip/storage/logs
        target: /var/www/html/storage/logs
        bind: {}
networks:
  sakip-network:
    name: sakip_sakip-network
    driver: bridge
volumes:
  redis-data:
    name: sakip_redis-data
    driver: local
```

## Dev overlay render (docker compose -f docker-compose.yml -f docker-compose.dev.yml config)
```yaml
name: sakip
services:
  app:
    build:
      context: /Users/macbook/Developer/php/sakip
      dockerfile: Dockerfile
    container_name: sakip-app
    depends_on:
      mysql:
        condition: service_healthy
        required: true
      redis:
        condition: service_started
        required: true
    environment:
      APP_DEBUG: "false"
      APP_ENV: local
      APP_KEY: base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=
      APP_NAME: SAKIP
      APP_URL: http://localhost
      BROADCAST_DRIVER: log
      CACHE_DRIVER: file
      DB_CONNECTION: mysql
      DB_DATABASE: /Users/macbook/Developer/php/sakip/database/database.sqlite
      DB_HOST: mysql
      DB_PASSWORD: 'y'
      DB_PORT: "3306"
      DB_USERNAME: x
      FILESYSTEM_DISK: local
      LOG_CHANNEL: daily
      LOG_LEVEL: warning
      MAIL_ENCRYPTION: tls
      MAIL_FROM_ADDRESS: noreply@sakip.local
      MAIL_FROM_NAME: SAKIP
      MAIL_HOST: smtp.mailtrap.io
      MAIL_MAILER: smtp
      MAIL_PASSWORD: "null"
      MAIL_PORT: "2525"
      MAIL_USERNAME: "null"
      QUEUE_CONNECTION: sync
      REDIS_HOST: redis
      REDIS_PASSWORD: z
      REDIS_PORT: "6379"
      SESSION_DRIVER: file
      SESSION_ENCRYPT: "true"
      SESSION_LIFETIME: "120"
      SESSION_SECURE_COOKIE: "true"
    networks:
      sakip-network: null
    ports:
      - mode: ingress
        host_ip: 127.0.0.1
        target: 80
        published: "80"
        protocol: tcp
    restart: unless-stopped
    volumes:
      - type: bind
        source: /Users/macbook/Developer/php/sakip/storage/app
        target: /var/www/html/storage/app
        bind: {}
      - type: bind
        source: /Users/macbook/Developer/php/sakip/storage/logs
        target: /var/www/html/storage/logs
        bind: {}
      - type: bind
        source: /Users/macbook/Developer/php/sakip/docker/php/php.ini
        target: /usr/local/etc/php/conf.d/custom.ini
        read_only: true
        bind: {}
      - type: bind
        source: /Users/macbook/Developer/php/sakip
        target: /var/www/html
        bind: {}
    working_dir: /var/www/html
  mysql:
    container_name: sakip-mysql
    environment:
      MYSQL_DATABASE: /Users/macbook/Developer/php/sakip/database/database.sqlite
      MYSQL_PASSWORD: 'y'
      MYSQL_ROOT_PASSWORD: w
      MYSQL_USER: x
    healthcheck:
      test:
        - CMD
        - mysqladmin
        - ping
        - -h
        - localhost
        - -u
        - root
        - -pw
      timeout: 5s
      interval: 10s
      retries: 5
      start_period: 30s
    image: mysql:8.0
    networks:
      sakip-network: null
    ports:
      - mode: ingress
        host_ip: 127.0.0.1
        target: 3306
        published: "3306"
        protocol: tcp
    restart: unless-stopped
    volumes:
      - type: bind
        source: /Users/macbook/Developer/php/sakip/docker/mysql/data
        target: /var/lib/mysql
        bind: {}
      - type: bind
        source: /Users/macbook/Developer/php/sakip/docker/mysql/my.cnf
        target: /etc/mysql/conf.d/custom.cnf
        read_only: true
        bind: {}
      - type: bind
        source: /Users/macbook/Developer/php/sakip/docker/mysql/backups
        target: /backups
        bind: {}
      - type: bind
        source: /Users/macbook/Developer/php/sakip/docker/mysql/init
        target: /docker-entrypoint-initdb.d
        read_only: true
        bind: {}
  queue:
    build:
      context: /Users/macbook/Developer/php/sakip
      dockerfile: Dockerfile
    command:
      - php
      - artisan
      - queue:work
      - --tries=3
      - --timeout=90
      - --sleep=3
      - --max-jobs=1000
    container_name: sakip-queue
    depends_on:
      mysql:
        condition: service_started
        required: true
      redis:
        condition: service_started
        required: true
    environment:
      APP_DEBUG: "false"
      APP_ENV: local
      APP_KEY: base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=
      APP_NAME: SAKIP
      CACHE_DRIVER: redis
      DB_CONNECTION: mysql
      DB_DATABASE: /Users/macbook/Developer/php/sakip/database/database.sqlite
      DB_HOST: mysql
      DB_PASSWORD: 'y'
      DB_PORT: "3306"
      DB_USERNAME: x
      QUEUE_CONNECTION: redis
      REDIS_HOST: redis
      REDIS_PASSWORD: z
      REDIS_PORT: "6379"
      SESSION_DRIVER: redis
    networks:
      sakip-network: null
    restart: unless-stopped
    volumes:
      - type: bind
        source: /Users/macbook/Developer/php/sakip/storage/logs
        target: /var/www/html/storage/logs
        bind: {}
      - type: bind
        source: /Users/macbook/Developer/php/sakip
        target: /var/www/html
        bind: {}
  redis:
    command:
      - redis-server
      - --appendonly
      - 'yes'
      - --requirepass
      - z
    container_name: sakip-redis
    healthcheck:
      test:
        - CMD
        - redis-cli
        - -a
        - z
        - ping
      timeout: 3s
      interval: 10s
      retries: 3
    image: redis:7-alpine
    networks:
      sakip-network: null
    ports:
      - mode: ingress
        host_ip: 127.0.0.1
        target: 6379
        published: "6379"
        protocol: tcp
    restart: unless-stopped
    volumes:
      - type: volume
        source: redis-data
        target: /data
        volume: {}
      - type: bind
        source: /Users/macbook/Developer/php/sakip/docker/redis/redis.conf
        target: /usr/local/etc/redis/redis.conf
        read_only: true
        bind: {}
  scheduler:
    build:
      context: /Users/macbook/Developer/php/sakip
      dockerfile: Dockerfile
    command:
      - sh
      - -c
      - " while true; do\n  php artisan schedule:run --verbose --no-interaction &\n
        \ sleep 60\ndone "
    container_name: sakip-scheduler
    depends_on:
      mysql:
        condition: service_started
        required: true
      redis:
        condition: service_started
        required: true
    environment:
      APP_DEBUG: "false"
      APP_ENV: local
      APP_KEY: base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=
      APP_NAME: SAKIP
      CACHE_DRIVER: redis
      DB_CONNECTION: mysql
      DB_DATABASE: /Users/macbook/Developer/php/sakip/database/database.sqlite
      DB_HOST: mysql
      DB_PASSWORD: 'y'
      DB_PORT: "3306"
      DB_USERNAME: x
      QUEUE_CONNECTION: redis
      REDIS_HOST: redis
      REDIS_PASSWORD: z
      REDIS_PORT: "6379"
      SESSION_DRIVER: redis
    networks:
      sakip-network: null
    restart: unless-stopped
    volumes:
      - type: bind
        source: /Users/macbook/Developer/php/sakip/storage/logs
        target: /var/www/html/storage/logs
        bind: {}
      - type: bind
        source: /Users/macbook/Developer/php/sakip
        target: /var/www/html
        bind: {}
networks:
  sakip-network:
    name: sakip_sakip-network
    driver: bridge
volumes:
  redis-data:
    name: sakip_redis-data
    driver: local
```
