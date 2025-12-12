DROP TABLE IF EXISTS completed_procedures;
DROP TABLE IF EXISTS appointments;
DROP TABLE IF EXISTS services;
DROP TABLE IF EXISTS employees;
DROP TABLE IF EXISTS specializations;
DROP TABLE IF EXISTS service_categories;

CREATE TABLE specializations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL UNIQUE,
    description TEXT
);

CREATE TABLE service_categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL UNIQUE,
    description TEXT
);

CREATE TABLE employees (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    first_name TEXT NOT NULL,
    last_name TEXT NOT NULL,
    middle_name TEXT,
    specialization_id INTEGER NOT NULL,
    salary_percentage REAL NOT NULL DEFAULT 30.0 CHECK (salary_percentage >= 0 AND salary_percentage <= 100),
    is_active INTEGER NOT NULL DEFAULT 1 CHECK (is_active IN (0, 1)),
    hire_date DATE NOT NULL DEFAULT (date('now')),
    dismissal_date DATE,
    phone TEXT,
    email TEXT,
    FOREIGN KEY (specialization_id) REFERENCES specializations(id) ON DELETE RESTRICT,
    CHECK (dismissal_date IS NULL OR dismissal_date >= hire_date)
);

CREATE TABLE services (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    duration_minutes INTEGER NOT NULL DEFAULT 30 CHECK (duration_minutes > 0),
    price REAL NOT NULL DEFAULT 0.0 CHECK (price >= 0),
    category_id INTEGER NOT NULL,
    description TEXT,
    FOREIGN KEY (category_id) REFERENCES service_categories(id) ON DELETE RESTRICT
);

CREATE TABLE appointments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    employee_id INTEGER NOT NULL,
    service_id INTEGER NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    patient_name TEXT NOT NULL,
    patient_phone TEXT,
    status TEXT NOT NULL DEFAULT 'scheduled' CHECK (status IN ('scheduled', 'completed', 'cancelled', 'no_show')),
    created_at DATETIME NOT NULL DEFAULT (datetime('now')),
    notes TEXT,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE RESTRICT,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE RESTRICT,
    CHECK (appointment_date >= date('now', '-1 day'))
);

CREATE TABLE completed_procedures (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    appointment_id INTEGER,
    employee_id INTEGER NOT NULL,
    service_id INTEGER NOT NULL,
    procedure_date DATE NOT NULL DEFAULT (date('now')),
    procedure_time TIME NOT NULL DEFAULT (time('now')),
    patient_name TEXT NOT NULL,
    notes TEXT,
    FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE SET NULL,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE RESTRICT,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE RESTRICT
);

INSERT INTO specializations (name, description) VALUES
('Терапевт', 'Терапевтическая стоматология'),
('Хирург', 'Хирургическая стоматология'),
('Ортодонт', 'Исправление прикуса и выравнивание зубов'),
('Ортопед', 'Протезирование зубов'),
('Пародонтолог', 'Лечение заболеваний десен'),
('Имплантолог', 'Установка зубных имплантатов');

INSERT INTO service_categories (name, description) VALUES
('Имплантация', 'Услуги по установке имплантатов'),
('Терапевтическая стоматология', 'Лечение кариеса, пульпита, периодонтита'),
('Хирургическая стоматология', 'Удаление зубов, операции'),
('Ортодонтия', 'Исправление прикуса, установка брекетов'),
('Ортопедия', 'Протезирование зубов'),
('Пародонтология', 'Лечение заболеваний пародонта'),
('Гигиена', 'Профессиональная чистка зубов');

INSERT INTO employees (first_name, last_name, middle_name, specialization_id, salary_percentage, is_active, hire_date, phone, email) VALUES
('Сергей', 'Иванов', 'Викторович', 1, 35.0, 1, '2020-01-15', '+7-901-111-22-33', 'ivanov@clinic.ru'),
('Анна', 'Федорова', 'Михайловна', 2, 40.0, 1, '2019-03-20', '+7-901-222-33-44', 'fedorova@clinic.ru'),
('Михаил', 'Соколов', 'Николаевич', 3, 30.0, 1, '2021-06-10', '+7-901-333-44-55', 'sokolov@clinic.ru'),
('Татьяна', 'Лебедева', 'Дмитриевна', 1, 32.5, 1, '2020-09-01', '+7-901-444-55-66', 'lebedeva@clinic.ru'),
('Андрей', 'Смирнов', 'Александрович', 6, 45.0, 1, '2018-11-05', '+7-901-555-66-77', 'smirnov@clinic.ru'),
('Наталья', 'Кузнецова', 'Владимировна', 4, 28.0, 0, '2019-05-12', '+7-901-666-77-88', 'kuznetsova@clinic.ru');

UPDATE employees SET dismissal_date = '2023-12-31', is_active = 0 WHERE id = 6;

INSERT INTO services (name, duration_minutes, price, category_id, description) VALUES
('Консультация', 30, 1000.0, 2, 'Первичная консультация стоматолога'),
('Лечение кариеса', 60, 3500.0, 2, 'Лечение кариеса одного зуба'),
('Лечение пульпита', 90, 5500.0, 2, 'Эндодонтическое лечение'),
('Удаление зуба сложное', 60, 4500.0, 3, 'Удаление зуба с осложнениями'),
('Установка имплантата', 120, 35000.0, 1, 'Установка одного имплантата'),
('Профессиональная чистка', 60, 3000.0, 7, 'Профессиональная гигиена полости рта'),
('Установка брекет-системы', 120, 50000.0, 4, 'Установка брекетов на одну челюсть'),
('Лечение пародонтита', 60, 4000.0, 6, 'Лечение заболеваний пародонта'),
('Коронка керамическая', 90, 25000.0, 5, 'Изготовление и установка керамической коронки'),
('Имплантация с коронкой', 150, 60000.0, 1, 'Полный комплекс: имплантат + коронка'),
('Отбеливание зубов', 90, 15000.0, 7, 'Профессиональное отбеливание зубов'),
('Виниры керамические', 120, 35000.0, 5, 'Изготовление и установка керамических виниров');

INSERT INTO appointments (employee_id, service_id, appointment_date, appointment_time, patient_name, patient_phone, status, created_at) VALUES
(1, 1, '2025-01-15', '10:00', 'Смирнов А.В.', '+7-902-111-22-33', 'completed', '2025-01-10 14:30:00'),
(1, 2, '2025-01-15', '11:00', 'Кузнецова Е.П.', '+7-902-222-33-44', 'completed', '2025-01-11 09:15:00'),
(2, 4, '2025-01-16', '14:00', 'Попов И.С.', '+7-902-333-44-55', 'scheduled', '2025-01-12 16:20:00'),
(3, 9, '2025-01-17', '10:00', 'Васильева М.А.', '+7-902-444-55-66', 'scheduled', '2025-01-13 11:00:00'),
(4, 1, '2025-01-15', '15:00', 'Петров Д.Н.', '+7-902-555-66-77', 'completed', '2025-01-10 18:00:00'),
(5, 6, '2025-01-18', '09:00', 'Соколов В.И.', '+7-902-666-77-88', 'scheduled', '2025-01-14 10:30:00'),
(1, 8, '2025-01-16', '10:00', 'Михайлов А.Б.', '+7-902-777-88-99', 'scheduled', '2025-01-13 15:45:00'),
(4, 2, '2025-01-16', '16:00', 'Федорова О.Л.', '+7-902-888-99-00', 'scheduled', '2025-01-14 09:00:00'),
(2, 5, '2025-01-17', '11:00', 'Николаев С.В.', '+7-902-999-00-11', 'scheduled', '2025-01-14 14:20:00'),
(5, 12, '2025-01-19', '10:00', 'Орлов П.М.', '+7-902-000-11-22', 'scheduled', '2025-01-15 12:00:00');

INSERT INTO completed_procedures (appointment_id, employee_id, service_id, procedure_date, procedure_time, patient_name, notes) VALUES
(1, 1, 1, '2025-01-15', '10:00', 'Смирнов А.В.', 'Консультация проведена, назначено лечение'),
(2, 1, 2, '2025-01-15', '11:15', 'Кузнецова Е.П.', 'Кариес вылечен, установлена пломба'),
(5, 4, 1, '2025-01-15', '15:00', 'Петров Д.Н.', 'Консультация, рекомендована чистка'),
(NULL, 1, 8, '2025-01-10', '14:00', 'Иванов К.С.', 'Профессиональная чистка выполнена'),
(NULL, 2, 4, '2025-01-12', '16:00', 'Лебедев А.П.', 'Удален зуб мудрости'),
(NULL, 4, 3, '2025-01-13', '10:00', 'Семенова Л.В.', 'Лечение пульпита завершено'),
(NULL, 5, 6, '2025-01-11', '11:00', 'Григорьев М.Н.', 'Имплантат установлен успешно'),
(NULL, 1, 2, '2025-01-14', '13:00', 'Тихонов В.А.', 'Лечение кариеса, пломба установлена'),
(NULL, 3, 9, '2025-01-08', '10:00', 'Романова И.С.', 'Брекет-система установлена'),
(NULL, 4, 8, '2025-01-09', '15:00', 'Зайцев Д.О.', 'Профессиональная чистка');

CREATE INDEX idx_employees_specialization ON employees(specialization_id);
CREATE INDEX idx_employees_active ON employees(is_active);
CREATE INDEX idx_services_category ON services(category_id);
CREATE INDEX idx_appointments_employee ON appointments(employee_id);
CREATE INDEX idx_appointments_date ON appointments(appointment_date);
CREATE INDEX idx_appointments_status ON appointments(status);
CREATE INDEX idx_completed_procedures_employee ON completed_procedures(employee_id);
CREATE INDEX idx_completed_procedures_date ON completed_procedures(procedure_date);
CREATE INDEX idx_completed_procedures_appointment ON completed_procedures(appointment_id);
