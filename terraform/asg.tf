# =============================================================
# Terraform — Auto Scaling Group + Launch Template
# =============================================================

# ----- Launch Template -----
resource "aws_launch_template" "vet_lt" {
  name_prefix   = "vet-lt-"
  image_id      = data.aws_ami.ubuntu.id
  instance_type = var.instance_type
  key_name      = "vockey"

  # IAM Instance Profile preexistente en Sandbox
  iam_instance_profile {
    name = "LabInstanceProfile"
  }

  # Security Group de la capa EC2
  vpc_security_group_ids = [aws_security_group.sg_ec2.id]

  # Script de arranque con valores interpolados
  user_data = base64encode(templatefile("${path.module}/user_data.sh", {
    db_host           = aws_db_instance.vet_db.address
    db_port           = "3306"
    db_name           = var.db_name
    db_username       = var.db_username
    db_password       = var.db_password
    redis_password    = var.redis_password
    app_key           = var.app_key
    ghcr_image        = var.ghcr_image
    s3_bucket         = aws_s3_bucket.vet_storage.id
    aws_access_key    = var.aws_access_key
    aws_secret_key    = var.aws_secret_key
    aws_session_token = var.aws_session_token
    alb_dns           = aws_lb.vet_alb.dns_name
    mail_username     = var.mail_username
    mail_password     = var.mail_password
  }))

  # Disco raíz de 20 GB
  block_device_mappings {
    device_name = "/dev/sda1"
    ebs {
      volume_size           = 20
      volume_type           = "gp2"
      delete_on_termination = true
    }
  }

  # Tags para las instancias lanzadas
  tag_specifications {
    resource_type = "instance"
    tags = {
      Name = "Veterinaria-ASG-Instance"
    }
  }

  # Metadata (IMDSv2)
  metadata_options {
    http_endpoint               = "enabled"
    http_tokens                 = "required"
    http_put_response_hop_limit = 2
  }

  lifecycle {
    create_before_destroy = true
  }
}

# ----- Auto Scaling Group -----
resource "aws_autoscaling_group" "vet_asg" {
  name                = "vet-asg"
  desired_capacity    = 2
  min_size            = 1
  max_size            = 3
  vpc_zone_identifier = data.aws_subnets.default.ids
  target_group_arns   = [aws_lb_target_group.vet_tg.arn]

  # Health check del ALB (no solo EC2)
  health_check_type         = "ELB"
  health_check_grace_period = 300

  launch_template {
    id      = aws_launch_template.vet_lt.id
    version = "$Latest"
  }

  # Tags propagadas a las instancias
  tag {
    key                 = "Name"
    value               = "Veterinaria-ASG-Instance"
    propagate_at_launch = true
  }

  tag {
    key                 = "Environment"
    value               = "sandbox"
    propagate_at_launch = true
  }

  # Esperar a que las instancias pasen el health check del ELB
  wait_for_elb_capacity = 1

  lifecycle {
    create_before_destroy = true
  }
}
