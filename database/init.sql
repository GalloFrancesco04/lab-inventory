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
  role        INT NOT NULL
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
  CONSTRAINT ck_objecttype_quantity_nonneg CHECK (quantity >= 0)
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


-- ========================


-- =========================
-- 1. POPOLAMENTO UTENTI (Staff/Tecnici)
-- =========================
-- Role: 1 = Admin, 2 = Tecnico
INSERT INTO `user` (email, pass, role) VALUES 
('admin@lab.it', '$2a$12$srdWkI8c3ADcYQHpHH7OBOHUpD00Oq4mLKexaXO.uhntNlEYZJZe2', 1),
('luca.tecnico@lab.it', '$2a$12$vQz71VwpxqFdJVX5kzek1eLj846rYfp8RFEFjMbzhAGn7V3Aui6vW', 2);

-- =========================
-- 2. POPOLAMENTO CLIENTI (Studenti/Professori)
-- =========================
-- Nota: Mario Sale era già nell'esempio, aggiungiamo altri
INSERT INTO client (email, name, surname, state) VALUES 
('giulia.bianchi@studenti.it', 'Giulia', 'Bianchi', 1), -- Studente attivo
('prof.verdi@uni.it', 'Luigi', 'Verdi', 1),             -- Professore attivo
('marco.neri@studenti.it', 'Marco', 'Neri', 0);         -- Studente sospeso

-- =========================
-- 3. POPOLAMENTO TIPI OGGETTO (Catalogo)
-- =========================
INSERT INTO objecttype (code, name, description, model, brand, quantity) VALUES 
('NB-DELL-XPS15', 'Laptop Prestazioni Alte', 'Laptop per rendering e calcolo', 'XPS 15', 'Dell', 3),
('MS-LOGI-B100', 'Mouse Ottico USB', 'Mouse base con filo', 'B100', 'Logitech', 10),
('DEV-ARD-UNO', 'Microcontrollore', 'Scheda per prototipazione elettronica', 'Uno R3', 'Arduino', 20),
('VID-EPS-EB', 'Videoproiettore', 'Proiettore HDMI 1080p', 'EB-FH06', 'Epson', 2);

-- =========================
-- 4. POPOLAMENTO OGGETTI FISICI (Asset)
-- =========================
-- Stato: 1 = Disponibile, 2 = In Prestito, 3 = In Manutenzione, 4 = Rotto/Dismesso

-- Laptop Dell (3 unità)
INSERT INTO object_ (object_type_code, state, location) VALUES 
('NB-DELL-XPS15', 1, 'Armadio A - Ripiano 1'), -- ID 1 (Disponibile)
('NB-DELL-XPS15', 2, 'In uso esterno'),        -- ID 2 (In prestito)
('NB-DELL-XPS15', 3, 'Laboratorio Riparazioni'); -- ID 3 (In manutenzione)

-- Mouse (Alcuni esempi)
INSERT INTO object_ (object_type_code, state, location) VALUES 
('MS-LOGI-B100', 1, 'Cesta Accessori'), -- ID 4
('MS-LOGI-B100', 2, 'In uso esterno'),  -- ID 5 (In prestito con il laptop 2)
('MS-LOGI-B100', 1, 'Cesta Accessori'); -- ID 6

-- Proiettore
INSERT INTO object_ (object_type_code, state, location) VALUES 
('VID-EPS-EB', 1, 'Armadio B'); -- ID 7

-- =========================
-- 5. POPOLAMENTO PRESTITI (Loans)
-- =========================

-- PRESTITO 1: Concluso (Restituito). Giulia Bianchi ha preso un Proiettore.
INSERT INTO loans (start_date, end_date, return_date, client_id, notes, quantity, state) VALUES 
('2023-10-01', '2023-10-02', '2023-10-02', 2, 'Restituito con cavo HDMI mancante', 1, 0); 
-- ID Loan sarà 1. Client ID 2 è Giulia.

-- PRESTITO 2: Attivo. Il Prof. Verdi ha un Laptop e un Mouse.
INSERT INTO loans (start_date, end_date, return_date, client_id, notes, quantity, state) VALUES 
(CURDATE(), DATE_ADD(CURDATE(), INTERVAL 7 DAY), NULL, 3, 'Prestito per conferenza', 2, 1);
-- ID Loan sarà 2. Client ID 3 è Prof. Verdi.

-- =========================
-- 6. POPOLAMENTO DETTAGLIO PRESTITI (LoanedObjects)
-- =========================

-- Dettagli Prestito 1 (Giulia - Proiettore ID 7) - Nota: Ora è restituito, ma teniamo lo storico
INSERT INTO loanedobjects (object_type_code, object_id, loan_id, damaged) VALUES 
('VID-EPS-EB', 7, 1, 0);

-- Dettagli Prestito 2 (Prof. Verdi - Laptop ID 2 e Mouse ID 5)
INSERT INTO loanedobjects (object_type_code, object_id, loan_id, damaged) VALUES 
('NB-DELL-XPS15', 2, 2, 0),
('MS-LOGI-B100', 5, 2, 0);

-- =========================
-- 7. POPOLAMENTO MANUTENZIONI
-- =========================

-- Il Laptop ID 3 è rotto (Stato 3 in object_ table)
INSERT INTO maintenance (object_id, description, start_date, end_date, type, technician_id, phone_number, cost) VALUES 
(3, 'Schermo sfarfalla e pixel bruciati', DATE_SUB(CURDATE(), INTERVAL 2 DAY), NULL, 'Hardware', 2, '333-1234567', 0.00);
-- ID Maintenance sarà 1. Technician ID 2 è Luca.

-- =========================
-- 8. POPOLAMENTO OPERAZIONI DI MANUTENZIONE
-- =========================

-- Operazione 1: Diagnostica
INSERT INTO operations (maintenance_id, description, start_date, end_date, cost) VALUES 
(1, 'Diagnostica iniziale ed stress test GPU', DATE_SUB(CURDATE(), INTERVAL 2 DAY), DATE_SUB(CURDATE(), INTERVAL 2 DAY), 20.00);

-- Operazione 2: Ordine Pezzo
INSERT INTO operations (maintenance_id, description, start_date, end_date, cost) VALUES 
(1, 'Ordinato pannello LCD di ricambio', DATE_SUB(CURDATE(), INTERVAL 1 DAY), NULL, 150.00);