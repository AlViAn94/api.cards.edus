<p align="center"><a href="https://mediana.kz/" target="_blank"><img src="https://www.intronet.kz/images/logo_medianakz.png" width="400" alt="Laravel Logo"></a></p>

## Описание проекта

Проект предоставляет ученикам и сотрудникам школьных учереждений перевыпускать и пополнять карты EDUS.<br>
Проект делится на две части, "Клиентская" (для создания и пополнения карт) "Менеджерская" (для обработки запросов, выпуска и отправки).
- [Сайт для первичного выпуска карт](https://cards.edus.kz).
- [Роут для api перевыпуска](https://api.cards.edus.kz).


## Основные функции и возможности

- ### Клиентская часть
1. Регистрация пользователя
2. Оплата пере выпуска карты
3. Проверка статуса

- ### Менеджерская часть
1. Генерация карты
2. Печать
3. Регистрация
4. Отправка


## Структура

- app
    - Console
    - Exceptions
    - Http
        - Controllers
            - UserController.php (отвечает за сохранение пользователя на стороне клиента и проверку оплаты)
            - PdfController.php (отвечает за генерацию и пдф файлы)
            - StatisticController.php (отвечает за вывод статистики в Dashboard)
            - CardController.php (отвечает за привязку NFC к аккаунту)
        - Middleware
        - Requests
    - Models
      - Users.php (основная модель отвечающая за сохранение в БД пользователя)
    - Providers
    - Services
      - UserService.php (сервис обновляющий информацию при генерации и валидация при регистрации)
      - PdfService.php (генерация и обновление карты)
- bootstrap
- config
- database
    - factories
    - migrations
    - seeders
- public
- resources
    - css
    - js
    - lang
    - views
- routes
- storage
    - app
    - framework
    - logs
- tests


