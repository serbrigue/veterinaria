# =============================================================
# Outputs — GCP Free Tier
# =============================================================

output "instance_public_ip" {
  description = "Dirección IP pública estática y reservada del servidor en Santiago de Chile"
  value       = google_compute_instance.vet_vm.network_interface[0].access_config[0].nat_ip
}

output "app_url" {
  description = "URL principal (IP Estática) para acceder a la aplicación web de Veterinaria"
  value       = "http://${google_compute_instance.vet_vm.network_interface[0].access_config[0].nat_ip}"
}

output "app_url_domain" {
  description = "URL con nombre de dominio comodín gratuito (nip.io) apuntando a la IP estática del servidor"
  value       = "http://veterinaria.${google_compute_instance.vet_vm.network_interface[0].access_config[0].nat_ip}.nip.io"
}

output "storage_bucket_name" {
  description = "Nombre del bucket regional de Google Cloud Storage creado (dentro de los 5GB gratuitos)"
  value       = google_storage_bucket.vet_storage.name
}

output "ssh_command" {
  description = "Comando de Google Cloud SDK para conectarse por SSH a la VM para depuración"
  value       = "gcloud compute ssh ${google_compute_instance.vet_vm.name} --zone=${google_compute_instance.vet_vm.zone} --project=${var.project_id}"
}
