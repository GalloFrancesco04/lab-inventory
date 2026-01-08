CREATE DATABASE IF NOT EXISTS laboratorio_inventario;
USE laboratorio_inventario;

-- Schema SQL (MySQL). Esegui nell'ordine: prima tabelle "padre", poi "figlie".

-- =========================
-- USER
-- =========================
CREATE TABLE `user` (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  email       VARCHAR(255) NOT NULL UNIQUE,
  pass        VARCHAR(255) NOT NULL,
  role        INT NOT NULL,
);

-- =========================
-- CLIENT
-- =========================
CREATE TABLE client (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  email       VARCHAR(255) NOT NULL UNIQUE,
  name        VARCHAR(120) NOT NULL,
  surname     VARCHAR(120) NOT NULL,
  state       INT NOT NULL
);

-- Insert sample client
INSERT INTO client (email, name, surname, state) VALUES ('mario.sale@example.com', 'Mario', 'Sale', 1);

-- =========================
-- OBJECTTYPE, OBJECT
-- =========================
CREATE TABLE objecttype (
  code        VARCHAR(50) PRIMARY KEY,
  name        VARCHAR(120) NOT NULL,
  description TEXT,
  model       VARCHAR(120),
  brand       VARCHAR(120),
  quantity    INT NOT NULL DEFAULT 0,
  CONSTRAINT ck_objecttype_quantity_nonneg CHECK (quantity > 0)
);

CREATE TABLE object_ (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  object_type_code VARCHAR(50) NOT NULL,
  state           INT NOT NULL,
  location        VARCHAR(255),
  CONSTRAINT fk_object_objecttype
    FOREIGN KEY (object_type_code) REFERENCES objecttype(code)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
);

-- =========================
-- LOANS
-- =========================
CREATE TABLE loans (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  start_date  DATE NOT NULL,
  end_date    DATE NOT NULL,
  return_date DATE,
  client_id   INT NOT NULL,
  notes       TEXT,
  quantity    INT NOT NULL DEFAULT 1,
  state       BOOLEAN NOT NULL DEFAULT TRUE,
  CONSTRAINT fk_loans_client
    FOREIGN KEY (client_id) REFERENCES client(id)
    ON UPDATE CASCADE
    ON DELETE RESTRICT,
  CONSTRAINT ck_loans_quantity_nonneg CHECK (quantity >= 0),
  CONSTRAINT ck_loans_dates CHECK (end_date >= start_date),
  CONSTRAINT ck_loans_returndate CHECK (return_date IS NULL OR return_date >= start_date)
);

-- =========================
-- LOANEDOBJECTS
-- =========================
CREATE TABLE loanedobjects (
  object_type_code VARCHAR(50) NOT NULL,
  object_id        INT NOT NULL,
  loan_id          INT NOT NULL,
  damaged          BOOLEAN NOT NULL DEFAULT FALSE,
  PRIMARY KEY (object_id, loan_id),
  CONSTRAINT fk_loanedobjects_object
    FOREIGN KEY (object_id) REFERENCES object_(id)
    ON UPDATE CASCADE
    ON DELETE RESTRICT,
  CONSTRAINT fk_loanedobjects_loans
    FOREIGN KEY (loan_id) REFERENCES loans(id)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
  CONSTRAINT fk_loanedobjects_objecttype
    FOREIGN KEY (object_type_code) REFERENCES objecttype(code)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
);

-- =========================
-- MAINTENANCE, OPERATIONS
-- =========================
CREATE TABLE maintenance (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  object_id     INT NOT NULL,
  description   TEXT,
  start_date    DATE NOT NULL,
  end_date      DATE,
  type          VARCHAR(80),
  technician_id INT NOT NULL,
  phone_number  VARCHAR(30),
  cost          DECIMAL(12,2) NOT NULL DEFAULT 0,
  CONSTRAINT fk_maintenance_object
    FOREIGN KEY (object_id) REFERENCES object_(id)
    ON UPDATE CASCADE
    ON DELETE RESTRICT,
  CONSTRAINT fk_maintenance_technician
    FOREIGN KEY (technician_id) REFERENCES `user`(id)
    ON UPDATE CASCADE
    ON DELETE RESTRICT,
  CONSTRAINT ck_maintenance_cost_nonneg CHECK (cost >= 0),
  CONSTRAINT ck_maintenance_dates CHECK (end_date IS NULL OR end_date >= start_date)
);

CREATE TABLE operations (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  maintenance_id INT NOT NULL,
  description    TEXT,
  start_date     DATE NOT NULL,
  end_date       DATE,
  cost           DECIMAL(12,2) NOT NULL DEFAULT 0,
  CONSTRAINT fk_operations_maintenance
    FOREIGN KEY (maintenance_id) REFERENCES maintenance(id)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
  CONSTRAINT ck_operations_cost_nonneg CHECK (cost >= 0),
  CONSTRAINT ck_operations_dates CHECK (end_date IS NULL OR end_date >= start_date)
);


