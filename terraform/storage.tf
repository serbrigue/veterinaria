# =============================================================
# 4. Google Cloud Storage — Bucket para Archivos (Imágenes, PDFs)
# =============================================================


# Paso 4.1: Generar un sufijo aleatorio
# Los nombres de los buckets en Google deben ser únicos a nivel mundial, 
# por lo que agregamos un texto aleatorio al final.
resource "random_id" "bucket_suffix" {
  byte_length = 4
}

# Paso 4.2: Crear el Bucket (Contenedor de archivos)
resource "google_storage_bucket" "vet_storage" {
  name          = "vet-assets-${var.project_id}-${random_id.bucket_suffix.hex}"
  location      = "SOUTHAMERICA-WEST1" # Santiago de Chile para máxima velocidad
  storage_class = "REGIONAL"

  # Borrado forzado: permite a Terraform eliminar el bucket incluso si tiene archivos dentro.
  force_destroy = true

  // Permite que el bucket sea accesible desde cualquier lugar
  uniform_bucket_level_access = false

  # Configuración CORS: Permite que nuestro frontend web pueda cargar/leer las imágenes correctamente.
  cors {
    origin          = ["*"]
    method          = ["GET", "HEAD", "PUT", "POST", "DELETE"]
    response_header = ["*"]
    max_age_seconds = 3600
  }
}

# Paso 4.3: Hacer las imágenes públicas
# Otorga el rol de 'Lector' (objectViewer) a todo el mundo (allUsers)
# para que las imágenes puedan verse en la página web sin necesidad de iniciar sesión en Google.
resource "google_storage_bucket_iam_member" "public_read" {
  bucket = google_storage_bucket.vet_storage.name
  role   = "roles/storage.objectViewer"
  member = "allUsers"
}

# =============================================================
# 5. Credenciales S3 Interoperables para Laravel
# =============================================================
# Laravel usa un driver tipo "S3" nativamente. Google Cloud Storage es compatible
# con S3, así que creamos un usuario de servicio y claves especiales para conectarlos.

# Creamos una cuenta de servicio dedicada al storage.
resource "google_service_account" "vet_storage_sa" {
  account_id   = "vet-storage-sa-${random_id.bucket_suffix.hex}"
  display_name = "Service Account para acceso S3 a Cloud Storage"
}

# Le damos permisos de administrador total pero SOLO sobre este bucket.
resource "google_storage_bucket_iam_member" "sa_storage_admin" {
  bucket = google_storage_bucket.vet_storage.name
  role   = "roles/storage.objectAdmin"
  member = "serviceAccount:${google_service_account.vet_storage_sa.email}"
}

# Generamos las claves de acceso tipo S3 (AWS Access Key y AWS Secret Key).
resource "google_storage_hmac_key" "vet_s3_key" {
  service_account_email = google_service_account.vet_storage_sa.email
  depends_on            = [google_storage_bucket_iam_member.sa_storage_admin]
}
