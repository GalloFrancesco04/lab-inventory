# INGSW-2526-T14

# Build

create an .env file in the root folder

```bash
PHP_PORT=8080
DB_PORT=3306
DB_ROOT_PASSWORD=change_me
DB_USER=app_user
DB_PASSWORD=change_me
DB_NAME=laboratorio_inventario
```

`DB_NAME` must be `laboratorio_inventario`: `database/init.sql` creates and seeds that database.
The first time you run the project use

`docker compose up --build -d `

For regular startup, you can simply use:

`docker compose up -d `

To access the application go to http://localhost:8080/frontend

Seeded accounts:

| Role | Email | Password |
|------|-------|----------|
| Admin | `admin@lab.it` | `admin123` |
| Technician | `luca.tecnico@lab.it` | `tech123` |

To reset the database to the seed data, run `docker compose down -v` and start again.

# Tests

With the stack running, `backend/tests/run.sh` runs every backend test. Each test file starts from a fresh copy of the seed data in a separate `laboratorio_inventario_test` database, so the application's data is never touched.

# Contribute

> To contribute to the project use the following commit convention

```
feat: new feature
fix: bug fixing
docs: documentation
style: style changes (no logic)
refactor: code changes that do not affect the core logic
test: creation and changes of tests
chore: general maintenance (build, dependencies)
```
