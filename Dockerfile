# Imagen oficial de PHP CLI
FROM php:8.2-cli

# Paquetes para Postgres y extensión pdo_pgsql
RUN apt-get update \
 && apt-get install -y --no-install-recommends libpq-dev \
 && docker-php-ext-install pdo_pgsql pgsql \
 && rm -rf /var/lib/apt/lists/*

# Carpeta de trabajo
WORKDIR /app

# Copiar el proyecto
COPY . .

# (opcional) más workers para el servidor embebido
ENV PHP_CLI_SERVER_WORKERS=4

# Render inyecta $PORT; exponemos 8080 por defecto
EXPOSE 8080

# Arrancar el servidor embebido apuntando a /public
# Usa $PORT si Render lo define; si no, 8080 local
CMD sh -lc 'php -S 0.0.0.0:${PORT:-8080} -t public'
