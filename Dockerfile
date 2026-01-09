FROM php:8.2-cli
COPY . /app
WORKDIR /app

# Jalankan PHP built-in web server di port 8000
CMD ["php", "-S", "0.0.0.0:8000", "-t", "/app"]
