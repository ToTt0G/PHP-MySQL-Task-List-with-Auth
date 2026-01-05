# Tasks Application

[![Live Demo](https://img.shields.io/badge/Live_Demo-Click_Here-blue?style=for-the-badge&logo=firefox)](https://tasks.redsunsetfarm.com)

A simple task management application built with PHP (custom MVC framework), MySQL, and Docker. This application allows users to register, manage tasks, and features real-time-like updates via short polling.

![App Preview](github/tasks.png)

## Features

- **User Authentication**:

  - Register and Login.
  - Session management with "Remember Me" functionality.
  - Secure password hashing (Bcrypt).
  - CSRF Protection for state-changing requests.

  ![Login Screen](github/login.png)

- **Task Management**:
  - Create, Read, Update, and Delete (CRUD) tasks.
  - Tasks are private to each user.
  - **Short Polling**: The frontend periodically checks for updates to keep the task list in sync across multiple tabs/devices.
- **Admin Dashboard**:

  - View all sessions, tasks, and users.
  - View details of specific users.

  ![Admin Dashboard](github/admin-dashboard.png)

- **Architecture**:
  - Custom MVC structure.
  - Middleware-based authentication.
  - PDO for database interactions.

## Tech Stack

- **Backend**: PHP 8.2
- **Database**: MySQL
- **Server**: Apache (via Docker image)
- **Frontend**: HTML, CSS, JavaScript (Vanilla)
- **Infrastructure**: Docker, Docker Compose
- **Dependency Management**: Composer

## Prerequisites

- Docker and Docker Compose installed on your machine.

---

## Getting Started (Open Source Users)

This section is for users who want to clone and run the project locally for development or testing.

### 1. Clone the repository

```bash
git clone https://github.com/ToTt0G/PHP-MySQL-Task-List-with-Auth.git
cd PHP-MySQL-Task-List-with-Auth
```

### 2. Environment Configuration

Create a `.env` file in the root directory:

```env
DB_HOST=db
MYSQL_DATABASE=tasks_db
MYSQL_USER=user
MYSQL_PASSWORD=password
MYSQL_ROOT_PASSWORD=root_password
```

### 3. Start the Application

```bash
docker compose up -d --build
```

This will:
- Start the MySQL database container
- Build the PHP/Apache container
- Install PHP dependencies via Composer
- Auto-initialize database tables on first run

### 4. Access the Application

Open your browser: **http://localhost:8081**

### Docker Commands (Development)

| Command | Description |
|---------|-------------|
| `docker compose restart` | Restart all containers |
| `docker compose restart task-app-environment` | Restart only the app (keeps DB running) |
| `docker compose up -d --no-deps --build task-app-environment` | Rebuild app image only |
| `docker compose down -v` | **⚠️ Wipe database** and stop containers |

> **Note**: Code changes sync instantly via bind mount - no rebuild needed for PHP changes.

---

## Developer Deployment (Server)

This section is for deploying to the production server environment.

### Architecture

| Question | Development | Production |
|----------|-------------|------------|
| Where does **Code** live? | Bind Mount (`./:/var/www/html`) | Baked into Docker image |
| Where does **Data** live? | Docker Volume (ephemeral) | `/mnt/fast_data/tasks-app_db` (SSD) |
| Where do **Secrets** live? | `.env` (local) | `/mnt/code/project/.env` (manual) |

### First-Time Setup (Server)

1. **SSH into the server** and create the production `.env`:

   ```bash
   ssh ryder@192.168.1.XX
   cd /mnt/code/PHP-MySQL-Task-List-with-Auth
   nano .env  # Paste production secrets here
   ```

### Deploy

Build the image from NAS files and start containers using the fast SSD:

```bash
docker compose -f docker-compose.prod.yml up -d --build
```

- `-f docker-compose.prod.yml`: Uses production config
- `--build`: Bakes latest code into a fresh image

### Production Commands

| Command | Description |
|---------|-------------|
| `docker compose -f docker-compose.prod.yml up -d --build` | Deploy/rebuild |
| `docker compose -f docker-compose.prod.yml restart` | Restart containers |
| `docker compose -f docker-compose.prod.yml logs -f` | View logs |
| `docker compose -f docker-compose.prod.yml down` | Stop containers |

> **⚠️ Important**: Database is stored on `/mnt/fast_data/tasks-app_db`, NOT the NAS. Never map DB volumes to `./`.

## Project Structure

```
├── public/                 # Publicly accessible files (Web Root)
│   ├── assets/             # CSS, JS, Images
│   └── index.php           # Entry point
├── src/                    # Source code
│   ├── config/             # Configuration (Database connection)
│   ├── Controllers/        # Request handlers (Auth, Tasks, Admin, Views)
│   ├── Helpers/            # Utilities (CSRF, Assets)
│   ├── Middleware/         # Request middleware (Authentication)
│   ├── Models/             # Database interactions (Users, Tasks, Sessions)
│   ├── Views/              # HTML Templates
│   └── Router.php          # Routing logic
├── docker-compose.yml      # Docker Compose service definitions
├── Dockerfile              # PHP/Apache Docker image definition
└── composer.json           # PHP Dependencies
```

## API Endpoints

The application exposes several API endpoints for frontend interaction:

### Auth

- `POST /api/auth/register`: Register a new user.
- `POST /api/auth/login`: Login.
- `POST /api/auth/logout`: Logout.

### Tasks

- `GET /api/tasks`: Get all tasks for the logged-in user.
- `DELETE /api/tasks`: Clear all tasks for the logged-in user.
- `POST /api/tasks`: Create a new task (requires `task` field).
- `POST /api/tasks` (with `edit_task_id`): Update a task.
- `POST /api/tasks` (with `delete_task_id`): Delete a task.
- `GET /api/tasks?count=<n>`: Poll for task updates (Short Polling).

### Users

- `GET /api/users/current`: Get current logged-in user info.

### Admin (Protected)

- `GET /api/admin/all-sessions`: List all active sessions.
- `GET /api/admin/all-tasks`: List all tasks in the system.
- `GET /api/admin/all-users`: List all registered users.
- `GET /api/admin/users/current`: Get user details by ID (extracted from URL, typically for `current` user in context of admin view logic).

## Development

- **Database Initialization**: The database tables (`users`, `tasks`, `sessions`) are automatically created if they don't exist when the application starts (defined in `src/config/db_config.php`).
- **Admin Access**: To access admin features, register a user with the email `rainryder4@gmail.com`.
