# دليل شامل لموقع تأجير السيارات (Real Rent Car)

## نظرة عامة على المشروع

هذا المشروع هو نظام **Laravel + Vue.js + Inertia.js** لإدارة تأجير السيارات.

---

## المستخدمون الافتراضيون (Demo Users)

### الأدمن (Admin)
- **Email:** `admin@example.com`
- **Password:** `00000000`
- **Role:** ADMIN

### العميل (Client)
- **Email:** `client@example.com`
- **Password:** `00000000`
- **Role:** CLIENT

---

## طرق تسجيل الدخول

### 1. تسجيل دخول الأدمن (الطريقة السرية)
**الرابط:** `/admin-secret-url`

- فقط مستخدمون بصلاحية **ADMIN** يمكنهم الدخول
- بعد تسجيل الدخول، يتم توجيهك تلقائياً إلى `/admin/cars`

### 2. تسجيل دخول العميل (الطريقة العادية)
**الرابط:** `/login`

- فقط مستخدمون بصلاحية **CLIENT** يمكنهم الدخول
- بعد تسجيل الدخول، يتم توجيهك تلقائياً إلى `/client/reservations`

### 3. إنشاء حساب جديد
**الرابط:** `/register`

---

## لماذا الإعدادات (Settings) لا تظهر؟

### السبب الرئيسي:
الإعدادات محمية بـ **Middleware خاص اسمه `restricted`**.

```php
// في ملف routes/settings.php
Route::middleware('auth', 'restricted')->group(function () {
    Route::redirect('settings', '/settings/profile');
    // ...
});
```

### محتوى الـ Middleware `restricted`:
```php
// app/Http/Middleware/restricted.php
public function handle(Request $request, Closure $next): Response
{
    return redirect()->to('/');  // ← يعيد التوجيه للصفحة الرئيسية دائماً!
}
```

**النتيجة:** أي شخص يحاول الوصول للإعدادات يتم إعادة توجيهه للصفحة الرئيسية `/`.

### الحل:
لإصلاح هذا، قم بتعديل ملف:
`app/Http/Middleware/restricted.php`

استبدل الكود بـ:
```php
public function handle(Request $request, Closure $next): Response
{
    return $next($request);  // ← اسمح بالوصول
}
```

أو إذا كنت تريد تقييد وصول معين، استخدم شروط منطقية.

---

## الـ Middleware المستخدمة

| Middleware | الوظيفة |
|------------|---------|
| `auth` | التأكد من تسجيل الدخول |
| `verified` | التأكد من تفعيل البريد الإلكتروني |
| `active` | التأكد من أن الحساب مفعل (ليس معلق) |
| `admin` | التأكد من أن المستخدم أدمن |
| `client` | التأكد من أن المستخدم عميل |
| `restricted` | مانع الوصول للإعدادات (حالياً يعيد التوجيه للرئيسية) |

---

## Routes (الروابط) التفصيلية

### الروابط العامة (Public Routes)

| الرابط | الـ Controller | الـ View | الوصف |
|--------|---------------|----------|-------|
| `/` | `HomePagesController@index` | `Welcome.vue` | الصفحة الرئيسية |
| `/fleet` | `HomePagesController@fleet` | `Fleet.vue` | عرض أسطول السيارات |
| `/fleet/{car}` | `BookingController@show` | `Booking.vue` | صفحة حجز سيارة محددة |
| `/about` | `HomePagesController@about` | `About.vue` | من نحن |
| `/contact` | `HomePagesController@contact` | `Contact.vue` | اتصل بنا |
| `/booking/{reservation}` | `BookingController@confirmation` | `BookingConfirmation.vue` | تأكيد الحجز |

### روابط المصادقة (Auth Routes)

| الرابط | الوصف |
|--------|-------|
| `/login` | تسجيل الدخول (للعملاء فقط) |
| `/admin-secret-url` | تسجيل دخول الأدمن (سري) |
| `/register` | إنشاء حساب جديد |
| `/forgot-password` | نسيت كلمة المرور |
| `/reset-password/{token}` | إعادة تعيين كلمة المرور |
| `/verify-email` | التحقق من البريد |
| `/logout` | تسجيل الخروج (POST) |

### روابط الأدمن (Admin Routes) - تتطلب صلاحية أدمن

**المسارات تبدأ بـ:** `/admin`

| الرابط | الـ Controller | الوصف |
|--------|---------------|-------|
| `/admin` | redirect → `/admin/cars` | تحويل للسيارات |
| `/admin/cars` | `CarsController@index` | قائمة السيارات |
| `/admin/cars/create` | `CarsController@create` | إضافة سيارة جديدة |
| `/admin/cars/{car}/edit` | `CarsController@edit` | تعديل سيارة |
| `/admin/reservations` | `ReservationsController@index` | قائمة الحجوزات |
| `/admin/reservations/{reservation}` | `ReservationsController@show` | تفاصيل حجز |
| `/admin/reservations/{reservation}/edit` | `ReservationsController@edit` | تعديل حجز |
| `/admin/reservations/{reservation}/print` | `ReservationsController@print` | طباعة الحجز |
| `/admin/clients` | `ClientsController@index` | قائمة العملاء |
| `/admin/clients/{client}` | `ClientsController@show` | تفاصيل عميل |
| `/admin/clients/{client}/suspend` | `ClientsController@suspend` | تعليق عميل (PATCH) |
| `/admin/clients/{client}/activate` | `ClientsController@activate` | تفعيل عميل (PATCH) |
| `/admin/payments` | `PaymentsController@index` | المدفوعات |
| `/admin/reports` | `ReportsController@index` | التقارير |
| `/admin/reports/create` | `ReportsController@create` | إنشاء تقرير |
| `/admin/support` | `SupportController@index` | الدعم الفني |
| `/admin/support/tickets/{ticket}` | `SupportController@show` | تفاصيل تذكرة |

### روابط العميل (Client Routes) - تتطلب صلاحية عميل

**المسارات تبدأ بـ:** `/client`

| الرابط | الـ Controller | الوصف |
|--------|---------------|-------|
| `/client` | redirect → `/client/reservations` | تحويل للحجوزات |
| `/client/reservations` | `ReservationsController@index` | حجوزاتي |
| `/client/reservations/{id}` | `ReservationsController@show` | تفاصيل حجز |
| `/client/reservations/{id}/print` | `ReservationsController@print` | طباعة حجز |
| `/client/support` | `SupportController@index` | تذاكر الدعم |
| `/client/support/create` | `SupportController@create` | إنشاء تذكرة |
| `/client/support/{id}` | `SupportController@show` | تفاصيل تذكرة |

### روابط الإعدادات (Settings Routes) - محظورة حالياً

| الرابط | الوصف |
|--------|-------|
| `/settings` | تحويل → `/settings/profile` |
| `/settings/profile` | تعديل الملف الشخصي |
| `/settings/password` | تغيير كلمة المرور |
| `/settings/appearance` | إعدادات المظهر |
| `/settings/two-factor` | المصادقة الثنائية |

---

## هيكل الـ Views (Vue Components)

```
resources/js/pages/
├── Welcome.vue           ← الصفحة الرئيسية
├── Fleet.vue             ← أسطول السيارات
├── Booking.vue           ← صفحة الحجز
├── BookingConfirmation.vue ← تأكيد الحجز
├── About.vue             ← من نحن
├── Contact.vue           ← اتصل بنا
├── auth/
│   ├── Login.vue         ← تسجيل دخول العملاء
│   ├── AdminLogin.vue    ← تسجيل دخول الأدمن
│   ├── Register.vue      ← إنشاء حساب
│   ├── ForgotPassword.vue
│   ├── ResetPassword.vue
│   └── VerifyEmail.vue
├── Admin/
│   ├── Cars/             ← إدارة السيارات
│   ├── Clients/          ← إدارة العملاء
│   ├── Reservations/     ← إدارة الحجوزات
│   ├── Payments/         ← المدفوعات
│   ├── Reports/          ← التقارير
│   └── Support/          ← الدعم الفني
├── Client/
│   ├── Reservations/     ← حجوزات العميل
│   └── Support/          ← دعم العميل
└── settings/
    ├── Profile.vue       ← إعدادات الملف الشخصي
    ├── Password.vue      ← تغيير كلمة المرور
    ├── Appearance.vue    ← المظهر
    └── TwoFactor.vue     ← المصادقة الثنائية
```

---

## الـ Layouts المستخدمة

| Layout | الاستخدام |
|--------|----------|
| `HomeLayout.vue` | الصفحات العامة (الرئيسية، الأسطول، من نحن...) |
| `AuthLayout.vue` | صفحات المصادقة (تسجيل الدخول، إنشاء حساب...) |
| `AdminLayout.vue` | لوحة تحكم الأدمن |
| `ClientLayout.vue` | لوحة تحكم العميل |
| `AppLayout.vue` | التخطيط العام |
| `settings/Layout.vue` | صفحات الإعدادات |

---

## ملخص سريع للوصول

### للأدمن:
1. ادخل على: `/admin-secret-url`
2. استخدم: `admin@example.com` / `00000000`
3. ستذهب تلقائياً لـ: `/admin/cars`

### للعميل:
1. ادخل على: `/login`
2. استخدم: `client@example.com` / `00000000`
3. ستذهب تلقائياً لـ: `/client/reservations`

### لإصلاح الإعدادات:
عدل الملف: `app/Http/Middleware/restricted.php`
واستبدل `return redirect()->to('/');` بـ `return $next($request);`

---

## ملفات الـ Routes الرئيسية

| الملف | المحتوى |
|-------|---------|
| `routes/web.php` | الروابط العامة |
| `routes/auth.php` | المصادقة (شامل تسجيل دخول الأدمن السري) |
| `routes/admin.php` | لوحة تحكم الأدمن |
| `routes/client.php` | لوحة تحكم العميل |
| `routes/settings.php` | الإعدادات (محظورة حالياً) |

---

*تم إنشاء هذا الدليل تلقائياً لشرح هيكل الموقع بالكامل*
