#!/usr/bin/env bash
# Runs every backend test against a fresh copy of the seed database,
# so the tests never change the application's data. Needs the Docker stack running.
set -euo pipefail
cd "$(dirname "$0")/../.."

set -a; source .env; set +a
TEST_DB=laboratorio_inventario_test

# Recreate the test database from the same schema and seed data as the app
reset_test_db() {
  {
    echo "DROP DATABASE IF EXISTS $TEST_DB;"
    sed "s/\blaboratorio_inventario\b/$TEST_DB/g" database/init.sql
    echo "GRANT ALL PRIVILEGES ON $TEST_DB.* TO '$DB_USER'@'%';"
  } | docker compose exec -T db mysql -uroot -p"$DB_ROOT_PASSWORD" 2>/dev/null
}

failures=0
for test in $(cd backend && find tests -name "*Test.php" | sort); do
  # Every test file starts from the seed data, so tests cannot affect each other
  reset_test_db
  echo "### $test"
  output=$(docker compose exec -T -e DB_NAME="$TEST_DB" php php "$test" 2>&1 || true)
  echo "$output"
  if grep -qE "❌|\[FAILED\]|Fatal error" <<<"$output"; then
    failures=$((failures + 1))
  fi
done

echo
echo "Test files with failures: $failures"
[ "$failures" -eq 0 ]
