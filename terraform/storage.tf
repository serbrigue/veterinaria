# =============================================================
# Google Cloud Storage — Bucket para Assets (GCP Free Tier: 5GB)
# =============================================================

resource "random_id" "bucket_suffix" {
  byte_length = 4
}

resource "google_storage_bucket" "vet_storage" {
  name          = "vet-assets-${var.project_id}-${random_id.bucket_suffix.hex}"
  location      = "SOUTHAMERICA-WEST1" # Santiago de Chile para mínima latencia
  storage_class = "REGIONAL"

  # Borrado forzado (permite destruir el bucket con terraform destroy aunque tenga archivos)
  force_destroy = true

  uniform_bucket_level_access = false

  cors {
    origin          = ["*"]
    method          = ["GET", "HEAD", "PUT", "POST", "DELETE"]
    response_header = ["*"]
    max_age_seconds = 3600
  }
}

# Hacer público el acceso de lectura a los objetos del bucket (para fotos de perfil, documentos, etc.)
resource "google_storage_bucket_iam_member" "public_read" {
  bucket = google_storage_bucket.vet_storage.name
  role   = "roles/storage.objectViewer"
  member = "allUsers"
}

# --- Credenciales S3 Interoperables (HMAC Key) para Laravel ---
resource "google_service_account" "vet_storage_sa" {
  account_id   = "vet-storage-sa-${random_id.bucket_suffix.hex}"
  display_name = "Service Account para acceso S3 a Cloud Storage"
}

resource "google_storage_bucket_iam_member" "sa_storage_admin" {
  bucket = google_storage_bucket.vet_storage.name
  role   = "roles/storage.objectAdmin"
  member = "serviceAccount:${google_service_account.vet_storage_sa.email}"
}

resource "google_storage_hmac_key" "vet_s3_key" {
  service_account_email = google_service_account.vet_storage_sa.email
  depends_on            = [google_storage_bucket_iam_member.sa_storage_admin]
}

