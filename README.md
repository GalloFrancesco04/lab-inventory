# INGSW-2526-T14

# Build

create an .env file in the root folder

```bash
PHP_PORT
DB_PORT
DB_ROOT_PASSWORD
DB_USER
DB_PASSWORD
DB_NAME
```
The first time you run the project use

`docker compose up --build -d `

For regular startup, you can simply use:

`docker compose up -d `

To access the application go to http://localhost:8080/

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
