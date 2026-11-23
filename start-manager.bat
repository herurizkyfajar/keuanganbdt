@echo off
rem Jalankan Manager (web UI) di browser default.
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0start-manager.ps1" %*
