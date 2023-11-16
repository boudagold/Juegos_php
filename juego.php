<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pinball Game</title>
    <style>
 
    
        canvas {
            display: block;
            background-color: #f0f0f0;
            max-width: 100%;
        }
    </style>
    
</head>
<body>
    <?php include 'partials/menu.php'; // Incluye el contenido de header.php
?>
    <canvas id="pinballCanvas" width="800" height="600"></canvas>
    <button onclick="resetGame()">Restart Game</button>
    <button ><a href="https://danieltec.es/proyectos/snake.php">Juego del snake</a></button>
    <script>
        const canvas = document.getElementById("pinballCanvas");
        const ctx = canvas.getContext("2d");

        class Block {
            constructor(x, y) {
                this.x = x;
                this.y = y;
                this.isVisible = true;
            }

            draw() {
                if (this.isVisible) {
                    ctx.fillStyle = "#000";
                    ctx.fillRect(this.x, this.y, 30, 10);
                }
            }
        }

        class Platform {
            constructor() {
                this.width = 100;
                this.height = 10;
                this.x = (canvas.width - this.width) / 2;
                this.y = canvas.height - this.height - 10;
                this.speed = 5; // Velocidad normal
            }

            draw() {
                ctx.fillStyle = "#00f";
                ctx.fillRect(this.x, this.y, this.width, this.height);
            }

            moveLeft() {
                this.x -= this.speed;
                if (this.x < 0) {
                    this.x = 0;
                }
            }

            moveRight() {
                this.x += this.speed;
                if (this.x + this.width > canvas.width) {
                    this.x = canvas.width - this.width;
                }
            }

            increaseSpeed() {
                this.speed = 10; // Velocidad aumentada
            }

            resetSpeed() {
                this.speed = 5; // Restablecer la velocidad
            }

            getTop() {
                return this.y;
            }

            getBottom() {
                return this.y + this.height;
            }

            getLeft() {
                return this.x;
            }

            getRight() {
                return this.x + this.width;
            }
        }

        class Ball {
            constructor() {
                this.radius = 10;
                this.x = canvas.width / 2;
                this.y = canvas.height / 2;
                this.speedX = 2;
                this.speedY = -2;
            }

            draw() {
                ctx.beginPath();
                ctx.arc(this.x, this.y, this.radius, 0, Math.PI * 2);
                ctx.fillStyle = "#f00";
                ctx.fill();
                ctx.closePath();
            }

            move() {
                this.x += this.speedX;
                this.y += this.speedY;

                // Colisiones con las paredes
                if (this.x + this.radius > canvas.width || this.x - this.radius < 0) {
                    this.speedX = -this.speedX;
                }

                if (this.y - this.radius < 0) {
                    this.speedY = -this.speedY;
                }

                // Colisión con la plataforma
                if (
                    this.y + this.radius > pinballGame.platform.getTop() &&
                    this.y - this.radius < pinballGame.platform.getBottom() &&
                    this.x + this.radius > pinballGame.platform.getLeft() &&
                    this.x - this.radius < pinballGame.platform.getRight()
                ) {
                    this.speedY = -this.speedY;
                }

                // Colisión con bloques
                pinballGame.blocks.forEach(block => {
                    if (
                        block.isVisible &&
                        this.y + this.radius > block.y &&
                        this.y - this.radius < block.y + 10 &&
                        this.x + this.radius > block.x &&
                        this.x - this.radius < block.x + 30
                    ) {
                        block.isVisible = false;
                        this.speedY = -this.speedY;
                        pinballGame.score += 10;
                    }
                });

                // Fin del juego si la pelota toca la parte inferior
                if (this.y + this.radius > canvas.height) {
                    stopGame();
                    alert("Game Over. Score: " + pinballGame.score);
                    resetGame();
                }
            }
        }

        class PinballGame {
            constructor() {
                this.blocks = [];
                this.platform = new Platform();
                this.ball = new Ball();
                this.score = 0;
                this.intervalId = null;
                this.createBlocks();
            }

            createBlocks() {
                for (let i = 0; i < canvas.width / 40; i++) {
                    for (let j = 0; j < 3; j++) {
                        const block = new Block(i * 40, j * 20);
                        this.blocks.push(block);
                    }
                }
            }

            drawBlocks() {
                this.blocks.forEach(block => block.draw());
            }

            drawPlatform() {
                this.platform.draw();
            }

            drawBall() {
                this.ball.draw();
            }

            update() {
                this.ball.move();
                this.checkCollision();
            }

            checkCollision() {
                if (
                    this.ball.y + this.ball.radius > this.platform.getTop() &&
                    this.ball.y - this.ball.radius < this.platform.getBottom() &&
                    this.ball.x > this.platform.getLeft() &&
                    this.ball.x < this.platform.getRight()
                ) {
                    this.score++;
                }
            }

            start() {
                this.intervalId = setInterval(() => {
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                    this.drawBlocks();
                    this.drawPlatform();
                    this.drawBall();
                    this.update();

                    ctx.fillStyle = "#000";
                    ctx.font = "20px Arial";
                    ctx.fillText("Score: " + this.score, 10, 30);
                }, 10);
            }

            stop() {
                clearInterval(this.intervalId);
            }
        }

        let pinballGame = new PinballGame();

        function resetGame() {
            pinballGame.stop();
            pinballGame = new PinballGame();
            pinballGame.start();
        }

        function stopGame() {
            pinballGame.stop();
        }

        document.addEventListener("keydown", (event) => {
            if (event.key === "ArrowLeft") {
                pinballGame.platform.moveLeft();
            } else if (event.key === "ArrowRight") {
                pinballGame.platform.moveRight();
            } else if (event.key === "f") {
                pinballGame.platform.increaseSpeed();
            }
        });

        document.addEventListener("keyup", (event) => {
            if (event.key === "f") {
                pinballGame.platform.resetSpeed();
            }
        });

        canvas.addEventListener("touchstart", handleTouchStart);
        canvas.addEventListener("touchend", handleTouchEnd);

        let touchStartX = 0;

        function handleTouchStart(event) {
            touchStartX = event.touches[0].clientX;
        }

        function handleTouchEnd(event) {
            const touchEndX = event.changedTouches[0].clientX;
            const swipeDistance = touchEndX - touchStartX;

            const sensitivity = 20;

            if (swipeDistance > sensitivity) {
                pinballGame.platform.moveRight();
            } else if (swipeDistance < -sensitivity) {
                pinballGame.platform.moveLeft();
            }
        }

        pinballGame.start();
    </script>
</body>
</html>
