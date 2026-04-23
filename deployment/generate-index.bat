@echo off
set "outputFile=file-index.json"

echo ==========================================
echo    FILE INDEX GENERATOR BY ANTIGRAVITY
echo ==========================================
echo.
echo Scanning current folder and subfolders...
echo Target: %cd%
echo Output: %outputFile%
echo.

powershell -Command ^
    "$results = Get-ChildItem -Recurse | Where-Object { $_.Name -ne '%outputFile%' -and $_.FullName -notlike '*\.*' -and $_.FullName -notlike '*\node_modules\*' -and $_.FullName -notlike '*\vendor\*' } | Select-Object " ^
    "    @{Name='name'; Expression={$_.Name}}, " ^
    "    @{Name='path'; Expression={$_.FullName.Replace($pwd.Path + '\', '').Replace('\', '/')}}, " ^
    "    @{Name='type'; Expression={if($_.PSIsContainer){'folder'}else{'file'}}}, " ^
    "    @{Name='size'; Expression={if($_.PSIsContainer){0}else{$_.Length}}}, " ^
    "    @{Name='last_modified'; Expression={$_.LastWriteTime.ToString('yyyy-MM-dd HH:mm:ss')}}; " ^
    "$results | ConvertTo-Json -Depth 100 > %outputFile%"

echo.
echo ==========================================
echo DONE! Index has been generated.
echo Please upload this file via the Dashboard.
echo ==========================================
pause
