# =============================================================
# Terraform — Amazon RDS MySQL (Capa de Persistencia)
# =============================================================

# Subnet group para RDS (usa las subnets del VPC default)
resource "aws_db_subnet_group" "vet_db_subnet" {
  name       = "vet-db-subnet-group"
  subnet_ids = data.aws_subnets.default.ids

  tags = {
    Name = "Veterinaria-DB-Subnet-Group"
  }
}

# Instancia RDS MySQL 8.0
resource "aws_db_instance" "vet_db" {
  identifier = "vet-db"

  # Motor y versión
  engine         = "mysql"
  engine_version = "8.0"

  # Capacidad (Sandbox-compatible)
  instance_class    = "db.t3.micro"
  allocated_storage = 20
  storage_type      = "gp2"

  # Credenciales
  db_name  = var.db_name
  username = var.db_username
  password = var.db_password

  # Red y seguridad
  db_subnet_group_name   = aws_db_subnet_group.vet_db_subnet.name
  vpc_security_group_ids = [aws_security_group.sg_rds.id]
  publicly_accessible    = false
  multi_az               = false

  # Sandbox: no necesitamos snapshot final
  skip_final_snapshot       = true
  final_snapshot_identifier = null

  # Deshabilitar backups automáticos (Sandbox)
  backup_retention_period = 0

  # Permitir actualización de versiones menores
  auto_minor_version_upgrade = true

  tags = {
    Name = "Veterinaria-RDS"
  }
}
