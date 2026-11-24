# Lab Inventory

A web app for running a university computer lab: it keeps track of the equipment, who has borrowed what, and what is being repaired.

- **Administrators** manage the inventory, the clients (students and staff) and their loans, and send broken items to maintenance.
- **Technicians** see the maintenances assigned to them, log the work done and its cost, and close them.

Team project for the Software Engineering course (Ingegneria del Software, 2025/26) at the University of Parma, by Francesco Gallo (frontend), Ivan (HTTP layer and controllers), Morris Roberti (handlers and domain model) and Simone De Rensis (database and repositories).

**Stack:** PHP 8.3 and MySQL 8 on Docker, with a plain JavaScript frontend (MVP pattern, no frameworks).

**Docs:** [user manual](docs/USER_MANUAL.md), [API](docs/API.md), [design document](docs/Documentation.pdf), [diagrams](docs/diagrams).

## Build

Create a `.env` file in the root folder:

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

## Tests

With the stack running, `backend/tests/run.sh` runs every backend test. Each test file starts from a fresh copy of the seed data in a separate `laboratorio_inventario_test` database, so the application's data is never touched.

## Contribute

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
