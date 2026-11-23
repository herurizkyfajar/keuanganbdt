param(
    [string]$DataPath = ""
)

$ErrorActionPreference = "Stop"
$root = Split-Path -Parent $MyInvocation.MyCommand.Path
$exe  = Join-Path $root "server\ManagerServer.exe"

if (-not (Test-Path $exe)) {
    Write-Host "ManagerServer.exe tidak ditemukan di $exe" -ForegroundColor Red
    exit 1
}

function Test-PortOpen([int]$Port) {
    $client = New-Object System.Net.Sockets.TcpClient
    try {
        $client.Connect("127.0.0.1", $Port)
        $client.Close()
        return $true
    } catch {
        return $false
    }
}

function Test-Manager([int]$Port) {
    try {
        $r = Invoke-WebRequest -Uri "http://127.0.0.1:$Port" -UseBasicParsing -TimeoutSec 2
        return ($r.StatusCode -eq 200 -and $r.Content -match "Manager|Businesses")
    } catch {
        return $false
    }
}

# Cari port: pakai server yang sudah jalan, atau port bebas mulai 55667
$port = 0
for ($p = 55667; $p -le 55676; $p++) {
    if (Test-PortOpen $p) {
        if (Test-Manager $p) { $port = $p; break }
    } else {
        $port = $p
        break
    }
}

if ($port -eq 0) {
    Write-Host "Tidak ada port kosong di 55667-55676." -ForegroundColor Red
    exit 1
}

$alreadyRunning = Test-Manager $port
$url = "http://127.0.0.1:$port"

if (-not $alreadyRunning) {
    $args = @("--urls=$url", "--edition=desktop")
    if ($DataPath -ne "") {
        $args += "--path=$DataPath"
    }

    Write-Host "Menjalankan ManagerServer di $url ..."
    Start-Process -FilePath $exe `
        -ArgumentList $args `
        -WorkingDirectory (Join-Path $root "server") `
        -WindowStyle Hidden | Out-Null

    # Tunggu sampai server siap (maks ~60 detik)
    $ready = $false
    for ($i = 0; $i -lt 120; $i++) {
        Start-Sleep -Milliseconds 500
        if (Test-Manager $port) { $ready = $true; break }
    }

    if (-not $ready) {
        Write-Host "Server tidak merespons di $url setelah 60 detik." -ForegroundColor Red
        exit 1
    }
} else {
    Write-Host "ManagerServer sudah berjalan di $url"
}

Write-Host "Membuka browser: $url" -ForegroundColor Green
Start-Process $url
