terraform {
  required_providers {
    aws = {
      source  = "hashicorp/aws"
      version = "~> 5.0"
    }
  }
}

provider "aws" {
  region = var.aws_region
}

# -----------------------------------------------------------
# Buscar la última imagen (AMI) de Ubuntu 24.04 LTS
# -----------------------------------------------------------
data "aws_ami" "ubuntu" {
  most_recent = true
  owners      = ["099720109477"] # ID oficial de Canonical (Ubuntu)

  filter {
    name   = "name"
    values = ["ubuntu/images/hvm-ssd-gp3/ubuntu-noble-24.04-amd64-server-*"]
  }
}

# -----------------------------------------------------------
# Security Group: Permite tráfico web (80) y acceso SSH (22)
# -----------------------------------------------------------
resource "aws_security_group" "vet_sg" {
  name        = "veterinaria-sg"
  description = "Permitir trafico HTTP y SSH"

  ingress {
    from_port   = 22
    to_port     = 22
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]
  }

  ingress {
    from_port   = 80
    to_port     = 80
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]
  }

  egress {
    from_port   = 0
    to_port     = 0
    protocol    = "-1"
    cidr_blocks = ["0.0.0.0/0"]
  }

  tags = {
    Name = "Veterinaria-SG"
  }
}

# -----------------------------------------------------------
# Instancia EC2
# -----------------------------------------------------------
resource "aws_instance" "vet_server" {
  ami           = data.aws_ami.ubuntu.id
  instance_type = var.instance_type
  key_name      = "vockey" # Llave predeterminada de AWS Academy
  iam_instance_profile = "LabInstanceProfile" # Perfil requerido por AWS Academy

  vpc_security_group_ids = [aws_security_group.vet_sg.id]

  # Configuración del disco (EBS)
  root_block_device {
    volume_size = 20 # 20 GB es suficiente
    volume_type = "gp2"
  }

  # -----------------------------------------------------------
  # User Data: Script que se ejecuta automáticamente al arrancar
  # -----------------------------------------------------------
  user_data = <<-EOF
    #!/bin/bash
    # Actualizar sistema e instalar dependencias
    apt-get update -y
    apt-get install -y docker.io docker-compose-v2 git

    # Clonar el repositorio asegurando que sea la rama main
    cd /home/ubuntu
    git clone -b main https://github.com/${var.github_repository}.git veterinaria
    cd veterinaria

    # Crear el archivo .env necesario para Docker Compose
    cat <<'INNER_EOF' > .env
    ${file("${path.module}/../.env")}
    INNER_EOF

    # Obtener la IP pública desde el IMDS de AWS con reintentos
    # El IMDS puede no estar listo inmediatamente al boot (race condition)
    # Intentamos primero IMDSv2 (token-based, requerido en instancias nuevas de AWS)
    # y caemos a IMDSv1 si falla
    echo "⏳ Esperando la IP pública del IMDS de AWS..."
    PUBLIC_IP=""
    for i in $(seq 1 10); do
      TOKEN=$(curl -s -X PUT "http://169.254.169.254/latest/api/token" \
               -H "X-aws-ec2-metadata-token-ttl-seconds: 60" 2>/dev/null)
      if [ -n "$TOKEN" ]; then
        PUBLIC_IP=$(curl -s -H "X-aws-ec2-metadata-token: $TOKEN" \
                    http://169.254.169.254/latest/meta-data/public-ipv4 2>/dev/null)
      else
        PUBLIC_IP=$(curl -s http://169.254.169.254/latest/meta-data/public-ipv4 2>/dev/null)
      fi
      [ -n "$PUBLIC_IP" ] && break
      echo "   IMDS no disponible todavía (intento $i/10), reintentando en 5s..."
      sleep 5
    done

    if [ -z "$PUBLIC_IP" ]; then
      echo "❌ ERROR: No se pudo obtener la IP pública del IMDS tras 10 intentos."
      exit 1
    fi
    echo "✅ IP pública obtenida: $PUBLIC_IP"

    # sed -i reemplaza el valor EXISTENTE en el .env (sin duplicados)
    sed -i "s|^APP_URL=.*|APP_URL=http://$PUBLIC_IP|" .env
    sed -i "s|^APP_DEBUG=.*|APP_DEBUG=false|" .env
    sed -i "s|^APP_ENV=.*|APP_ENV=production|" .env

    # SANCTUM_STATEFUL_DOMAINS no existe en .env.example, aquí sí usamos >>
    echo "SANCTUM_STATEFUL_DOMAINS=$PUBLIC_IP" >> .env

    # Levantar los contenedores de producción (descargando imagen desde GHCR)
    docker compose -f docker-compose.prod.yml up -d

    # Cambiar propietario para que el usuario ubuntu pueda editar si se conecta
    chown -R ubuntu:ubuntu /home/ubuntu/veterinaria
  EOF

  tags = {
    Name = "Veterinaria-Server"
  }
}
