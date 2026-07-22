# =============================================================
# Terraform — Security Groups (Aislamiento por Capas)
# =============================================================

# ----- Security Group: ALB (Capa Pública) -----
resource "aws_security_group" "sg_alb" {
  name        = "vet-sg-alb"
  description = "Permite trafico HTTP entrante al ALB"
  vpc_id      = data.aws_vpc.default.id

  # HTTP desde cualquier origen
  ingress {
    description = "HTTP desde Internet"
    from_port   = 80
    to_port     = 80
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]
  }

  # Todo el tráfico de salida
  egress {
    from_port   = 0
    to_port     = 0
    protocol    = "-1"
    cidr_blocks = ["0.0.0.0/0"]
  }

  tags = {
    Name = "Veterinaria-SG-ALB"
  }
}

# ----- Security Group: EC2 (Capa de Cómputo) -----
resource "aws_security_group" "sg_ec2" {
  name        = "vet-sg-ec2"
  description = "Permite HTTP desde ALB y SSH para debug"
  vpc_id      = data.aws_vpc.default.id

  # HTTP solo desde el ALB
  ingress {
    description     = "HTTP desde ALB"
    from_port       = 80
    to_port         = 80
    protocol        = "tcp"
    security_groups = [aws_security_group.sg_alb.id]
  }

  # SSH para debugging (Sandbox)
  ingress {
    description = "SSH para administracion"
    from_port   = 22
    to_port     = 22
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]
  }

  # Todo el tráfico de salida
  egress {
    from_port   = 0
    to_port     = 0
    protocol    = "-1"
    cidr_blocks = ["0.0.0.0/0"]
  }

  tags = {
    Name = "Veterinaria-SG-EC2"
  }
}

# ----- Security Group: RDS (Capa de Datos) -----
resource "aws_security_group" "sg_rds" {
  name        = "vet-sg-rds"
  description = "Permite MySQL solo desde las instancias EC2"
  vpc_id      = data.aws_vpc.default.id

  # MySQL solo desde EC2
  ingress {
    description     = "MySQL desde EC2"
    from_port       = 3306
    to_port         = 3306
    protocol        = "tcp"
    security_groups = [aws_security_group.sg_ec2.id]
  }

  # Todo el tráfico de salida
  egress {
    from_port   = 0
    to_port     = 0
    protocol    = "-1"
    cidr_blocks = ["0.0.0.0/0"]
  }

  tags = {
    Name = "Veterinaria-SG-RDS"
  }
}
