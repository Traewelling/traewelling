# Self-Hosting Träwelling

Träwelling is a Laravel-based web application and can be deployed either directly on a server (“bare metal”) or through
containerized setups such as Docker or Nix flake environments.

---

## 1. Bare Metal Setup (Webserver + PHP + Database)

Träwelling is a standard **Laravel application**, so it can be installed on any server that supports PHP and a
relational database (MariaDB, MySQL, or SQLite).

### Background processes

To ensure background jobs and scheduled tasks run correctly, you need to set up the following:

- **Queue worker:**  
  Laravel’s queue handles background processing such as sending emails, processing exports, and webhooks.
  Make sure a process is continuously running:
  ```bash
  php artisan queue:work --queue=default,webhook,export
  ```
- **Scheduler:**
  Laravel’s task scheduler should run every minute (e.g., via cron or systemd timer):
  ```bash
  * * * * * php artisan schedule:run
  ```

## 2. Docker Setup

A few Docker-related files are available under [`docs/hosting/docker/`](./docker/).  
They can be used to spin up a containerized instance of Träwelling.

> [!IMPORTANT]
> The Docker setup is **not actively maintained**. It may require adjustments to work correctly.  
> Feel free to use it **at your own risk** or help us maintain it!

If you’d like to help keep the Docker configuration up to date, contributions are very welcome.

## 3. Nix Flake Setup

There is also a **Nix flake** configuration available.

> [!IMPORTANT]
> This setup is currently **not actively maintained** and may not work reliably in its current state.
> Use the Nix flake setup **at your own risk** and help us maintain it if you can!
