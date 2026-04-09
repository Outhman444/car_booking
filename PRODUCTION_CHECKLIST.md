# Production Checklist - Real Rent Car

## Critical Security Settings

### 1. Environment Variables (.env)
- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false`
- [ ] `APP_KEY=` (generate with `php artisan key:generate`)
- [ ] `APP_URL=https://your-domain.com`

### 2. Database
- [ ] Use MySQL/PostgreSQL (not SQLite) for production
- [ ] Set proper DB credentials in .env

### 3. Security
- [ ] Remove or disable DemoUsersSeeder in production
- [ ] Change admin login URL from `/admin-secret-url` to something unique
- [ ] Set `FILESYSTEM_DISK=s3` or proper cloud storage

---

## Payment Gateway Configuration

### PayPal Setup
```env
PAYPAL_CLIENT_ID=your_paypal_client_id
PAYPAL_SECRET=your_paypal_secret
PAYPAL_SANDBOX=false  # Set to false for production
```
Get credentials from [PayPal Developer Portal](https://developer.paypal.com)

### Stripe Setup
```env
STRIPE_KEY=pk_live_xxx  # Use live key for production
STRIPE_SECRET=sk_live_xxx  # Use live secret for production
STRIPE_WEBHOOK_SECRET=whsec_xxx  # From Stripe Dashboard
```
Get credentials from [Stripe Dashboard](https://dashboard.stripe.com)

### Admin Configuration
1. Go to `/admin/payment-methods`
2. Enable PayPal and/or Stripe
3. Set Sandbox Mode to OFF for production
4. Customize display names and descriptions

---

## Commands to Run Before Deployment

```bash
# Generate application key
php artisan key:generate

# Clear all cache
php artisan optimize:clear

# Run migrations
php artisan migrate --force

# Link storage
php artisan storage:link

# Build assets
npm run build

# Optimize for production
php artisan optimize
```

---

## Demo Data (REMOVE IN PRODUCTION)

The following seeders create demo data - **DO NOT RUN** in production:

| Seeder | Creates | Risk |
|--------|---------|------|
| DemoUsersSeeder | admin@example.com / 00000000 | HIGH - Known credentials |
| CarsSeeder | Sample cars | LOW - Test data |
| ClientsSeeder | Sample clients | MEDIUM - Test accounts |
| ReservationsPaymentsSeeder | Sample reservations | LOW - Test data |
| TicketSeeder | Sample tickets | LOW - Test data |

### To Disable Demo Seeders:
Edit `database/seeders/DatabaseSeeder.php`:

```php
public function run(): void
{
    // Comment out or remove these in production:
    // $this->call([DemoUsersSeeder::class, ...]);
}
```

---

## Admin Login Security

Current admin login URL: `/admin-secret-url`

**Recommendation:** Change this to a unique, hard-to-guess URL.

Edit `routes/auth.php`:
```php
// Change 'admin-secret-url' to your secret path
Route::get('your-secret-admin-path', ...);
```

---

## Features Status

| Feature | Status |
|---------|--------|
| Car Management (CRUD) | WORKING |
| Reservation Management | WORKING |
| Client Management | WORKING |
| Payment Viewing | WORKING |
| Payment Methods (PayPal/Stripe) | WORKING |
| Reports | WORKING |
| Support Tickets | WORKING |
| Settings Pages | WORKING |
| Two-Factor Auth | WORKING |
| Client Registration | WORKING |
| Password Reset | WORKING |

---

## File Permissions (Linux/Mac)

```bash
chmod -R 755 storage
chmod -R 755 bootstrap/cache
chmod -R 755 public
```

---

## Post-Deployment Verification

1. [ ] Admin login works
2. [ ] Client registration works
3. [ ] Car booking works
4. [ ] Payment methods configured in admin
5. [ ] Client can pay for reservations
6. [ ] PDF generation works
7. [ ] File uploads work
8. [ ] Email sending works (configure mail driver)
9. [ ] SSL certificate installed
10. [ ] Database backups configured

---

*Generated for production readiness*
