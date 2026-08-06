# =============================================================
# 3. Red y Cortafuegos (VPC & Firewalls) — GCP
# =============================================================
# Este archivo configura la red privada virtual (VPC) y las reglas de seguridad
# que dictan quién puede conectarse a nuestro servidor.


# En lugar de usar la red por defecto, creamos una propia aislada.
resource "google_compute_network" "vpc" {
  name                    = "vet-vpc-gcp"
  auto_create_subnetworks = false
}

# Creamos una Subred
# Se asigna un bloque de direcciones IP (10.0.1.0/24) en nuestra región específica.
resource "google_compute_subnetwork" "subnet" {
  name          = "vet-subnet-gcp"
  ip_cidr_range = "10.0.1.0/24"
  region        = var.region
  network       = google_compute_network.vpc.id
}

# Abrimos puertos HTTP y HTTPS
# Regla de firewall que permite que cualquier persona en internet (0.0.0.0/0)
# pueda visitar la página web a través de los puertos 80 y 443.
resource "google_compute_firewall" "allow_http" {
  name    = "vet-allow-http"
  network = google_compute_network.vpc.name

  allow {
    protocol = "tcp"
    ports    = ["80", "443"]
  }

  source_ranges = ["0.0.0.0/0"]
  # Esta etiqueta 'web-server' se usa para aplicar esta regla solo a las VMs que la tengan.
  target_tags = ["web-server"]
}

# Paso 3.4: Abrir Puerto SSH (Para administración)
# Regla de firewall que permite conectarnos a la consola del servidor de forma segura.
resource "google_compute_firewall" "allow_ssh" {
  name    = "vet-allow-ssh"
  network = google_compute_network.vpc.name

  allow {
    protocol = "tcp"
    ports    = ["22"]
  }

  # Se permite acceso desde el servicio interno de Google (IAP).
  source_ranges = ["35.235.240.0/20"]
  target_tags   = ["web-server"]
}
