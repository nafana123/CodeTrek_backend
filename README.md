Этот проект использует Docker для создания контейнеризированной среды для разработки с поддержкой различных языков программирования, таких как PHP, Node.js, Python, Go, Java, .NET и других.

## Требования

- Docker
- Docker Compose

## Структура проекта

Проект использует Docker Compose для настройки нескольких контейнеров:

- **php**:8.1
- **database**: Контейнер с MySQL.
- **container-manager**: Контейнер для работы с Docker внутри Docker.
- **user_code_* (Node.js, TypeScript, Python, PHP, Go, Java, .NET, GCC)**: Контейнеры для различных языков программирования для выполнения кода.

## Как запустить проект
## 1. Укажите своё подключение к базе данных
DATABASE_URL="mysql://admin:admin@127.0.0.1:3306/codeTrek?serverVersion=8.0.32&charset=utf8mb4"

## 2. Выполните команду
docker-compose up --build

## Когда контейнеры запустятся, вы увидите сообщение:
Контейнеры запущены.

## Чтобы запустить сервер PHP
php -S localhost:8000 -t public

## !!! В .env поменяйте поля для получения кода подтверждения при регистрации !!!
MAIL_USERNAME= ### Берите с https://ethereal.email
MAIL_PASSWORD= ### Берите с https://ethereal.email


