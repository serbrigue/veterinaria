# =============================================================
# Red y Cortafuegos (VPC & Firewalls) — GCP Free Tier
# =============================================================

# 1. Red VPC Personalizada
resource "google_compute_network" "vpc" {
  name                    = "vet-vpc-gcp"
  auto_create_subnetworks = false
}

# 2. Subred en la región elegible (us-central1)
resource "google_compute_subnetwork" "subnet" {
  name          = "vet-subnet-gcp"
  ip_cidr_range = "10.0.1.0/24"
  region        = var.region
  network       = google_compute_network.vpc.id
}

# 3. Regla de Firewall: Tráfico Web HTTP (Puerto 80) y HTTPS (Puerto 443)
resource "google_compute_firewall" "allow_http" {
  name    = "vet-allow-http"
  network = google_compute_network.vpc.name

  allow {
    protocol = "tcp"
    ports    = ["80", "443"]
  }

  source_ranges = ["0.0.0.0/0"]
  target_tags   = ["web-server"]
}

# 4. Regla de Firewall: Tráfico SSH para debugging y administración
resource "google_compute_firewall" "allow_ssh" {
  name    = "vet-allow-ssh"
  network = google_compute_network.vpc.name

  allow {
    protocol = "tcp"
    ports    = ["22"]
  }

  source_ranges = ["0.0.0.0/0", "35.235.240.0/20"] # Incluye rango IAP de Google
  target_tags   = ["web-server"]
}
