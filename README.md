# Тестовое задание

### Сборка проекта

Если используется docker и docker-compose:
1. `cd prh-test_task`
2. `./build_dev.sh`
3. В браузере открыть: [127.0.0.1:25080](http://127.0.0.1:25080). Если необходимо, порт можно изменить в переменной `APP_HTTP_MAP_PORT`, файла `./.env`

иначе:
1. `cd prh-test_task/app`
2. `composer install --no-interaction`
3. `./init --env=Development --overwrite=All --delete=All`
4. Прописать параметры подключения к БД в `common/config/main-local.php`
5. `./yii migrate --interactive=0`
6. `./yii fixture '*' --namespace='common\fixtures' --interactive=0`

### Аутентификация
Если фикстуры загружены, то залогиниться можно по кредам: **admin/admin**

### FAQ

**Q.** Как уменьшить время свежести яблок?  
**A.** Предопределить параметр `apple.freshnessTime` в `backend/config/params-local.php` (См. `backend/config/params.php`).  