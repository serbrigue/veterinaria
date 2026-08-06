# =============================================================
# Google Compute Engine (Máquina Virtual / Servidor)
# =============================================================
# Este archivo levanta el servidor real (la computadora en la nube)
# donde correrá nuestro código, base de datos y contenedores Docker.

# Reservamos una IP Pública Estática
# Esto asegura que la IP de nuestro servidor nunca cambie, incluso si lo reiniciamos.
resource "google_compute_address" "vet_static_ip" {
  name   = "vet-static-ip-${random_id.bucket_suffix.hex}"
  region = var.region
}

# Configuracion y lanzamiento de la Máquina Virtual
resource "google_compute_instance" "vet_vm" {
  name         = "vet-server-cl"
  machine_type = var.machine_type
  zone         = var.zone

  # Asignamos la etiqueta 'web-server' para que apliquen las reglas de firewall (puertos 80, 443, 22).
  tags = ["web-server"]

  # Configuración del Disco Duro (Sistema Operativo)
  boot_disk {
    initialize_params {
      image = "ubuntu-os-cloud/ubuntu-2404-lts-amd64" # Usamos Ubuntu 24.04 LTS
      size  = 30                                      # Tamaño de 30 GB SSD Balanceado
      type  = "pd-balanced"
    }
  }

  # Configuración de Red
  network_interface {
    network    = google_compute_network.vpc.id
    subnetwork = google_compute_subnetwork.subnet.id

    # Vinculamos la IP Estática que reservamos en el Paso 6.1
    access_config {
      nat_ip = google_compute_address.vet_static_ip.address
    }
  }

  # Permisos que tendrá la propia Máquina Virtual hacia otros servicios de Google
  service_account {
    scopes = [
      "https://www.googleapis.com/auth/devstorage.read_write", # Escribir en Storage
      "https://www.googleapis.com/auth/logging.write",         # Enviar logs a la consola de GCP
      "https://www.googleapis.com/auth/monitoring.write"       # Enviar métricas de CPU/RAM
    ]
  }

  # Paso 6.3: Inyección del Script de Arranque (Startup Script)
  # Este script se ejecutará automáticamente la primera vez que la máquina encienda.
  # Aquí le inyectamos todas nuestras variables (contraseñas, claves, nombre del bucket, etc.)
  # para que el servidor se configure a sí mismo sin intervención humana.
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

  # Dependencias: Le decimos a Terraform que NO cree el servidor hasta que 
  # el firewall esté listo, para no dejar la máquina incomunicada.
  depends_on = [
    google_compute_firewall.allow_http,
    google_compute_firewall.allow_ssh
  ]
}
