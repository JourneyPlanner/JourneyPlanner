$downloadUrl = "https://github.com/tus/tusd/releases/latest/download/tusd_windows_amd64.zip"
$outputPath = ".\tusd.zip"
$executablePath = ".\tusd_windows_amd64\tusd.exe"

if (-not (Test-Path $executablePath)) {
    Invoke-WebRequest -Uri $downloadUrl -OutFile $outputPath
    Expand-Archive -Path $outputPath -DestinationPath .
    Remove-Item -Path $outputPath
}

# Execute tusd
Start-Process -FilePath .\tusd_windows_amd64\tusd.exe -ArgumentList "-hooks-http http://localhost:8000/api/upload -hooks-http-forward-headers Authorization -hooks-enabled-events pre-create,post-finish" -NoNewWindow -Wait
