# =============================================================
# 7. Outputs — Resultados del Despliegue
# =============================================================
# Una vez que Terraform termina de construir toda la infraestructura,
# imprime esta información útil en la pantalla para que podamos usarla.

# Muestra la IP pública estática que se asignó al servidor.
output "instance_public_ip" {
  description = "Dirección IP pública estática y reservada del servidor en Santiago de Chile"
  value       = google_compute_instance.vet_vm.network_interface[0].access_config[0].nat_ip
}

# Muestra la URL directa por IP para acceder al sistema.
output "app_url" {
  description = "URL principal (IP Estática) para acceder a la aplicación web de Veterinaria"
  value       = "http://${google_compute_instance.vet_vm.network_interface[0].access_config[0].nat_ip}"
}

# Genera y muestra un dominio comodín (nip.io) gratuito que ya apunta a nuestra IP.
output "app_url_domain" {
  description = "URL con nombre de dominio comodín gratuito (nip.io) apuntando a la IP estática del servidor"
  value       = "http://veterinaria.${google_compute_instance.vet_vm.network_interface[0].access_config[0].nat_ip}.nip.io"
}

# Muestra el nombre final del Bucket de almacenamiento creado.
output "storage_bucket_name" {
  description = "Nombre del bucket regional de Google Cloud Storage creado (dentro de los 5GB gratuitos)"
  value       = google_storage_bucket.vet_storage.name
}

# Te entrega el comando exacto que debes pegar en tu terminal para entrar al servidor por SSH.
output "ssh_command" {
  description = "Comando de Google Cloud SDK para conectarse por SSH a la VM para depuración"
  value       = "gcloud compute ssh ${google_compute_instance.vet_vm.name} --zone=${google_compute_instance.vet_vm.zone} --project=${var.project_id}"
}
