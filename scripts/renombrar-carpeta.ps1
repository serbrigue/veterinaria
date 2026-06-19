$parent = Split-Path -Parent $PSScriptRoot
$old = Join-Path $parent "laravel-vue-options-bootstrap-skeleton"
$new = Join-Path $parent "veterinaria-aprendizaje"
if (-not (Test-Path $old)) {
    Write-Host "No existe la carpeta antigua: $old"
    exit 0
}
if (Test-Path $new) {
    Write-Host "Ya existe $new. Borra la carpeta antigua manualmente si ya migraste."
    exit 0
}
Rename-Item -LiteralPath $old -NewName "veterinaria-aprendizaje"
Write-Host "Renombrado a $new"
