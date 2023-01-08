import pyodbc
import traceback
import sys
import psycopg2
from psycopg2 import Error

list_tables_access=[]
dict_struct_access={}
dict_struct_pg={}
conn = pyodbc.connect(r'Driver={Microsoft Access Driver (*.mdb, *.accdb)};DBQ=C:\\DEV\\GEOL\\DBCartoUpdateXP.accdb;')
cursor = conn.cursor()


def get_table_col_names(con, table_str):
    col_names = []
    try:
        cur = con.cursor()
        cur.execute("select * from " + table_str + " LIMIT 0")
        for desc in cur.description:
            col_names.append(desc[0])        
        cur.close()
        return col_names
    except psycopg2.Error as e:
        print(e)
        
for row in cursor.tables():
    list_tables_access.append(row.table_name)
    
for table in list_tables_access:
    print("=======")
    print(table)
    print("=======")
    dict_struct_access[table]=[]
    try:
        for row in cursor.columns(table=table):
            try:
                print(row.column_name)
                dict_struct_access[table].append(row.column_name)
            except Exception as e:
                #TODO more efficent exception capture
                print("Issue in column")
                traceback.print_exc()
    except Exception as e:
        #TODO more efficent exception capture
        print("Issue in table")
        traceback.print_exc()

#print(dict_struct_access)

conn_pg = psycopg2.connect(user="",
                                  password='',
                                  host="",
                                  port="",
                                  database="")
cursor_pg = conn_pg.cursor()
                                  
cursor_pg.execute("""SELECT table_name FROM information_schema.tables
       WHERE table_schema = 'public'""")
for row in cursor_pg.fetchall():
    print(row[0])
    col_list=get_table_col_names(conn_pg,row[0])
    dict_struct_pg[row[0]]=col_list
print(dict_struct_pg)
print("done")