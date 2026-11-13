# HopeRise backend files (overlay for your existing Laravel 12 project)

1. Copy these folders into your project root, merging with what is there (app, config, database, routes).
   If routes/api.php already exists, merge the routes instead of overwriting it.
   If your project has no API routes yet: `php artisan install:api`
2. Add ENV-ADDITIONS.txt to .env
3. php artisan migrate
   php artisan db:seed --class=CampaignSeeder
4. Webhook URLs to register:
   Paystack dashboard  -> https://YOUR-API/api/webhooks/paystack
   M-Pesa callback     -> built automatically from MPESA_CALLBACK_BASE_URL + MPESA_CALLBACK_SECRET
