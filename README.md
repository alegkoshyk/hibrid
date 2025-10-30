# Hybrid Auto Customs Calculator Plugin

Kalkulyator mytnykh platezhiv dlya hibrydnykh avtomobiliw z intehratsiyeyu kursiv NBU.

## Description

Цей плагін WordPress надає калькулятор для розрахунку митних платежів на гібридні автомобілі. Він інтегрується з API MD Office та автоматично отримує актуальні курси валют Національного банку України (НБУ).

### Features

- ✅ Розрахунок митних платежів для гібридних авто
- ✅ Інтеграція з API MD Office
- ✅ Автоматичне отримання курсів валют з НБУ
- ✅ Управління країнами і коефіцієнтами
- ✅ Адмін-панель для налаштувань
- ✅ Responsive дизайн
- ✅ AJAX розрахунки без перезавантаження
- ✅ Локалізація для української мови

## Requirements

- WordPress 5.0+
- PHP 7.4+
- jQuery

## Installation

1. **Download Plugin**
   - Завантажте папку плагіну

2. **Upload to WordPress**
   ```bash
   # Copy to WordPress plugins directory
   cp -r hybrid-auto-customs-calculator /path/to/wordpress/wp-content/plugins/
   ```

3. **Activate Plugin**
   - Перейдіть до WordPress Admin → Plugins
   - Знайдіть "Hybrid Auto Customs Calculator"
   - Натисніть "Activate"

4. **Initial Setup**
   - Перейдіть до Admin → Hybrid Auto Calc → Settings
   - Введіть ваш API ключ MD Office
   - Натисніть "Sync Currencies Now"

## Configuration

### 1. API Settings
- **Admin → Hybrid Auto Calc → Settings**
- Введіть ваш API ключ від MD Office
- Налаштуйте базовий URL API (за замовчуванням: `https://www.mdoffice.com.ua/api`)
- Увімкніть логування для налагодження

### 2. Countries Management
- **Admin → Hybrid Auto Calc → Countries**
- Додайте або змініть країни походження
- Встановіть коефіцієнти для кожної країни
- Вимкніть країни, які не потрібні

### 3. Currencies Management
- **Admin → Hybrid Auto Calc → Currencies**
- Вибір валют для відображення в калькуляторі
- Курси автоматично оновлюються щодня з НБУ

## Usage

### Add Calculator to Page/Post

Додайте шорткод до вашої сторінки або посту:

```
[hybrid-auto-calculator]
```

### Shortcode Parameters

На поточний момент шорткод не має параметрів. Всі налаштування управляються через адмін-панель.

## How It Works

### Frontend

1. **Calculator Form** - Користувач вводить дані про автомобіль:
   - Тип особи (фізична/юридична)
   - Тип двигуна (гібрид бензин/дизель)
   - Вік автомобіля
   - Об'єм двигуна
   - Країна походження
   - Вартість
   - Валюта

2. **Currency Selection** - При виборі валюти:
   - Автоматично завантажується актуальний курс з NBU
   - Коневертуються вхідні дані

3. **Calculation** - AJAX запит на сервер:
   - Дані відправляються до MD Office API
   - Розраховуються мито, ПДВ, акциз

4. **Results Display** - Результати показуються:
   - Вартість в гривнях
   - Мито, ПДВ, Акциз
   - Загальна сума
   - Еквівалент у вибраній валюті

### Backend

1. **Currency Sync** - Щодня о midnight:
   - Отримуються актуальні курси з НБУ API
   - Зберігаються в транзієнтах (24 години кешу)

2. **Calculation Request** - При AJAX запиті:
   - Перевіряються дані
   - Складається запит до MD Office API
   - Повертаються результати

## Database

Плагін використовує WordPress options для зберігання:
- `hybrid_auto_calc_settings` - Налаштування API
- `hybrid_auto_calc_countries` - Країни і коефіцієнти
- `hybrid_auto_calc_nbu_currencies` - Увімкнені валюти
- Транзієнт `hybrid_auto_calc_currencies` - Кешований список валют (24 години)

## API Integration

### MD Office API

Розраховує митні платежі:

```
GET https://www.mdoffice.com.ua/api/api_paid.auto
?key=YOUR_API_KEY
&motor=2
&age=0
&capacity=3000
&currency=840
&cost=30000
&user=0
&year=2025
```

### NBU API

Отримує курси валют:

```
GET https://bank.gov.ua/NBUStatService/v1/statdirectory/exchangenew?json
```

## File Structure

```
hybrid-auto-customs-calculator/
├── hybrid-auto-customs-calculator.php    # Main plugin file
├── includes/
│   ├── class-hybrid-auto-calc.php        # Main plugin class
│   ├── class-hybrid-auto-calc-admin.php  # Admin functionality
│   ├── class-hybrid-auto-calc-api.php    # API integration
│   ├── class-hybrid-auto-calc-frontend.php # Frontend
│   └── calculator-template.php           # Calculator HTML
├── assets/
│   ├── css/
│   │   ├── calculator.css               # Frontend styles
│   │   └── admin.css                    # Admin styles
│   └── js/
│       └── calculator.js                # Frontend JavaScript
├── languages/
│   └── hybrid-auto-calc-uk.po          # Ukrainian translations
└── README.md                           # This file
```

## Troubleshooting

### "Currencies not loaded"
- Перейдіть до Settings і натисніть "Sync Currencies Now"
- Перевірте з'єднання з інтернетом
- Перевірте логи сервера на помилки

### Calculator not showing
- Переконайтеся, що шорткод правильно добавлений: `[hybrid-auto-calculator]`
- Очистіть кеш браузера
- Перевірте консоль браузера на помилки JavaScript

### API Error
- Перевірте API ключ в Settings
- Перевірте базовий URL API
- Переконайтеся, що ваш сервер може зробити зовнішні запити

### Exchange rate not updating
- Перевірте, що wp_schedule_event включений
- Запустіть wp-cron вручну: натисніть "Sync Currencies Now" в Settings

## Support

Якщо у вас виникли проблеми:

1. Перевірте консоль браузера (F12) на помилки
2. Увімкніть логування в Settings
3. Перевірте логи сервера: `/wp-content/debug.log`

## Development

### Adding Custom Countries

Змініть функцію `get_default_countries()` в `class-hybrid-auto-calc-admin.php`:

```php
'code' => array(
    'code' => 'code',
    'name' => 'Country Name',
    'coefficient' => 1.0,
    'enabled' => 1,
),
```

### Customizing Styles

Змініть файли CSS:
- `assets/css/calculator.css` - Фронтенд
- `assets/css/admin.css` - Адмін

### Adding More Currencies

Вибір доступних валют управляється через адмін-панель. Новых валют автоматично додаються при синхронізації з НБУ.

## License

GPL v2 or later

## Changelog

### 1.0.0
- Initial release
- API integration
- Currency sync from NBU
- Admin settings
- Frontend calculator

## Author

Your Name - https://yoursite.com

## Disclaimer

Цей плагін надає розрахунки на основі даних API. За точність розрахунків та актуальність ставок звертайтеся до офіційних джерел:
- Національний банк України: https://bank.gov.ua/
- MD Office API: https://www.mdoffice.com.ua/
