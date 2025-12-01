# Docker Setup

A Docker Compose setup for local development.

## Quick Start

**All commands must be run from the project root directory.**

1. Start the containers:

   ```bash
   docker compose -f docs/hosting/docker/docker-compose.yml --project-directory . up -d --build
   ```

2. Access the application at http://localhost:8081

## Seed with Sample Data

Set `SEED_DB=true` in the app service environment (uncomment in docker-compose.yml), then rebuild. This resets the database.
