# =============================================================
# Terraform — Data Sources
# =============================================================

# AMI de Ubuntu 24.04 LTS (la más reciente)
data "aws_ami" "ubuntu" {
  most_recent = true
  owners      = ["099720109477"] # Canonical

  filter {
    name   = "name"
    values = ["ubuntu/images/hvm-ssd-gp3/ubuntu-noble-24.04-amd64-server-*"]
  }

  filter {
    name   = "virtualization-type"
    values = ["hvm"]
  }
}

# VPC por defecto del Sandbox
data "aws_vpc" "default" {
  default = true
}

# Todas las subnets del VPC default (necesarias para ALB multi-AZ)
data "aws_subnets" "default" {
  filter {
    name   = "vpc-id"
    values = [data.aws_vpc.default.id]
  }
}
