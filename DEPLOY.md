# Запуск сайта на сервере (hoster.kz Cloud)

Сервер: `server.nii-arai-publishhouse.kz` (Ubuntu, 2 vCPU / 4 GB).
Нужны: IP-адрес сервера и пароль root (их показывает панель hoster.kz).

## 1. Зайти на сервер

```bash
ssh root@IP_СЕРВЕРА
```

## 2. Установить Nginx, PHP и MySQL

```bash
apt update
apt install -y nginx mariadb-server php-fpm php-mysql php-mbstring git unzip certbot python3-certbot-nginx
```

## 3. Создать базу данных

Придумай свой пароль вместо `СЛОЖНЫЙ_ПАРОЛЬ`:

```bash
mysql -e "CREATE DATABASE journal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -e "CREATE USER 'journal_user'@'localhost' IDENTIFIED BY 'СЛОЖНЫЙ_ПАРОЛЬ';"
mysql -e "GRANT ALL PRIVILEGES ON journal.* TO 'journal_user'@'localhost'; FLUSH PRIVILEGES;"
```

## 4. Загрузить сайт

```bash
git clone https://github.com/aseta-cell/NII.git /var/www/nii
cd /var/www/nii
mysql journal < database.sql
cp config.local.example.php config.local.php
nano config.local.php
```

В `config.local.php` впиши:
- `db_pass`: пароль из шага 3;
- `site_url`: `https://nii-arai-publishhouse.kz`;
- `reviewer_code` и `editor_code`: секретные коды для регистрации рецензентов и редакторов;
- реквизиты для оплаты уже прописаны: Kaspi и перевод из любого банка на номер **+7 771 473 18 52**. Поменять их можно в `payment_instructions`;
- при желании валюту (`currency`) и цены (`plans`). По умолчанию цены как в таблице на странице About: Author $149, Professional $499, Institutional — «Contact».

Затем выдай права на папку загрузок:

```bash
mkdir -p uploads
chown -R www-data:www-data /var/www/nii/uploads
```

## 5. Настроить Nginx

```bash
nano /etc/nginx/sites-available/nii
```

Вставь:

```nginx
server {
    listen 80;
    server_name nii-arai-publishhouse.kz www.nii-arai-publishhouse.kz;

    root /var/www/nii;
    index index.html index.php;

    client_max_body_size 25M;

    # Секретные файлы и git наружу не отдаём
    location ~ /\.git { deny all; }
    location ~ ^/(config\.local\.php|config\.local\.example\.php|database\.sql|DEPLOY\.md|README\.md|CNAME)$ { deny all; }
    location ~ ^/migration-.*\.sql$ { deny all; }

    # Загруженные файлы никогда не выполняются как PHP
    location ^~ /uploads/ {
        location ~ \.php$ { deny all; }
    }

    location / {
        try_files $uri $uri/ =404;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php-fpm.sock;
    }
}
```

Включи сайт:

```bash
ln -s /etc/nginx/sites-available/nii /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default
nginx -t && systemctl reload nginx
```

Лимит загрузки файлов в PHP (статьи до 20 МБ):

```bash
PHPINI=$(php -r 'echo php_ini_loaded_file();' | sed 's/cli/fpm/')
sed -i 's/^upload_max_filesize.*/upload_max_filesize = 20M/; s/^post_max_size.*/post_max_size = 25M/' "$PHPINI"
systemctl restart "php$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')-fpm"
```

## 6. Домен

В панели hoster.kz → Домены → `nii-arai-publishhouse.kz` → DNS добавь записи:

| Тип | Имя | Значение |
|-----|-----|----------|
| A   | @   | IP_СЕРВЕРА |
| A   | www | IP_СЕРВЕРА |

DNS обновляется от 15 минут до нескольких часов.

## 7. HTTPS (бесплатный сертификат)

Когда домен уже открывает сайт по `http://`:

```bash
certbot --nginx -d nii-arai-publishhouse.kz -d www.nii-arai-publishhouse.kz
```

## 8. Первый вход

1. Открой `https://nii-arai-publishhouse.kz/register.php`.
2. Выбери роль **Editor** и введи `editor_code` из `config.local.php`.
3. Так же зарегистрируй рецензентов (роль **Reviewer**, код `reviewer_code`).

## Как работает оплата

1. Автор подаёт статью (Publish), редактор назначает рецензента.
2. Рецензент принимает статью (Accept), и у автора появляется кнопка **Pay publication fee**.
3. Автор выбирает тариф и получает реквизиты и номер платежа.
4. Автор переводит деньги и загружает чек.
5. Редактор в своём кабинете видит чек в блоке **Payments to Confirm** и нажимает **Confirm payment**.
6. Редактор нажимает **Publish Article** (при желании указывает DOI), и статья появляется в архиве.

Сумма всегда берётся из `config.local.php`, поэтому подменить её в браузере нельзя.

## Особые (семейные) аккаунты — всё бесплатно

Человек, который знает особый пароль, публикуется без оплаты. Включить это можно двумя способами:
- **при регистрации:** раскрыть «I have a special access password» и ввести пароль;
- **уже зарегистрированному:** Account Settings → **Special Access** → ввести пароль → **Activate**.

После этого:
- принятая статья сразу получает статус **Paid** (платёж 0, план `free`), платить ничего не нужно;
- если человек уже начал оплату, неоплаченный счёт закрывается и статья тоже становится бесплатной;
- у редактора рядом с автором видна пометка «Special access · free».

Сам пароль в коде не хранится, только его хеш (`free_access_hash` в `config.php`). Сменить пароль:

```bash
php -r 'echo password_hash("НОВЫЙ_ПАРОЛЬ", PASSWORD_DEFAULT);'
```

Полученную строку впиши в `config.local.php` как `"free_access_hash" => '...'` (в одинарных кавычках).

## Патенты

Страница **Patents** (`patents.php`): подача заявок на изобретение, полезную модель или промышленный образец.

1. Автор нажимает **New patent application** и заполняет форму:
   - ФИО и ИИН;
   - телефон, email, адрес;
   - соавторов;
   - название на казахском, русском и английском;
   - реферат и файл описания.
2. Заявке сразу присваивается номер вида `NII-PAT-2026-00001`.
3. Пошлина **$99** (`patent_fee` в настройках). Автор переводит деньги по реквизитам и загружает чек.
4. Редактор в кабинете → блок **Patent Applications** → **Confirm payment**. После этого статусы: Filed → Under Examination → Granted / Rejected.
5. Кнопка **Open application document** открывает официальный документ:
   - номер заявки и штрихкод;
   - все поля на трёх языках.

   Кнопка **Print / Save as PDF** печатает его или сохраняет в PDF. Пока пошлина не оплачена, на документе стоит «DRAFT».

**Семейные аккаунты** (особый пароль) подают заявки бесплатно: заявка сразу получает статус Filed. Если пароль ввести позже, неоплаченные заявки тоже становятся бесплатными.

## Счётчики статьи

На странице статьи и в архиве показываются настоящие **просмотры** (каждое открытие `article.php`) и **скачивания** (кнопка Download PDF).

## Обновление сайта

```bash
cd /var/www/nii && git pull
```

Если база была создана из старого `database.sql` (до особых аккаунтов и счётчиков), один раз выполни:

```bash
mysql journal < migration-001-free-access.sql
mysql journal < migration-002-patents.sql
```

## Резервная копия базы

```bash
mysqldump journal > /root/journal-$(date +%F).sql
```
