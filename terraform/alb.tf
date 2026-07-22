# =============================================================
# Terraform — Application Load Balancer
# =============================================================

# ALB público
resource "aws_lb" "vet_alb" {
  name               = "vet-alb"
  internal           = false
  load_balancer_type = "application"
  security_groups    = [aws_security_group.sg_alb.id]
  subnets            = data.aws_subnets.default.ids

  tags = {
    Name = "Veterinaria-ALB"
  }
}

# Target Group (apunta a las instancias EC2 del ASG)
resource "aws_lb_target_group" "vet_tg" {
  name     = "vet-tg"
  port     = 80
  protocol = "HTTP"
  vpc_id   = data.aws_vpc.default.id

  # Health Check en la ruta de login (GET público, devuelve 200)
  health_check {
    enabled             = true
    path                = "/iniciar-sesion"
    port                = "traffic-port"
    protocol            = "HTTP"
    healthy_threshold   = 3
    unhealthy_threshold = 3
    timeout             = 10
    interval            = 30
    matcher             = "200"
  }

  # Sticky Sessions (cookie del ALB) para mantener sesiones Redis locales
  stickiness {
    type            = "lb_cookie"
    cookie_duration = 86400 # 24 horas
    enabled         = true
  }

  tags = {
    Name = "Veterinaria-TG"
  }
}

# Listener HTTP (puerto 80 → forward al Target Group)
resource "aws_lb_listener" "vet_http" {
  load_balancer_arn = aws_lb.vet_alb.arn
  port              = 80
  protocol          = "HTTP"

  default_action {
    type             = "forward"
    target_group_arn = aws_lb_target_group.vet_tg.arn
  }
}
