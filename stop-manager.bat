@echo off
echo Menghentikan semua proses ManagerServer...
taskkill /IM ManagerServer.exe /F >nul 2>&1
if errorlevel 1 (
  echo Tidak ada ManagerServer yang berjalan.
) else (
  echo ManagerServer dihentikan.
)
