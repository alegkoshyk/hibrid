# 🚗 Hybrid Auto Customs Calculator - Гайд з Установки

## Швидка установка (5 хвилин)

### Крок 1: Завантажити плагін
```bash
# Перейдіть у директорію плагінів WordPress
cd wp-content/plugins/

# Скопіюйте папку плагіну
cp -r hybrid-auto-customs-calculator .
```

### Крок 2: Активувати плагін
1. Перейдіть до **WordPress Admin → Plugins**
2. Знайдіть **"Hybrid Auto Customs Calculator"**
3. Натисніть **"Activate"**

### Крок 3: Налаштування
1. Перейдіть до **Admin → Hybrid Auto Calc → Settings**
2. Введіть ваш **API ключ** від MD Office
3. Натисніть **"Sync Currencies Now"**

### Крок 4: Додайте на сторінку
1. Створіть нову сторінку або відредагуйте існуючу
2. Додайте шорткод:
   ```
   [hybrid-auto-calculator]
   ```
3. Опублікуйте сторінку

---

## Детальна установка

### Вимоги

- ✅ WordPress 5.0 або новіше
- ✅ PHP 7.4 або новіше  
- ✅ jQuery (Загружається в WordPress за замовчуванням)
- ✅ API ключ від [MD Office](https://www.mdoffice.com.ua/)

### Крок за кроком

#### 1. Завантаження плагіну

**Варіант A: Вручну через FTP**
1. Завантажте папку `hybrid-auto-customs-calculator`
2. За допомогою FTP клієнта (FileZilla, WinSCP) завантажте у:
   ```
   /public_html/wp-content/plugins/
   ```

**Варіант B: Через WordPress Admin**
1. Перейдіть до **Plugins → Add New → Upload Plugin**
2. Завантажте ZIP архів плагіну
3. Натисніть **"Install Now"**

**Варіант C: Git**
```bash
cd wp-content/plugins/
git clone https://github.com/yourusername/hybrid-auto-customs-calculator.git
```

#### 2. Активація плагіну

1. **WordPress Admin Panel**
2. **Plugins** → **Installed Plugins**
3. Знайдіть **"Hybrid Auto Customs Calculator"**
4. Натисніть **"Activate"**

Ви повинні бачити новий пункт меню **"Hybrid Auto Calc"** в лівій панелі адміну.

#### 3. Налаштування API

1. Перейдіть до **Hybrid Auto Calc → Settings**
2. Заповніть поля:
   - **API Key**: Ваш ключ з MD Office
   - **API Base URL**: `https://www.mdoffice.com.ua/api` (за замовчуванням)
   - **Enable Logging**: Вмикати для налагодження

3. Натисніть **"Save Changes"**

#### 4. Синхронізація валют

1. На сторінці **Settings** натисніть **"Sync Currencies Now"**
2. Вижди повідомлення: **"Currencies synced successfully!"**
3. Валюти автоматично синхронізуватимуться щодня о полуночі

#### 5. Управління країнами

1. Перейдіть до **Hybrid Auto Calc → Countries**
2. Налаштуйте країни:
   - Вмикайте/вимикайте країни за допомогою чекбоксів
   - Змінюйте назви країн
   - Встановлюйте коефіцієнти для кожної країни

3. Натисніть **"Save Countries"**

#### 6. Вибір валют

1. Перейдіть до **Hybrid Auto Calc → Currencies**
2. Виберіть які валюти показувати в калькуляторі
3. Натисніть **"Save Currencies"**

#### 7. Додавання калькулятора на сторінку

**Спосіб 1: Через Gutenberg редактор**
1. Створіть нову сторінку або посту
2. Додайте блок **"Shortcode"**
3. Введіть: `[hybrid-auto-calculator]`
4. Опублікуйте

**Спосіб 2: Через Classic редактор**
1. Створіть нову сторінку
2. Переключіться в **"Text"** режим
3. Вставте: `[hybrid-auto-calculator]`
4. Опублікуйте

**Спосіб 3: Через шаблон (Code)**
```php
<?php echo do_shortcode('[hybrid-auto-calculator]'); ?>
```

---

## Перевірка установки

✅ **Все готово!** Перевірте:

1. **Admin Panel**
   - Меню **"Hybrid Auto Calc"** видно?
   - Валюти завантажені?

2. **Frontend**
   - Перейдіть на сторінку де ви додали шорткод
   - Калькулятор видно?
   - Форма працює?

3. **Консоль браузера** (F12)
   - Помилок нема?
   - Логи показують успішні запити?

---

## Отримання API ключа від MD Office

1. Відвідайте https://www.mdoffice.com.ua/
2. Зареєструйтеся або увійдіть в кабінет
3. Знайдіть **"API Key"** в налаштуваннях профіля
4. Скопіюйте ключ
5. Вставте в **Settings** плагіну

---

## Розширена конфігурація

### Додання нових країн

Змініть файл `includes/class-hybrid-auto-calc-admin.php`:

```php
'620' => array(
    'code' => '620',
    'name' => 'Португалія',
    'coefficient' => 1.0,
    'enabled' => 1,
),
```

### Кастомізація стилів

Змініть CSS файли:
- `assets/css/calculator.css` - стилі калькулятора
- `assets/css/admin.css` - адмін стилі

### Увімкнення детального логування

1. **Settings → Enable Logging: ✓**
2. Відкрийте консоль браузера (F12)
3. Див. логи при розрахунках

---

## Рішення проблем

### Проблема: "Currencies not loaded"

**Рішення:**
1. Перейдіть до **Settings**
2. Натисніть **"Sync Currencies Now"**
3. Перевірте з'єднання з інтернетом

### Проблема: Шорткод не показує калькулятор

**Рішення:**
1. Перевірте шорткод: `[hybrid-auto-calculator]`
2. Очистіть кеш браузера (Ctrl+Shift+Del)
3. Перевірте консоль браузера на помилки

### Проблема: API помилка

**Рішення:**
1. Перевірте API ключ в Settings
2. Переконайтеся що базовий URL правильний
3. Перевірте логи сервера

---

## Деактивація і видалення

### Деактивація
1. **Plugins → Installed Plugins**
2. Знайдіть плагін
3. Натисніть **"Deactivate"**

### Видалення
1. Натисніть **"Delete"** на сторінці плагінів
2. АБО видаліть папку через FTP:
   ```
   /public_html/wp-content/plugins/hybrid-auto-customs-calculator/
   ```

---

## Техпідтримка

Якщо у вас виникли проблеми:

1. **Перевірте консоль браузера** (F12) на помилки JavaScript
2. **Увімкніть логування** в Settings
3. **Перевірте логи сервера**: `/wp-content/debug.log`
4. **Контактуйте розробника** з описом помилки

---

## Додаткові ресурси

- 📚 [Документація WordPress](https://wordpress.org/support/)
- 🏦 [НБУ API](https://bank.gov.ua/)
- 💼 [MD Office](https://www.mdoffice.com.ua/)
- 🐛 [GitHub Issues](https://github.com/yourusername/hybrid-auto-customs-calculator/issues)

---

**Готово!** Ваш калькулятор готовий до використання. 🎉
