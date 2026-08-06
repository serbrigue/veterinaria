# =============================================================
# 1. Configuración Principal de Terraform y Proveedores
# =============================================================
# Este archivo le indica a Terraform qué plugins (providers) necesita descargar
# para poder comunicarse con las plataformas externas, en este caso Google Cloud.

terraform {
  # Versión mínima de Terraform requerida para ejecutar este código.
  required_version = ">= 1.5"

  required_providers {
    # Proveedor oficial de Google Cloud Platform. Permite crear recursos como VMs, Redes, etc.
    google = {
      source  = "hashicorp/google"
      version = "~> 6.0"
    }
    # Proveedor Random. Se usa para generar cadenas de texto aleatorias (útil para nombres únicos de buckets).
    random = {
      source  = "hashicorp/random"
      version = "~> 3.5"
    }
  }
}

# Configuración del proveedor de Google. 
# Le indicamos en qué proyecto, región y zona vamos a trabajar,
# tomando los valores de nuestras variables definidas en variables.tf.
provider "google" {
  project = var.project_id
  region  = var.region
  zone    = var.zone
}
