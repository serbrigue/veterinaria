# =============================================================
# Google Compute Engine (VM e2-micro — 100% Capa Gratuita)
# =============================================================

# Dirección IP Pública Estática y Reservada en Santiago de Chile
resource "google_compute_address" "vet_static_ip" {
  name   = "vet-static-ip-${random_id.bucket_suffix.hex}"
  region = var.region
}

resource "google_compute_instance" "vet_vm" {
  name         = "vet-server-cl"
  machine_type = var.machine_type # e2-medium (4 GB RAM, 2 vCPU) para máxima velocidad
  zone         = var.zone

  tags = ["web-server"]

  boot_disk {
    initialize_params {
      image = "ubuntu-os-cloud/ubuntu-2404-lts-amd64"
      size  = 30 # 30 GB SSD Balanceado para alto rendimiento en base de datos
      type  = "pd-balanced"
    }
  }

  network_interface {
    network    = google_compute_network.vpc.id
    subnetwork = google_compute_subnetwork.subnet.id

    # Asigna la IP estática reservada al servidor
    access_config {
      nat_ip = google_compute_address.vet_static_ip.address
    }
  }

  # Permisos para que la VM pueda leer/escribir en Cloud Storage y enviar logs
  service_account {
    scopes = [
      "https://www.googleapis.com/auth/devstorage.read_write",
      "https://www.googleapis.com/auth/logging.write",
      "https://www.googleapis.com/auth/monitoring.write"
    ]
  }

  # Script de arranque procesado por Terraform (inyección de variables)
  metadata_startup_script = templatefile("${path.module}/startup-script.sh", {
    db_name          = var.db_name
    db_username      = var.db_username
    db_password      = var.db_password
    db_root_password = var.db_root_password
    redis_password   = var.redis_password
    app_key          = var.app_key
    ghcr_image       = var.ghcr_image
    bucket_name      = google_storage_bucket.vet_storage.name
    aws_access_key   = google_storage_hmac_key.vet_s3_key.access_id
    aws_secret_key   = google_storage_hmac_key.vet_s3_key.secret
    region           = var.region
    mail_username    = var.mail_username
    mail_password    = var.mail_password
  })

  # Asegurar que el firewall y la red estén listos antes de lanzar la VM
  depends_on = [
    google_compute_firewall.allow_http,
    google_compute_firewall.allow_ssh
  ]
}
