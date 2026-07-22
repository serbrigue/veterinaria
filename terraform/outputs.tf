# =============================================================
# Terraform — Outputs
# =============================================================

output "alb_dns_name" {
  description = "DNS público del Application Load Balancer (URL de acceso a la app)"
  value       = "http://${aws_lb.vet_alb.dns_name}"
}

output "rds_endpoint" {
  description = "Endpoint de la instancia RDS MySQL"
  value       = aws_db_instance.vet_db.endpoint
}

output "rds_address" {
  description = "Hostname de RDS (sin puerto)"
  value       = aws_db_instance.vet_db.address
}

output "s3_bucket_name" {
  description = "Nombre del bucket S3 creado"
  value       = aws_s3_bucket.vet_storage.id
}
