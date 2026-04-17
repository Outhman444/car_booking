# Clone the repository
git clone https://github.com/Outhman444/car_booking.git

cd car_booking

# Install PHP dependencies
composer install

# Install Node dependencies
npm install



# Run database migrations
php artisan migrate

# Seed the database (optional)
php artisan db:seed


php artisan storage:link


# Start the development server
php artisan serve
npm run dev
