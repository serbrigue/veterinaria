# =============================================================
# Terraform — Amazon S3 (Almacenamiento de Archivos)
# =============================================================

# Sufijo aleatorio para unicidad global del bucket
resource "random_id" "bucket_suffix" {
  byte_length = 4
}

# Bucket S3 para fotos de mascotas, sucursales y archivos
resource "aws_s3_bucket" "vet_storage" {
  bucket        = "vaalavet-s3-${random_id.bucket_suffix.hex}"
  force_destroy = true # Permite destruir el bucket aunque tenga objetos (Sandbox)

  tags = {
    Name        = "Veterinaria-S3-Storage"
    Environment = "sandbox"
  }
}

# Bloquear acceso público (los archivos se sirven via Laravel)
resource "aws_s3_bucket_public_access_block" "vet_storage_block" {
  bucket = aws_s3_bucket.vet_storage.id

  block_public_acls       = true
  block_public_policy     = true
  ignore_public_acls      = true
  restrict_public_buckets = true
}

# Propiedad del bucket
resource "aws_s3_bucket_ownership_controls" "vet_storage_ownership" {
  bucket = aws_s3_bucket.vet_storage.id

  rule {
    object_ownership = "BucketOwnerEnforced"
  }
}

# Configuración CORS para permitir subidas desde el navegador
resource "aws_s3_bucket_cors_configuration" "vet_storage_cors" {
  bucket = aws_s3_bucket.vet_storage.id

  cors_rule {
    allowed_headers = ["*"]
    allowed_methods = ["GET", "PUT", "POST", "DELETE", "HEAD"]
    allowed_origins = ["*"]
    expose_headers  = ["ETag"]
    max_age_seconds = 3600
  }
}
