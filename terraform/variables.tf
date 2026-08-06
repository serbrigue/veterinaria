# =============================================================
# 2. Variables de Configuración — Entradas del Sistema
# =============================================================
# Aquí definimos todas las variables que nuestro código de infraestructura va a necesitar.


# ----- Configuración de Google Cloud -----
variable "project_id" {
  description = "ID del proyecto en Google Cloud Platform (GCP)"
  type        = string
}

variable "region" {
  description = "Región de GCP (Ej: southamerica-west1 para Santiago de Chile)"
  type        = string
  default     = "southamerica-west1"
}

variable "zone" {
  description = "Zona de GCP dentro de la región (ej. southamerica-west1-a)"
  type        = string
  default     = "southamerica-west1-a"
}

variable "machine_type" {
  description = "Tipo de máquina en Compute Engine (ej. e2-medium para mayor velocidad con créditos de prueba)"
  type        = string
  default     = "e2-medium"
}

# ----- Base de Datos MySQL (Contenedor local en la VM) -----
# Variables para inicializar y conectar la base de datos.
variable "db_name" {
  description = "Nombre de la base de datos MySQL"
  type        = string
  default     = "veterinaria"
}

variable "db_username" {
  description = "Usuario de la base de datos MySQL"
  type        = string
  default     = "vet_user"
}

variable "db_password" {
  description = "Contraseña de la base de datos MySQL"
  type        = string
  sensitive   = true # 'sensitive' oculta el valor en los logs de la consola
}

variable "db_root_password" {
  description = "Contraseña root de MySQL"
  type        = string
  sensitive   = true
}

# ----- Redis -----
variable "redis_password" {
  description = "Contraseña para Redis (Caché y Colas)"
  type        = string
  sensitive   = true
  default     = "redis_secret"
}

# ----- Aplicación Laravel -----
variable "app_key" {
  description = "Clave de encriptación de Laravel (base64:...)"
  type        = string
  sensitive   = true
}

variable "ghcr_image" {
  description = "Imagen Docker de la aplicación en GitHub Container Registry que la VM descargará"
  type        = string
  default     = "ghcr.io/serbrigue/veterinaria:main"
}

# ----- Correo (SMTP / Gmail) -----
variable "mail_username" {
  description = "Correo Gmail para envío SMTP (notificaciones del sistema)"
  type        = string
  default     = "vaalavet@gmail.com"
}

variable "mail_password" {
  description = "Contraseña de aplicación de Gmail (16 caracteres)"
  type        = string
  sensitive   = true
}
