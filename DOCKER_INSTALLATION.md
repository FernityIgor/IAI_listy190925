# IAI Lists - Docker Installation Guide

This guide will help you install and run the IAI Lists application using Docker on Linux.

## Prerequisites

Before you begin, ensure you have the following installed on your Linux system:

- **Docker**: [Install Docker](https://docs.docker.com/engine/install/)
- **Docker Compose**: [Install Docker Compose](https://docs.docker.com/compose/install/)
- **Git**: For cloning the repository

### Quick Docker Installation (Ubuntu/Debian)

```bash
# Update package index
sudo apt update

# Install Docker
sudo apt install docker.io docker-compose

# Start and enable Docker service
sudo systemctl start docker
sudo systemctl enable docker

# Add your user to the docker group (optional, to run docker without sudo)
sudo usermod -aG docker $USER
```

After adding yourself to the docker group, log out and log back in for the changes to take effect.

## Installation Steps

### 1. Clone the Repository

```bash
git clone https://github.com/FernityIgor/IAI_listy190925.git
cd IAI_listy190925
```

### 2. Build and Run with Docker Compose

```bash
# Build and start the application
docker-compose up -d --build
```

This command will:
- Build the Docker image from the Dockerfile
- Create and start the container
- Set up persistent storage for labels and logs
- Expose the application on port 8080

### 3. Access the Application

Once the containers are running, you can access the application at:

```
http://localhost:8080
```

Or if you're accessing from another machine:

```
http://your-server-ip:8080
```

## Configuration

### Storage Directory

The application stores labels in a persistent Docker volume. If you need to access these files from the host system, you can:

1. Check the volume location:
```bash
docker volume inspect iai_listy190925_labels_data
```

2. Or copy files from the container:
```bash
docker cp iai_listy_app:/var/www/html/storage/labels ./local_labels/
```

### Environment Variables

You can customize the application by modifying the `docker-compose.yml` file to add environment variables:

```yaml
environment:
  - APACHE_DOCUMENT_ROOT=/var/www/html
  - PHP_INI_MEMORY_LIMIT=256M
```

## Management Commands

### View Running Containers
```bash
docker-compose ps
```

### View Application Logs
```bash
docker-compose logs -f web
```

### Stop the Application
```bash
docker-compose down
```

### Restart the Application
```bash
docker-compose restart
```

### Update the Application
```bash
# Pull latest changes
git pull origin master

# Rebuild and restart
docker-compose up -d --build
```

### Access Container Shell
```bash
docker-compose exec web bash
```

## Troubleshooting

### Port Already in Use

If port 8080 is already in use, modify the `docker-compose.yml` file:

```yaml
ports:
  - "8081:80"  # Change 8080 to any available port
```

### Permission Issues

If you encounter permission issues with storage:

```bash
# Fix storage permissions
docker-compose exec web chown -R www-data:www-data /var/www/html/storage
docker-compose exec web chmod -R 755 /var/www/html/storage
```

### Container Won't Start

Check the logs for detailed error information:

```bash
docker-compose logs web
```

### Rebuild from Scratch

If you need to completely rebuild:

```bash
# Stop and remove containers, networks, and volumes
docker-compose down -v

# Remove the image
docker rmi iai_listy190925_web

# Rebuild everything
docker-compose up -d --build
```

## Development

### Local Development with Hot Reload

For development, you can mount your local code:

```yaml
volumes:
  - .:/var/www/html
  - ./storage:/var/www/html/storage
```

Add this to the `web` service in `docker-compose.yml`.

### Database Connection (if needed)

If your application requires a database, add a database service to `docker-compose.yml`:

```yaml
services:
  db:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: rootpass
      MYSQL_DATABASE: iai_db
      MYSQL_USER: iai_user
      MYSQL_PASSWORD: iai_pass
    volumes:
      - db_data:/var/lib/mysql
    networks:
      - iai_network

volumes:
  db_data:
```

## Security Considerations

- Change default API keys and passwords before deploying to production
- Use HTTPS in production environments
- Consider using Docker secrets for sensitive information
- Keep Docker and the base images updated

## Support

If you encounter any issues, please check:

1. Docker and Docker Compose are properly installed
2. The repository was cloned successfully
3. All required ports are available
4. Check the application logs for specific error messages

For additional support, please create an issue in the GitHub repository.