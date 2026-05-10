#!/usr/bin/env python3
"""Deploy script for Золотая Рыбка website to reg.ru hosting via SFTP"""
import paramiko
import os
import sys

HOST = "server141.hosting.reg.ru"
PORT = 22
USER = "u3510525"
PASS = "U8Ti5Eq1olr8Md0M"
REMOTE_ROOT = "/var/www/u3510525/data/www/shashliki-borodino.ru"
LOCAL_ROOT = os.path.dirname(os.path.abspath(__file__))

EXCLUDE = {'.git', '.DS_Store', '__pycache__', 'deploy.py', 'node_modules', '.gitignore'}
INCLUDE_EXTS = {'.html', '.php', '.css', '.js', '.json', '.htaccess', '.png', '.jpg', '.jpeg', '.webp', '.gif', '.svg', '.ico', '.txt', '.md', '.gitkeep'}

def should_include(path):
    name = os.path.basename(path)
    if name in EXCLUDE: return False
    if name.startswith('.'): return name == '.htaccess' or name == '.gitkeep'
    _, ext = os.path.splitext(name)
    return not ext or ext.lower() in INCLUDE_EXTS

def get_files():
    files = []
    for root, dirs, filenames in os.walk(LOCAL_ROOT):
        dirs[:] = [d for d in dirs if d not in EXCLUDE and not d.startswith('.')]
        for fname in filenames:
            if should_include(fname):
                local_path = os.path.join(root, fname)
                rel_path = os.path.relpath(local_path, LOCAL_ROOT)
                files.append((local_path, rel_path))
    return files

def sftp_mkdir(sftp, path):
    parts = path.split('/')
    current = ''
    for part in parts:
        if not part: continue
        current = current + '/' + part
        try:
            sftp.stat(current)
        except FileNotFoundError:
            try:
                sftp.mkdir(current)
                print(f'  mkdir: {current}')
            except: pass

def main():
    print(f"🔌 Подключаемся к {HOST}...")
    
    transport = paramiko.Transport((HOST, PORT))
    transport.connect(username=USER, password=PASS)
    sftp = paramiko.SFTPClient.from_transport(transport)
    
    print(f"✅ Подключено! Загружаем файлы в {REMOTE_ROOT}")
    
    # Ensure root exists
    try: sftp.stat(REMOTE_ROOT)
    except: sftp.mkdir(REMOTE_ROOT)
    
    files = get_files()
    total = len(files)
    print(f"📦 Файлов для загрузки: {total}\n")
    
    for i, (local_path, rel_path) in enumerate(files, 1):
        remote_path = REMOTE_ROOT + '/' + rel_path.replace('\\', '/')
        remote_dir = os.path.dirname(remote_path)
        sftp_mkdir(sftp, remote_dir)
        try:
            sftp.put(local_path, remote_path)
            print(f"  [{i:3}/{total}] ✅ {rel_path}")
        except Exception as e:
            print(f"  [{i:3}/{total}] ❌ {rel_path}: {e}")
    
    # Set permissions for menu-data.json and uploads directory
    try:
        sftp.chmod(REMOTE_ROOT + '/menu-data.json', 0o666)
        sftp.chmod(REMOTE_ROOT + '/uploads', 0o755)
        print("\n🔐 Права на файлы установлены")
    except: pass
    
    sftp.close()
    transport.close()
    print(f"\n🎉 Деплой завершён! Сайт: https://shashliki-borodino.ru")
    print(f"🔑 Админка: https://shashliki-borodino.ru/admin/")
    print(f"   Логин: admin | Пароль: Gold2026!")

if __name__ == '__main__':
    main()
