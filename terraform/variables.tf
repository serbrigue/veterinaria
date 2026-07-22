# =============================================================
# Terraform — Variables de Entrada
# =============================================================

# ----- Credenciales AWS Sandbox (temporales) -----

variable "aws_access_key" {
  description = "AWS Access Key ID (credencial temporal del Sandbox)"
  type        = string
  sensitive   = true
}

variable "aws_secret_key" {
  description = "AWS Secret Access Key (credencial temporal del Sandbox)"
  type        = string
  sensitive   = true
}

variable "aws_session_token" {
  description = "AWS Session Token (obligatorio en Sandbox Academy)"
  type        = string
  sensitive   = true
}

# ----- Base de Datos RDS -----

variable "db_name" {
  description = "Nombre de la base de datos MySQL"
  type        = string
  default     = "veterinaria"
}

variable "db_username" {
  description = "Usuario administrador de MySQL"
  type        = string
  default     = "vet_user"
}

variable "db_password" {
  description = "Contraseña del usuario MySQL"
  type        = string
  sensitive   = true
}

# ----- Aplicación -----

variable "app_key" {
  description = "APP_KEY de Laravel (base64:...)"
  type        = string
  sensitive   = true
}

variable "ghcr_image" {
  description = "Imagen Docker de la aplicación en GHCR"
  type        = string
  default     = "ghcr.io/serbrigue/veterinaria:main"
}

variable "instance_type" {
  description = "Tipo de instancia EC2 para el ASG"
  type        = string
  default     = "t3.small"
}

variable "redis_password" {
  description = "Contraseña de Redis"
  type        = string
  default     = "redis_secret"
  sensitive   = true
}

# ----- Mail (opcional) -----

variable "mail_username" {
  description = "Usuario SMTP para envío de correos"
  type        = string
  default     = ""
}

variable "mail_password" {
  description = "Contraseña SMTP"
  type        = string
  default     = ""
  sensitive   = true
}
