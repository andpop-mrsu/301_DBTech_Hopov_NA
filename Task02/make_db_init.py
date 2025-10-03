#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import os
import csv
import sqlite3
from pathlib import Path

class DatabaseGenerator:
    def __init__(self, dataset_path="dataset", db_name="movies_rating.db"):
        self.dataset_path = Path(dataset_path)
        self.db_name = db_name
        self.sql_script = "db_init.sql"
        
        # Структура таблиц
        self.tables = {
            'movies': {
                'columns': [
                    ('id', 'INTEGER PRIMARY KEY'),
                    ('title', 'TEXT'),
                    ('year', 'INTEGER'),
                    ('genres', 'TEXT')
                ],
                'source_file': 'movies.csv',
                'has_auto_id': False
            },
            'ratings': {
                'columns': [
                    ('id', 'INTEGER PRIMARY KEY AUTOINCREMENT'),
                    ('user_id', 'INTEGER'),
                    ('movie_id', 'INTEGER'),
                    ('rating', 'REAL'),
                    ('timestamp', 'INTEGER')
                ],
                'source_file': 'ratings.csv',
                'has_auto_id': True
            },
            'tags': {
                'columns': [
                    ('id', 'INTEGER PRIMARY KEY AUTOINCREMENT'),
                    ('user_id', 'INTEGER'),
                    ('movie_id', 'INTEGER'),
                    ('tag', 'TEXT'),
                    ('timestamp', 'INTEGER')
                ],
                'source_file': 'tags.csv',
                'has_auto_id': True
            },
            'users': {
                'columns': [
                    ('id', 'INTEGER PRIMARY KEY'),
                    ('name', 'TEXT'),
                    ('email', 'TEXT'),
                    ('gender', 'TEXT'),
                    ('register_date', 'TEXT'),
                    ('occupation', 'TEXT')
                ],
                'source_file': 'users.csv',
                'has_auto_id': False
            }
        }

    def generate_sql_script(self):
        """Генерация SQL-скрипта"""
        print("Генерация SQL-скрипта...")
        
        with open(self.sql_script, 'w', encoding='utf-8') as f:
            # Удаление существующих таблиц
            f.write("-- Удаление существующих таблиц\n")
            for table_name in self.tables.keys():
                f.write(f"DROP TABLE IF EXISTS {table_name};\n")
            
            f.write("\n")
            
            # Создание таблиц
            f.write("-- Создание таблиц\n")
            for table_name, table_info in self.tables.items():
                columns_def = ", ".join([f"{col[0]} {col[1]}" for col in table_info['columns']])
                f.write(f"CREATE TABLE {table_name} ({columns_def});\n")
            
            f.write("\n")
            
            # Вставка данных
            f.write("-- Вставка данных\n")
            for table_name, table_info in self.tables.items():
                source_file = self.dataset_path / table_info['source_file']
                if source_file.exists():
                    columns = [col[0] for col in table_info['columns']]
                    
                    # Если используется AUTOINCREMENT, пропускаем первый столбец при вставке
                    if table_info['has_auto_id']:
                        columns_str = ", ".join(columns[1:])
                    else:
                        columns_str = ", ".join(columns)
                    
                    f.write(f"-- Данные для таблицы {table_name}\n")
                    
                    with open(source_file, 'r', encoding='utf-8') as csv_file:
                        reader = csv.reader(csv_file)
                        next(reader)  # Пропускаем заголовок
                        
                        for row in reader:
                            # Если используется AUTOINCREMENT, пропускаем первый столбец
                            if table_info['has_auto_id'] and len(row) > 0:
                                row = row[1:]
                            
                            # Экранирование специальных символов
                            escaped_row = []
                            for value in row:
                                if value is None or value == '':
                                    escaped_row.append("NULL")
                                else:
                                    escaped_value = str(value).replace("'", "''")
                                    escaped_row.append(f"'{escaped_value}'")
                            
                            values_str = ", ".join(escaped_row)
                            f.write(f"INSERT INTO {table_name} ({columns_str}) VALUES ({values_str});\n")
                    
                    f.write("\n")
                else:
                    print(f"Предупреждение: Файл {source_file} не найден")
        
        print(f"SQL-скрипт {self.sql_script} успешно создан!")
        return True

    def analyze_csv_structure(self):
        """Анализ структуры CSV файлов"""
        print("Анализ структуры CSV файлов...")
        
        for table_name, table_info in self.tables.items():
            source_file = self.dataset_path / table_info['source_file']
            if source_file.exists():
                with open(source_file, 'r', encoding='utf-8') as csv_file:
                    reader = csv.reader(csv_file)
                    header = next(reader)
                    
                    print(f"\nФайл: {table_info['source_file']}")
                    print(f"Заголовок: {header}")
                    print(f"Кол-во столбцов в CSV: {len(header)}")
                    print(f"Кол-во столбцов в таблице: {len(table_info['columns'])}")
                    print(f"Авто-ID: {table_info['has_auto_id']}")
                    
                    if len(header) != len(table_info['columns']):
                        print(f"⚠️  ВНИМАНИЕ: Несоответствие количества столбцов!")

    def create_database(self):
        """Создание базы данных напрямую из Python"""
        print("Создание базы данных...")
        
        # Удаляем существующую базу
        if os.path.exists(self.db_name):
            os.remove(self.db_name)
        
        # Создаем соединение с базой
        conn = sqlite3.connect(self.db_name)
        cursor = conn.cursor()
        
        try:
            # Создаем таблицы
            for table_name, table_info in self.tables.items():
                columns_def = ", ".join([f"{col[0]} {col[1]}" for col in table_info['columns']])
                cursor.execute(f"CREATE TABLE {table_name} ({columns_def})")
            
            # Вставляем данные
            for table_name, table_info in self.tables.items():
                source_file = self.dataset_path / table_info['source_file']
                if source_file.exists():
                    print(f"Загрузка данных в таблицу {table_name}...")
                    
                    with open(source_file, 'r', encoding='utf-8') as csv_file:
                        reader = csv.reader(csv_file)
                        header = next(reader)  # Пропускаем заголовок
                        
                        columns = [col[0] for col in table_info['columns']]
                        
                        # Определяем, какие столбцы использовать для вставки
                        if table_info['has_auto_id']:
                            # Пропускаем первый столбец (ID) для AUTOINCREMENT таблиц
                            insert_columns = columns[1:]
                            csv_columns_to_use = 1  # Пропускаем первый столбец в CSV
                        else:
                            insert_columns = columns
                            csv_columns_to_use = 0  # Используем все столбцы
                        
                        expected_columns = len(insert_columns)
                        placeholders = ", ".join(["?"] * expected_columns)
                        
                        batch_size = 1000
                        batch = []
                        row_count = 0
                        duplicate_count = 0
                        
                        for row in reader:
                            row_count += 1
                            
                            # Пропускаем первый столбец для AUTOINCREMENT таблиц
                            if table_info['has_auto_id'] and len(row) > 0:
                                row = row[1:]
                            
                            # Проверяем количество столбцов
                            if len(row) != expected_columns:
                                print(f"⚠️  Предупреждение: Строка {row_count} имеет {len(row)} столбцов вместо {expected_columns}")
                                
                                # Дополняем или обрезаем строку до нужного количества столбцов
                                if len(row) < expected_columns:
                                    # Добавляем NULL для недостающих столбцов
                                    row.extend([None] * (expected_columns - len(row)))
                                else:
                                    # Обрезаем лишние столбцы
                                    row = row[:expected_columns]
                            
                            # Обрабатываем пустые значения
                            processed_row = []
                            for i, value in enumerate(row):
                                if value == '':
                                    processed_row.append(None)
                                else:
                                    # Преобразуем типы данных
                                    column_type = table_info['columns'][i + csv_columns_to_use][1]
                                    if 'INTEGER' in column_type and value is not None:
                                        try:
                                            processed_row.append(int(value))
                                        except ValueError:
                                            processed_row.append(value)
                                    elif 'REAL' in column_type and value is not None:
                                        try:
                                            processed_row.append(float(value))
                                        except ValueError:
                                            processed_row.append(value)
                                    else:
                                        processed_row.append(value)
                            
                            batch.append(tuple(processed_row))
                            
                            if len(batch) >= batch_size:
                                try:
                                    cursor.executemany(
                                        f"INSERT INTO {table_name} ({', '.join(insert_columns)}) VALUES ({placeholders})",
                                        batch
                                    )
                                    batch = []
                                except sqlite3.IntegrityError as e:
                                    if "UNIQUE constraint failed" in str(e):
                                        print(f"⚠️  Обнаружены дубликаты, используем индивидуальную вставку...")
                                        self.insert_rows_individually(cursor, table_name, insert_columns, batch)
                                        batch = []
                                        duplicate_count += batch_size
                                    else:
                                        raise e
                        
                        # Вставляем оставшиеся данные
                        if batch:
                            try:
                                cursor.executemany(
                                    f"INSERT INTO {table_name} ({', '.join(insert_columns)}) VALUES ({placeholders})",
                                    batch
                                )
                            except sqlite3.IntegrityError as e:
                                if "UNIQUE constraint failed" in str(e):
                                    print(f"⚠️  Обнаружены дубликаты в последнем батче...")
                                    self.insert_rows_individually(cursor, table_name, insert_columns, batch)
                                    duplicate_count += len(batch)
                                else:
                                    raise e
                        
                        print(f"   Загружено {row_count} записей")
                        if duplicate_count > 0:
                            print(f"   Пропущено {duplicate_count} дубликатов")
            
            conn.commit()
            print(f"База данных {self.db_name} успешно создана!")
            
            # Проверяем содержимое базы
            self.verify_database_content(cursor)
            
            return True
            
        except Exception as e:
            print(f"Ошибка при создании базы данных: {e}")
            import traceback
            traceback.print_exc()
            conn.rollback()
            return False
        finally:
            conn.close()

    def insert_rows_individually(self, cursor, table_name, columns, rows):
        """Вставка строк по одной с обработкой дубликатов"""
        placeholders = ", ".join(["?"] * len(columns))
        
        for row in rows:
            try:
                cursor.execute(
                    f"INSERT INTO {table_name} ({', '.join(columns)}) VALUES ({placeholders})",
                    row
                )
            except sqlite3.IntegrityError as e:
                if "UNIQUE constraint failed" in str(e):
                    # Пропускаем дубликат
                    continue
                else:
                    raise e

    def verify_database_content(self, cursor):
        """Проверка содержимого базы данных"""
        print("\nПроверка содержимого базы данных:")
        
        for table_name in self.tables.keys():
            try:
                cursor.execute(f"SELECT COUNT(*) FROM {table_name}")
                count = cursor.fetchone()[0]
                print(f"  {table_name}: {count} записей")
                
                # Покажем первые 5 ID для проверки
                cursor.execute(f"SELECT id FROM {table_name} ORDER BY id LIMIT 5")
                first_ids = [str(row[0]) for row in cursor.fetchall()]
                print(f"    Первые ID: {', '.join(first_ids)}")
                
            except Exception as e:
                print(f"  {table_name}: ошибка - {e}")

def main():
    generator = DatabaseGenerator()
    
    # Проверяем существование каталога dataset
    if not generator.dataset_path.exists():
        print(f"Ошибка: Каталог {generator.dataset_path} не найден!")
        print("Пожалуйста, создайте каталог 'dataset' и поместите в него CSV файлы:")
        print("- movies.csv")
        print("- ratings.csv")
        print("- tags.csv")
        print("- users.csv")
        return 1
    
    # Анализируем структуру CSV файлов
    generator.analyze_csv_structure()
    
    # Генерируем SQL-скрипт
    generator.generate_sql_script()
    
    # Создаем базу данных
    success = generator.create_database()
    
    return 0 if success else 1

if __name__ == "__main__":
    exit(main())