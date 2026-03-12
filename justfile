default:
    @just --list

start:
    docker compose up -d

stop:
    docker compose down

logs:
    docker compose logs -f mariadb

connect:
    mysql -h 127.0.0.1 -P 3306 -u root -prootpassword -D cmpe272
